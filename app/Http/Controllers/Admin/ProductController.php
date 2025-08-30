<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
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
        $query = Product::with('unit:id,name,text,step');
        
        // Search by name or sku
        if ($request->has('q')) {
            $search = $request->get('q');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Filter by unit_id
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->get('unit_id'));
        }
        
        // Filter by is_active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);
        
        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage()
            ]
        ]);
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
            'brand:id,name',
            'store:id,name',
            'images:id,product_id,url',
            'categories:id,name'
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
            'brand:id,name',
            'store:id,name',
            'images:id,product_id,url',
            'categories:id,name'
        ]);
        return response()->json(new ProductResource($product));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        // Guard: Prevent unit change when stock > 0
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
            'brand:id,name',
            'store:id,name',
            'images:id,product_id,url',
            'categories:id,name'
        ]);
        
        return response()->json(new ProductResource($product));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 204);
    }
    
    /**
     * Update product status.
     */
    public function updateStatus(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);
        
        $product->update([
            'is_active' => $request->boolean('is_active')
        ]);
        
        $product->load([
            'unit:id,name,text,step',
            'brand:id,name',
            'store:id,name',
            'images:id,product_id,url',
            'categories:id,name'
        ]);
        
        return response()->json(new ProductResource($product));
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
        if ($src->categories->count()) {
            $new->categories()->sync($src->categories->pluck('id')->all());
        }
        if ($src->images && $src->images->count()) {
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
