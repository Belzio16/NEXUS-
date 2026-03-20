<?php
/**
 * NEXUS — Sistema de Gestão de Produtos
 * @author  Jaraujo
 * @version 1.0.0 © 2024
 */

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service) {}

    /* ── Index ── */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category', 'status', 'featured']);

        $products = Product::with('category')
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::withCount('products')->get();
        $stats      = $this->service->getDashboardStats();

        return view('pages.products.index', compact('products', 'categories', 'filters', 'stats'));
    }

    /* ── Create ── */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('pages.products.create', compact('categories'));
    }

    /* ── Store ── */
    public function store(ProductRequest $request): RedirectResponse
    {
        $product = $this->service->create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Produto criado!',
                'message' => "\"{$product->name}\" foi adicionado ao catálogo.",
            ]);
    }

    /* ── Show ── */
    public function show(Product $product): View
    {
        $product->load(['category', 'activities']);
        $product->increment('views_count');

        return view('pages.products.show', compact('product'));
    }

    /* ── Edit ── */
    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('pages.products.edit', compact('product', 'categories'));
    }

    /* ── Update ── */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, $request->validated());

        return redirect()
            ->route('products.index')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Produto atualizado!',
                'message' => "\"{$product->name}\" foi atualizado com sucesso.",
            ]);
    }

    /* ── Destroy ── */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $this->service->delete($product);

        return redirect()
            ->route('products.index')
            ->with('toast', [
                'type'    => 'warning',
                'title'   => 'Produto removido',
                'message' => "\"{$name}\" foi removido do catálogo.",
            ]);
    }

    /* ── AJAX Live Search ── */
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q', '')->trim();

        if ($term->isEmpty()) {
            return response()->json([]);
        }

        $results = Product::with('category')
            ->search((string) $term)
            ->active()
            ->limit(8)
            ->get(['id', 'name', 'price', 'stock', 'image', 'category_id', 'sku'])
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'sku'      => $p->sku,
                'price'    => 'AOA ' . number_format($p->price, 2, ',', '.'),
                'stock'    => $p->stock,
                'category' => $p->category->name,
                'image'    => $p->image ? asset('storage/' . $p->image) : null,
                'url'      => route('products.show', $p),
            ]);

        return response()->json($results);
    }

    /* ── Toggle Featured ── */
    public function toggleFeatured(Product $product): JsonResponse
    {
        $product->update(['featured' => !$product->featured]);
        return response()->json([
            'featured' => $product->featured,
            'message'  => $product->featured ? 'Produto destacado!' : 'Destaque removido.',
        ]);
    }
}
