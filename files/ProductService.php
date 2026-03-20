<?php
/**
 * NEXUS — Sistema de Gestão de Produtos
 * @author  Jaraujo
 * @version 1.0.0 © 2024
 */

namespace App\Services;

use App\Models\Product;
use App\Models\ProductActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleImage($data);
            $product = Product::create($data);

            ProductActivity::create([
                'product_id'  => $product->id,
                'action'      => 'created',
                'description' => "Produto \"{$product->name}\" criado com stock inicial de {$product->stock} unidades.",
                'quantity_change' => $product->stock,
                'value'       => $product->price,
            ]);

            return $product->load('category');
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $oldStock = $product->stock;
            $data = $this->handleImage($data, $product);
            $product->update($data);

            $stockDiff = ($data['stock'] ?? $oldStock) - $oldStock;
            $actionDesc = $stockDiff !== 0
                ? "Produto atualizado. Stock alterado em " . ($stockDiff > 0 ? "+$stockDiff" : "$stockDiff") . " unidades."
                : "Dados do produto atualizados.";

            ProductActivity::create([
                'product_id'      => $product->id,
                'action'          => $stockDiff > 0 ? 'restocked' : 'updated',
                'description'     => $actionDesc,
                'quantity_change' => $stockDiff ?: null,
                'value'           => $product->price,
            ]);

            return $product->fresh('category');
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            ProductActivity::create([
                'product_id'  => $product->id,
                'action'      => 'archived',
                'description' => "Produto \"{$product->name}\" removido do catálogo.",
            ]);
            $product->delete();
        });
    }

    /* ── Dashboard stats ── */
    public function getDashboardStats(): array
    {
        $products = Product::withTrashed();

        return [
            'total_products'   => Product::count(),
            'active_products'  => Product::active()->count(),
            'low_stock'        => Product::lowStock()->count(),
            'out_of_stock'     => Product::where('stock', 0)->count(),
            'total_value'      => Product::active()->sum(DB::raw('price * stock')),
            'total_sales'      => Product::sum('sales_count'),
            'featured'         => Product::featured()->count(),
            'categories'       => \App\Models\Category::withCount('products')->get(),
        ];
    }

    public function getSalesChartData(): array
    {
        // Simula dados de vendas dos últimos 7 dias
        $labels = [];
        $sales  = [];
        $revenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[]  = $date->format('d/m');
            $daily     = rand(800, 4500);
            $sales[]   = rand(12, 60);
            $revenue[] = $daily;
        }

        return compact('labels', 'sales', 'revenue');
    }

    public function getCategoryChartData(): array
    {
        $cats = \App\Models\Category::withCount(['products as total'])
            ->with(['products' => fn($q) => $q->selectRaw('category_id, SUM(sales_count) as total_sales')->groupBy('category_id')])
            ->get();

        return [
            'labels' => $cats->pluck('name')->toArray(),
            'data'   => $cats->pluck('products_count')->toArray(),
            'colors' => $cats->pluck('color')->toArray(),
        ];
    }

    /* ── Private helpers ── */
    private function handleImage(array $data, ?Product $product = null): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($product?->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $data['image']->store('products', 'public');
        }
        return $data;
    }
}
