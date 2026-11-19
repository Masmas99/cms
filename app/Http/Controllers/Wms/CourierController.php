<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourierController extends Controller
{
    public function index(): View
    {
        return view(wms_view('couriers.index'), [
            'couriers' => Courier::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view(wms_view('couriers.create'), [
            'courier' => new Courier(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Courier::create($this->validated($request));

        return redirect()->route(wms_route_name('couriers.index'))->with('success', 'Ekspedisi berhasil ditambahkan.');
    }

    public function edit(Courier $courier): View
    {
        return view(wms_view('couriers.edit'), [
            'courier' => $courier,
        ]);
    }

    public function update(Request $request, Courier $courier): RedirectResponse
    {
        $courier->update($this->validated($request, $courier));

        return redirect()->route(wms_route_name('couriers.index'))->with('success', 'Ekspedisi berhasil diperbarui.');
    }

    public function destroy(Courier $courier): RedirectResponse
    {
        if ($courier->shipments()->exists()) {
            return back()->with('error', 'Ekspedisi tidak dapat dihapus karena sudah dipakai pada pengiriman.');
        }

        $courier->delete();

        return redirect()->route(wms_route_name('couriers.index'))->with('success', 'Ekspedisi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Courier $courier = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('couriers', 'code')->ignore($courier?->id)],
            'name' => ['required', 'string', 'max:150'],
            'tracking_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }
}
