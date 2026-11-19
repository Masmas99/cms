<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use App\Services\ReferenceGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();

        return view(wms_view('adjustments.index'), [
            'adjustments' => StockAdjustment::query()
                ->with(['product', 'location.warehouse', 'requester', 'approver', 'opnameItem.stockOpname'])
                ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    /**
     * Submit a manual adjustment request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'batch_number' => ['nullable', 'string', 'max:80'],
            'quantity_difference' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $batch = trim((string) ($data['batch_number'] ?? ''));

        if ((float) $data['quantity_difference'] < 0) {
            $available = (float) InventoryStock::query()
                ->where('product_id', $data['product_id'])
                ->where('location_id', $data['location_id'])
                ->where('batch_number', $batch)
                ->value('qty_on_hand');

            if ($available < abs((float) $data['quantity_difference'])) {
                throw ValidationException::withMessages([
                    'quantity_difference' => 'Penyesuaian negatif melebihi stok fisik yang tercatat.',
                ]);
            }
        }

        $adjustment = StockAdjustment::create([
            'reference_no' => ReferenceGenerator::make('ADJ', 'stock_adjustments'),
            'product_id' => $data['product_id'],
            'location_id' => $data['location_id'],
            'batch_number' => $batch,
            'quantity_difference' => $data['quantity_difference'],
            'reason' => $data['reason'],
            'status' => StockAdjustment::STATUS_PENDING,
            'requested_by' => $request->user()->id,
        ]);

        return redirect()
            ->route(wms_route_name('adjustments.index'))
            ->with('success', "Pengajuan {$adjustment->reference_no} dikirim dan menunggu approval admin.");
    }

    public function approve(Request $request, StockAdjustment $adjustment): RedirectResponse
    {
        if ($adjustment->status !== StockAdjustment::STATUS_PENDING) {
            return back()->with('error', 'Hanya pengajuan berstatus pending yang dapat disetujui.');
        }

        try {
            $this->inventory->applyAdjustment($adjustment, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $adjustment->update([
            'status' => StockAdjustment::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'decided_at' => now(),
            'decision_note' => $request->string('decision_note')->trim()->toString() ?: null,
        ]);

        return back()->with('success', "Adjustment {$adjustment->reference_no} disetujui dan stok diperbarui.");
    }

    public function reject(Request $request, StockAdjustment $adjustment): RedirectResponse
    {
        if ($adjustment->status !== StockAdjustment::STATUS_PENDING) {
            return back()->with('error', 'Hanya pengajuan berstatus pending yang dapat ditolak.');
        }

        $data = $request->validate([
            'decision_note' => ['required', 'string', 'max:1000'],
        ]);

        $adjustment->update([
            'status' => StockAdjustment::STATUS_REJECTED,
            'approved_by' => $request->user()->id,
            'decided_at' => now(),
            'decision_note' => $data['decision_note'],
        ]);

        return back()->with('success', "Adjustment {$adjustment->reference_no} ditolak tanpa mengubah stok.");
    }

    /**
     * Provide the data needed by the manual request form.
     */
    public function create(): View
    {
        return view(wms_view('adjustments.create'), [
            'products' => Product::query()->orderBy('name')->get(),
            'locations' => Location::query()->pickable()->with('warehouse')->orderBy('warehouse_id')->orderBy('full_code')->get(),
        ]);
    }
}
