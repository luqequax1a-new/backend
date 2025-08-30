<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImage;
use App\Support\Slugger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::select([
            'id', 'name', 'sku', 'price', 'stock_qty', 
            'unit_id', 'store_id', 'brand_id', 'is_active', 'created_at'
        ])->with(['unit:id,name,text,step', 'store:id,name', 'brand:id,name', 'images:id,product_id,url']);

        // Query parameter - search
        if ($request->filled('query')) {
            $searchTerm = $request->get('query');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by brand_id
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->get('brand_id'));
        }

        // Filter by category_id
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('category_id', $request->get('category_id'));
            });
        }

        // Filter by store_id
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->get('store_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->get('status') === 'active';
            $query->where('is_active', $isActive);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('dir', 'desc');
        
        if ($sort === 'latest') {
            $query->latest();
        } else {
            $query->orderBy($sort, $dir);
        }

        // Pagination
        $pageSize = $request->get('pageSize', 15);
        $products = $query->paginate($pageSize);

        return response()->json(ProductResource::collection($products));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);
        
        // slug boşsa name'den üret
        if (empty($validated['slug'])) {
            $validated['slug'] = Slugger::unique($validated['name']);
        } else {
            $validated['slug'] = Slugger::unique($validated['slug']); // çakışıyorsa artır
        }
        
        $product = Product::create($validated);
        
        // Sync categories if provided
        if (!empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }
        
        $product->load([
            'unit:id,name,text,step',
            'store:id,name',
            'brand:id,name',
            'categories:id,name',
            'images:id,product_id,url'
        ]);

        return response()->json(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load([
            'unit:id,name,text,step',
            'store:id,name',
            'brand:id,name',
            'categories:id,name',
            'images:id,product_id,url'
        ]);
        return response()->json(new ProductResource($product));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        // Check for unit change guard before validation
        if ($request->filled('unit_id') && $product->unit_id != $request->input('unit_id') && $product->stock_qty > 0) {
            return response()->json([
                'message' => 'UNIT_CHANGE_FORBIDDEN_WHEN_STOCK',
                'errors' => [
                    'unit_id' => ['UNIT_CHANGE_FORBIDDEN_WHEN_STOCK']
                ]
            ], 409);
        }
        
        // Enhanced step validation for integer-only units
        if ($product->unit && $product->unit->step == 1.0) {
            $integerFields = ['stock_qty', 'min_qty', 'max_qty'];
            foreach ($integerFields as $field) {
                if ($request->filled($field) && !is_int($request->input($field)) && floor($request->input($field)) != $request->input($field)) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            $field => ['Bu ürün birimi tam sayı gerektirir.']
                        ]
                    ], 422);
                }
            }
        }
        
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? null;
        unset($validated['category_ids']);
        
        // Handle slug logic
        if (array_key_exists('slug', $validated)) {
            $base = $validated['slug'] ?: $validated['name'] ?? $product->name;
            $validated['slug'] = Slugger::unique($base, $product->id);
        } elseif (array_key_exists('name', $validated)) {
            // ad değişirse slug'ı elle vermediyse dokunma (mevcut slug korunur)
            // istenirse: $validated['slug'] = Slugger::unique($validated['name'], $product->id);
        }
        
        $product->update($validated);
        
        // Sync categories if provided
        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }
        
        $product->load([
            'unit:id,name,text,step',
            'store:id,name',
            'brand:id,name',
            'categories:id,name',
            'images:id,product_id,url'
        ]);

        return response()->json(new ProductResource($product));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Toggle product status.
     */
    public function toggleStatus(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $product->update([
            'is_active' => $request->get('is_active')
        ]);

        return response()->json([
            'message' => 'Product status updated successfully',
            'is_active' => $product->is_active
        ]);
    }

    /**
     * Duplicate a product.
     */
    public function duplicate($id): JsonResponse
    {
        $src = Product::with(['unit','store','brand','images','categories'])->findOrFail($id);

        $new = $src->replicate([
            'slug', 'sku' // bunları kopyalama
        ]);
        $new->name = 'copy- '.$src->name;
        $new->slug = Slugger::unique($new->name);
        $new->is_active = false; // taslak olsun
        $new->push();

        // İlişkileri kopyala (ihtiyacına göre)
        if ($src->relationLoaded('categories')) {
            $new->categories()->sync($src->categories->pluck('id')->all());
        }
        if ($src->relationLoaded('images') && $src->images->count()) {
            foreach ($src->images as $img) {
                $new->images()->create([
                    'path' => $img->path,   // dosyayı fiziksel kopyalamıyorsak referans kalsın
                    'alt'  => $img->alt,
                    'sort' => $img->sort,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product duplicated.',
            'data'    => new ProductResource($new->load(['unit','store','brand','images','categories'])),
        ], 201);
    }
}
