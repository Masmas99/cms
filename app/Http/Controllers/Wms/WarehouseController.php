<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /**
     * Display the warehouse list.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $warehouses = Warehouse::query()
            ->withCount(['locations as total_bins' => fn (Builder $query) => $query->where('is_staging', false)])
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view(wms_view('warehouses.index'), [
            'warehouses' => $warehouses,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view(wms_view('warehouses.create'), [
            'warehouse' => new Warehouse(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $warehouse = DB::transaction(function () use ($request): Warehouse {
            $warehouse = Warehouse::create($this->validated($request));

            $warehouse->locations()->create([
                'zone' => 'STG',
                'aisle' => 'STG',
                'rack' => 'STG',
                'bin_code' => 'IN',
                'full_code' => Location::buildFullCode($warehouse->code, 'STG', 'STG', 'STG', 'IN'),
                'capacity_m3' => 0,
                'capacity_kg' => 0,
                'is_staging' => true,
                'is_active' => true,
            ]);

            return $warehouse;
        });

        return redirect()
            ->route(wms_route_name('warehouses.show'), $warehouse)
            ->with('success', 'Gudang dan area staging berhasil dibuat.');
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load(['locations' => fn ($query) => $query
            ->withCount(['stocks as occupied_lots' => fn ($stock) => $stock->where('qty_on_hand', '>', 0)])
            ->orderBy('zone')
            ->orderBy('aisle')
            ->orderBy('rack')
            ->orderBy('bin_code')]);

        return view(wms_view('warehouses.show'), [
            'warehouse' => $warehouse,
            'totalQuantity' => $warehouse->stocks()->sum('qty_on_hand'),
        ]);
    }

    public function edit(Warehouse $warehouse): View
    {
        return view(wms_view('warehouses.edit'), [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($this->validated($request, $warehouse));

        return redirect()
            ->route(wms_route_name('warehouses.show'), $warehouse)
            ->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->locations()->whereHas('stocks', fn ($query) => $query->where('qty_on_hand', '>', 0))->exists()) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena masih menyimpan stok.');
        }

        if ($warehouse->purchaseOrders()->exists()) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena sudah dipakai pada purchase order.');
        }

        $warehouse->locations()->delete();
        $warehouse->delete();

        return redirect()
            ->route(wms_route_name('warehouses.index'))
            ->with('success', 'Gudang berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Warehouse $warehouse = null): array
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses', 'code')->ignore($warehouse?->id)],
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'is_active' => ['boolean'],
        ]);

        return $data;
    }
}
