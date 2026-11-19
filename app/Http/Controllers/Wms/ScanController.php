<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    /**
     * Mobile friendly scanning console.
     */
    public function index(Request $request): View
    {
        return view(wms_view('scan.index'), [
            'recent' => $request->session()->get('scan_history', []),
        ]);
    }

    /**
     * Resolve a scanned code to a product, bin or order.
     */
    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:120'],
        ]);

        $code = trim($data['code']);

        $product = Product::query()
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->first();

        if ($product !== null) {
            $this->rememberScan($request, $code);

            return redirect()
                ->route(wms_route_name('products.show'), $product)
                ->with('success', "Produk ditemukan: {$product->sku} - {$product->name}. Stok tersedia: {$product->availableQuantity()} {$product->uom}.");
        }

        $location = Location::query()
            ->where('full_code', strtoupper($code))
            ->first();

        if ($location !== null) {
            $this->rememberScan($request, $code);

            return redirect()
                ->route(wms_route_name('locations.show'), $location)
                ->with('success', "Bin ditemukan: {$location->full_code} ({$location->warehouse->name}).");
        }

        $order = SalesOrder::query()->where('reference_no', strtoupper($code))->first();

        if ($order !== null) {
            $this->rememberScan($request, $code);

            return redirect()
                ->route(wms_route_name('sales-orders.show'), $order)
                ->with('success', "Sales order ditemukan: {$order->reference_no}.");
        }

        return back()
            ->withInput()
            ->with('error', "Kode \"{$code}\" tidak dikenali. Coba scan barcode produk, label bin, atau nomor sales order.");
    }

    /**
     * Keep the last few successful scans for the console history panel.
     */
    private function rememberScan(Request $request, string $code): void
    {
        $history = collect($request->session()->get('scan_history', []))
            ->reject(fn (array $entry): bool => ($entry['code'] ?? null) === $code)
            ->prepend([
                'code' => $code,
                'at' => now()->format('d/m/Y H:i'),
            ])
            ->take(8)
            ->values()
            ->all();

        $request->session()->put('scan_history', $history);
    }
}
