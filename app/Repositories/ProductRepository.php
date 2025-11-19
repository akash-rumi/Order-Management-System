<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()->with(['variants.inventory','vendor']);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('slug', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        return $query->paginate($perPage);
    }

    public function findByIdWithRelations(int $id): ?Product
    {
        return Product::with(['variants.inventory','vendor'])->find($id);
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
    
    /**
     * Full-text search using MATCH...AGAINST
     * Returns a Collection of products (paginated handled by caller)
     */
    public function searchFullText(string $q, int $perPage = 15)
    {
        // Sanitize query
        $q = trim($q);
        if ($q === '') return collect();

        // Use MATCH...AGAINST for relevance
        $query = Product::query()
            ->selectRaw('products.*, MATCH(name, description) AGAINST (? IN NATURAL LANGUAGE MODE) as relevance', [$q])
            ->whereRaw('MATCH(name, description) AGAINST (? IN NATURAL LANGUAGE MODE)', [$q])
            ->orderByDesc('relevance')
            ->with(['variants.inventory','vendor']);

        return $query->paginate($perPage);
    }
}
