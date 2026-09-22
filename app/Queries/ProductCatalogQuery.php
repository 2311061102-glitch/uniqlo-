<?php

namespace App\Queries;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductCatalogQuery
{
    public function web(array $filters, ?Category $category = null): Builder
    {
        $query = Product::query()->active()->with(['images', 'variants', 'reviews']);

        if ($category) {
            $categoryIds = $category->children()->pluck('id')->push($category->id);
            $query->whereIn('category_id', $categoryIds);
        }

        return $this->applyFilters($query, $filters, 'q');
    }

    public function api(array $filters): Builder
    {
        $query = Product::query()
            ->active()
            ->with(['images', 'variants', 'category'])
            ->withAvg('reviews', 'rating');

        return $this->applyFilters($query, $filters, 'search');
    }

    private function applyFilters(Builder $query, array $filters, string $searchKey): Builder
    {
        $query->when(! empty($filters['category']), fn (Builder $query) => $query->whereHas(
            'category', fn (Builder $category) => $category->where('slug', $filters['category'])
        ));
        $query->when(! empty($filters['size']), fn (Builder $query) => $query->whereHas(
            'variants', fn (Builder $variant) => $variant->where('size', $filters['size'])
        ));
        $query->when(! empty($filters['color']), fn (Builder $query) => $query->whereHas(
            'variants', fn (Builder $variant) => $variant->where('color', $filters['color'])
        ));
        $query->when(isset($filters['min_price']) && $filters['min_price'] !== '', fn (Builder $query) =>
            $query->where('base_price', '>=', (int) $filters['min_price'])
        );
        $query->when(isset($filters['max_price']) && $filters['max_price'] !== '', fn (Builder $query) =>
            $query->where('base_price', '<=', (int) $filters['max_price'])
        );
        $query->when(! empty($filters['material']), fn (Builder $query) =>
            $query->where('material', 'like', '%'.$filters['material'].'%')
        );
        $query->when(! empty($filters[$searchKey]), fn (Builder $query) =>
            $query->where('name', 'like', '%'.$filters[$searchKey].'%')
        );

        return match ($filters['sort'] ?? null) {
            'featured' => $query->orderByDesc('is_featured')->latest(),
            'price_asc' => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'oldest' => $query->oldest(),
            'best_selling' => $query->orderByDesc('sold_count'),
            'stock_desc' => $query->withSum('variants', 'stock_quantity')->orderByDesc('variants_sum_stock_quantity'),
            'sale' => $query->where('discount_percent', '>', 0)->orderByDesc('discount_percent'),
            default => $query->latest(),
        };
    }
}
