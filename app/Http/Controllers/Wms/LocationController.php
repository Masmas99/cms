<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Display the bin matrix.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $warehouseId = $request->integer('warehouse');
        $zone = $request->string('zone')->trim()->toString();

        $locations = Location::query()
            ->with('warehouse')
            ->withSum('stocks as qty_on_hand', 'qty_on_hand')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('full_code', 'like', "%{$search}%")
                        ->orWhere('bin_code', 'like', "%{$search}%");
                });
            })
            ->when($warehouseId > 0, fn (Builder $query) => $query->where('warehouse_id', $warehouseId))
            ->when($zone !== '', fn (Builder $query) => $query->where('zone', $zone))
            ->orderBy('warehouse_id')
            ->orderBy('zone')
            ->orderBy('aisle')
            ->orderBy('rack')
            ->orderBy('bin_code')
            ->paginate(20)
            ->withQueryString();

        return view(wms_view('locations.index'), [
            'locations' => $locations,
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
            'zones' => Location::query()->distinct()->orderBy('zone')->pluck('zone'),
            'search' => $search,
            'warehouseId' => $warehouseId,
            'zone' => $zone,
        ]);
    }

    public function create(Request $request): View
    {
        return view(wms_view('locations.create'), [
            'location' => new Location([
                'warehouse_id' => $request->integer('warehouse') ?: null,
                'is_active' => true,
            ]),
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $warehouse = Warehouse::query()->findOrFail($data['warehouse_id']);
        $data['full_code'] = Location::buildFullCode($warehouse->code, $data['zone'], $data['aisle'], $data['rack'], $data['bin_code']);

        $this->ensureUnique($data);

        $location = Location::create($data);

        return redirect()
            ->route(wms_route_name('locations.show'), $location)
            ->with('success', 'Lokasi bin berhasil dibuat.');
    }

    public function show(Location $location): View
    {
        $location->load(['warehouse', 'stocks' => fn ($query) => $query->with('product')->where('qty_on_hand', '>', 0)->orderBy('expired_date')]);

        return view(wms_view('locations.show'), [
            'location' => $location,
        ]);
    }

    public function edit(Location $location): View
    {
        return view(wms_view('locations.edit'), [
            'location' => $location,
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $this->validated($request, $location);
        $warehouse = Warehouse::query()->findOrFail($data['warehouse_id']);
        $data['full_code'] = Location::buildFullCode($warehouse->code, $data['zone'], $data['aisle'], $data['rack'], $data['bin_code']);

        $this->ensureUnique($data, $location);

        $location->update($data);

        return redirect()
            ->route(wms_route_name('locations.show'), $location)
            ->with('success', 'Lokasi bin berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->is_staging) {
            return back()->with('error', 'Area staging tidak dapat dihapus.');
        }

        if ($location->stocks()->where('qty_on_hand', '>', 0)->exists()) {
            return back()->with('error', 'Bin tidak dapat dihapus karena masih menyimpan stok.');
        }

        $location->delete();

        return redirect()
            ->route(wms_route_name('locations.index'))
            ->with('success', 'Lokasi bin berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Location $location = null): array
    {
        $request->merge([
            'zone' => strtoupper((string) $request->input('zone')),
            'aisle' => strtoupper((string) $request->input('aisle')),
            'rack' => strtoupper((string) $request->input('rack')),
            'bin_code' => strtoupper((string) $request->input('bin_code')),
        ]);

        return $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'zone' => ['required', 'string', 'max:20'],
            'aisle' => ['required', 'string', 'max:20'],
            'rack' => ['required', 'string', 'max:20'],
            'bin_code' => ['required', 'string', 'max:20'],
            'capacity_m3' => ['required', 'numeric', 'min:0'],
            'capacity_kg' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function ensureUnique(array $data, ?Location $location = null): void
    {
        $duplicate = Location::query()
            ->where('warehouse_id', $data['warehouse_id'])
            ->where('zone', $data['zone'])
            ->where('aisle', $data['aisle'])
            ->where('rack', $data['rack'])
            ->where('bin_code', $data['bin_code'])
            ->when($location !== null, fn (Builder $query) => $query->whereKeyNot($location->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'bin_code' => 'Kombinasi zona, aisle, rak, dan bin sudah dipakai.',
            ]);
        }
    }
}
