<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Services\InventoryService;
use App\Services\ReferenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnOrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(): View
    {
        return view(wms_view('returns.index'), [
            'returns' => ReturnOrder::query()
                ->with(['salesOrder', 'receiver'])
                ->withCount('items')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $salesOrder = $request->filled('sales_order')
            ? SalesOrder::query()->find($request->integer('sales_order'))
            : null;

        return view(wms_view('returns.create'), [
            'products' => Product::query()->orderBy('name')->get(),
            'salesOrders' => SalesOrder::query()->latest()->limit(100)->get(),
            'salesOrder' => $salesOrder,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.condition' => ['required', 'in:good,damaged'],
            'items.*.batch_number' => ['nullable', 'string', 'max:80'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $return = DB::transaction(function () use ($data, $request): ReturnOrder {
            $return = ReturnOrder::create([
                'reference_no' => ReferenceGenerator::make('RMA', 'return_orders'),
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'status' => ReturnOrder::STATUS_RECEIVED,
                'notes' => $data['notes'] ?? null,
                'received_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'condition' => $item['condition'],
                    'disposition' => ReturnItem::DISPOSITION_PENDING,
                    'batch_number' => trim((string) ($item['batch_number'] ?? '')) ?: 'RMA-'.$return->reference_no,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $return;
        });

        return redirect()
            ->route(wms_route_name('returns.show'), $return)
            ->with('success', "Retur {$return->reference_no} dicatat. Menunggu disposisi admin.");
    }

    public function show(ReturnOrder $returnOrder): View
    {
        $returnOrder->load(['salesOrder', 'receiver', 'items.product', 'items.location.warehouse', 'items.decider']);

        return view(wms_view('returns.show'), [
            'return' => $returnOrder,
            'locations' => Location::query()->pickable()->with('warehouse')->orderBy('warehouse_id')->orderBy('full_code')->get(),
        ]);
    }

    /**
     * Apply the admin's disposition decision for one returned line.
     */
    public function disposition(Request $request, ReturnOrder $returnOrder, ReturnItem $item): RedirectResponse
    {
        if ($item->return_order_id !== $returnOrder->id) {
            abort(404);
        }

        if ($item->disposition !== ReturnItem::DISPOSITION_PENDING) {
            return back()->with('error', 'Item ini sudah memiliki disposisi final.');
        }

        $data = $request->validate([
            'disposition' => ['required', 'in:restock,quarantine,scrap'],
            'location_id' => ['nullable', 'required_if:disposition,restock', 'exists:locations,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $item, $returnOrder, $request): void {
            if ($data['disposition'] === ReturnItem::DISPOSITION_RESTOCK) {
                $location = Location::query()->findOrFail($data['location_id']);
                $product = $item->product;

                $this->inventory->addStock(
                    $product,
                    $location,
                    $item->quantity,
                    $item->batch_number,
                    null,
                    $request->user(),
                    StockMovement::TYPE_RETURN,
                    $returnOrder->reference_no,
                    'Restock retur dari '.$returnOrder->customer_name,
                );
            }

            $item->update([
                'disposition' => $data['disposition'],
                'location_id' => $data['disposition'] === ReturnItem::DISPOSITION_RESTOCK ? $data['location_id'] : null,
                'notes' => $data['notes'] ?? $item->notes,
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
            ]);

            $returnOrder->refresh()->load('items');

            if ($returnOrder->isFullyDisposed()) {
                $returnOrder->update(['status' => ReturnOrder::STATUS_COMPLETED]);
            }
        });

        return back()->with('success', 'Disposisi retur disimpan.');
    }
}
