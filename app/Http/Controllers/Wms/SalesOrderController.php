<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\StockAllocation;
use App\Services\ReferenceGenerator;
use App\Services\ReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct(private readonly ReservationService $reservation) {}

    /**
     * Display outbound orders.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $orders = SalesOrder::query()
            ->withCount('items')
            ->withSum('items as ordered_qty', 'qty_ordered')
            ->withSum('items as picked_qty', 'qty_picked')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(wms_view('sales-orders.index'), [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the sales order creation form.
     */
    public function create(): View
    {
        return view(wms_view('sales-orders.create'), [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store the order and immediately attempt FIFO/FEFO allocation.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.qty_ordered' => ['required', 'numeric', 'min:0.01'],
        ]);

        $order = DB::transaction(function () use ($data, $request): SalesOrder {
            $order = SalesOrder::create([
                'reference_no' => ReferenceGenerator::make('SO', 'sales_orders'),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'status' => SalesOrder::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'qty_ordered' => $item['qty_ordered'],
                ]);
            }

            return $order;
        });

        try {
            $this->reservation->reserve($order);
        } catch (ValidationException $exception) {
            return redirect()
                ->route(wms_route_name('sales-orders.show'), $order)
                ->with('warning', 'Sales order dibuat, tetapi alokasi stok belum berhasil: '.collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route(wms_route_name('sales-orders.show'), $order)
            ->with('success', 'Sales order dibuat dan stok berhasil dialokasikan.');
    }

    /**
     * Show an order with reservation, picking and shipping information.
     */
    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load([
            'creator',
            'items.product',
            'items.allocations.inventoryStock.location',
            'items.allocations.inventoryStock.product',
            'shipment.courier',
        ]);

        return view(wms_view('sales-orders.show'), [
            'order' => $salesOrder,
            'couriers' => Courier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Reserve stock for a draft order.
     */
    public function reserve(SalesOrder $salesOrder): RedirectResponse
    {
        try {
            $this->reservation->reserve($salesOrder);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route(wms_route_name('sales-orders.show'), $salesOrder)->with('success', 'Stok berhasil dialokasikan sesuai aturan rotasi.');
    }

    /**
     * Release reservations and return the order to draft.
     */
    public function release(SalesOrder $salesOrder): RedirectResponse
    {
        try {
            $this->reservation->release($salesOrder);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route(wms_route_name('sales-orders.show'), $salesOrder)->with('success', 'Alokasi stok dilepas.');
    }

    /**
     * Show the smart picking route.
     */
    public function picking(SalesOrder $salesOrder): View|RedirectResponse
    {
        if (in_array($salesOrder->status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_CANCELLED], true)) {
            return redirect()->route(wms_route_name('sales-orders.show'), $salesOrder)
                ->with('error', 'Alokasikan stok terlebih dahulu sebelum melakukan picking.');
        }

        $allocations = $this->allocations($salesOrder)
            ->whereNull('picked_at')
            ->orderBy('pick_sequence')
            ->get();

        return view(wms_view('sales-orders.picking'), [
            'order' => $salesOrder->loadMissing('items.product'),
            'allocations' => $allocations,
            'pickedCount' => $this->allocations($salesOrder)->whereNotNull('picked_at')->count(),
            'totalCount' => $this->allocations($salesOrder)->count(),
        ]);
    }

    /**
     * Confirm a pick by scanning a bin, SKU or by pressing the row action.
     */
    public function pick(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        if (in_array($salesOrder->status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Pesanan ini tidak sedang dalam proses picking.');
        }

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:120'],
            'allocation' => ['nullable', 'uuid'],
        ]);

        $allocation = $this->resolveAllocation($salesOrder, $data, picked: false);

        if ($allocation === null) {
            return back()->with('error', 'Tidak ada item picking yang cocok dengan kode tersebut.');
        }

        try {
            $this->reservation->pick($allocation, $request->user());
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        $salesOrder->refresh()->load('items');

        if ($salesOrder->isFullyPicked()) {
            return redirect()->route(wms_route_name('sales-orders.packing'), $salesOrder)
                ->with('success', 'Semua item sudah di-pick. Lanjutkan verifikasi packing.');
        }

        return redirect()->route(wms_route_name('sales-orders.picking'), $salesOrder)->with('success', 'Item berhasil dikonfirmasi.');
    }

    /**
     * Show packing verification.
     */
    public function packing(SalesOrder $salesOrder): View|RedirectResponse
    {
        if (in_array($salesOrder->status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_RESERVED, SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_CANCELLED], true)) {
            return redirect()->route(wms_route_name('sales-orders.show'), $salesOrder)
                ->with('error', 'Selesaikan picking sebelum packing.');
        }

        $allocations = $this->allocations($salesOrder)
            ->whereNotNull('picked_at')
            ->with(['salesOrderItem.product', 'inventoryStock.location'])
            ->orderBy('pick_sequence')
            ->get();

        return view(wms_view('sales-orders.packing'), [
            'order' => $salesOrder,
            'allocations' => $allocations,
            'couriers' => Courier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Verify one picked parcel line at the packing table.
     */
    public function pack(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        if (in_array($salesOrder->status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_RESERVED, SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Pesanan ini belum siap untuk packing.');
        }

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:120'],
            'allocation' => ['nullable', 'uuid'],
        ]);

        $allocation = $this->resolveAllocation($salesOrder, $data, picked: true);

        if ($allocation === null) {
            return back()->with('error', 'Tidak ada item yang cocok untuk diverifikasi.');
        }

        try {
            $this->reservation->pack($allocation, $request->user());
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        $salesOrder->refresh();

        return redirect()->route(wms_route_name('sales-orders.packing'), $salesOrder)
            ->with('success', $salesOrder->status === SalesOrder::STATUS_PACKED
                ? 'Packing selesai. Silakan input resi dan serahkan ke kurir.'
                : 'Item terverifikasi.');
    }

    /**
     * Capture courier and tracking number, then mark the order shipped.
     */
    public function ship(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $data = $request->validate([
            'courier_id' => ['nullable', 'exists:couriers,id'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $this->reservation->ship($salesOrder, $data['courier_id'] ?? null, $data['tracking_number'] ?? null, $request->user());
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route(wms_route_name('sales-orders.show'), $salesOrder)->with('success', 'Pengiriman dicatat. Stok telah dipotong dari bin.');
    }

    /**
     * Base allocation query for an order.
     *
     * @return Builder<StockAllocation>
     */
    private function allocations(SalesOrder $order): Builder
    {
        return StockAllocation::query()
            ->whereHas('salesOrderItem', fn (Builder $query) => $query->where('sales_order_id', $order->id))
            ->with(['salesOrderItem.product', 'inventoryStock.location']);
    }

    /**
     * Resolve a scan code or allocation id to an allocation owned by this order.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveAllocation(SalesOrder $order, array $data, bool $picked): ?StockAllocation
    {
        if (filled($data['allocation'] ?? null)) {
            return $this->allocations($order)
                ->when($picked, fn (Builder $query) => $query->whereNotNull('picked_at')->whereNull('packed_at'))
                ->when(! $picked, fn (Builder $query) => $query->whereNull('picked_at'))
                ->whereKey($data['allocation'])
                ->first();
        }

        $code = trim((string) ($data['code'] ?? ''));

        if ($code === '') {
            return null;
        }

        return $this->allocations($order)
            ->when($picked, fn (Builder $query) => $query->whereNotNull('picked_at')->whereNull('packed_at'))
            ->when(! $picked, fn (Builder $query) => $query->whereNull('picked_at'))
            ->where(function (Builder $query) use ($code): void {
                $query->whereHas('inventoryStock.location', fn (Builder $location) => $location->where('full_code', $code))
                    ->orWhereHas('inventoryStock.product', fn (Builder $product) => $product
                        ->where('sku', $code)
                        ->orWhere('barcode', $code));
            })
            ->orderBy('pick_sequence')
            ->first();
    }
}
