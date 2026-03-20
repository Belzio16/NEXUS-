<?php
/**
 * NEXUS — Sistema de Gestão de Produtos
 * @author  Jaraujo
 * @version 1.0.0 © 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'cost_price',
        'stock', 'min_stock', 'sku', 'image', 'status', 'featured',
        'rating', 'sales_count', 'views_count', 'tags',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'cost_price'  => 'decimal:2',
        'rating'      => 'decimal:2',
        'featured'    => 'boolean',
        'tags'        => 'array',
    ];

    /* ── Boot ── */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($product) {
            $product->slug ??= Str::slug($product->name);
            $product->sku  ??= strtoupper(Str::random(8));
        });
    }

    /* ── Relations ── */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProductActivity::class)->latest();
    }

    /* ── Scopes ── */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('featured', true);
    }

    public function scopeLowStock(Builder $q): Builder
    {
        return $q->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeFilter(Builder $q, array $filters): Builder
    {
        return $q
            ->when($filters['search']  ?? null, fn($q, $v) => $q->search($v))
            ->when($filters['category'] ?? null, fn($q, $v) => $q->where('category_id', $v))
            ->when($filters['status']   ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['featured'] ?? null, fn($q) => $q->featured());
    }

    /* ── Accessors ── */
    public function getMarginAttribute(): float
    {
        if (!$this->cost_price || $this->cost_price == 0) return 0;
        return round((($this->price - $this->cost_price) / $this->price) * 100, 1);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock === 0) return 'out_of_stock';
        if ($this->stock <= $this->min_stock) return 'low_stock';
        return 'in_stock';
    }

    public function getStockBadgeAttribute(): array
    {
        return match ($this->stock_status) {
            'out_of_stock' => ['label' => 'Sem Stock',   'class' => 'badge-danger'],
            'low_stock'    => ['label' => 'Stock Baixo', 'class' => 'badge-warning'],
            default        => ['label' => 'Em Stock',    'class' => 'badge-success'],
        };
    }
}
