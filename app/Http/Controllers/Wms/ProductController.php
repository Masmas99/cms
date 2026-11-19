<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the product catalogue.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();

        $products = Product::query()
            ->withSum('stocks as on_hand', 'qty_on_hand')
            ->withSum('stocks as reserved', 'qty_reserved')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn (Builder $query) => $query->where('category', $category))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view(wms_view('products.index'), [
            'products' => $products,
            'search' => $search,
            'category' => $category,
            'categories' => Product::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view(wms_view('products.create'), [
            'product' => new Product([
                'uom' => 'pcs',
                'rotation' => Product::ROTATION_FIFO,
                'is_active' => true,
            ]),
        ]);
    }

    /**
     * Persist a new product.
     */
    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validated($request));

        return redirect()
            ->route(wms_route_name('products.show'), $product)
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Show a product with its stock and movement history.
     */
    public function show(Product $product): View
    {
        $product->load(['stocks' => fn ($query) => $query->with('location.warehouse')->where('qty_on_hand', '>', 0)->orderBy('expired_date')]);

        return view(wms_view('products.show'), [
            'product' => $product,
            'movements' => $product->movements()->with(['location', 'user'])->latest()->limit(15)->get(),
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(Product $product): View
    {
        return view(wms_view('products.edit'), [
            'product' => $product,
        ]);
    }

    /**
     * Update a product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return redirect()
            ->route(wms_route_name('products.show'), $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Delete an unused product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stocks()->exists() || $product->movements()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat stok.');
        }

        $product->delete();

        return redirect()
            ->route(wms_route_name('products.index'))
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Validate the product payload.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:80'],
            'uom' => ['required', 'string', 'max:20'],
            'rotation' => ['required', Rule::in([Product::ROTATION_FIFO, Product::ROTATION_FEFO])],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'max_stock' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
