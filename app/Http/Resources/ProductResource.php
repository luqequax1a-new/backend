<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit_id' => $this->unit_id,
            'price' => (float) $this->price,
            'tax_rate' => (float) $this->tax_rate,
            'stock_qty' => $this->formatQuantity($this->stock_qty),
            'min_qty' => $this->formatQuantity($this->min_qty),
            'max_qty' => $this->formatQuantity($this->max_qty),
            'is_active' => (bool) $this->is_active,
            'store_id' => $this->store_id,
            'brand_id' => $this->brand_id,
            'category_ids' => $this->whenLoaded('categories', function() {
                return $this->categories->pluck('id')->toArray();
            }),
            'unit' => $this->whenLoaded('unit', function() {
                return [
                    'id' => $this->unit->id,
                    'text' => $this->unit->text,
                    'step' => $this->unit->step
                ];
            }),
            'store' => $this->whenLoaded('store', function() {
                return [
                    'id' => $this->store->id,
                    'name' => $this->store->name
                ];
            }),
            'brand' => $this->whenLoaded('brand', function() {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name
                ];
            }),
            'categories' => $this->whenLoaded('categories', function() {
                return $this->categories->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name
                    ];
                });
            }),
            'image_url' => $this->whenLoaded('images', fn() => $this->images->first()?->url),
        ];
    }

    /**
     * Format stock quantity based on unit step.
     */
    private function formatStock()
    {
        if ($this->unit && $this->unit->step == 1) {
            return (int) $this->stock_qty; // tam sayı
        }
        return (float) $this->stock_qty;   // ondalık
    }
    
    /**
     * Format stock with unit text (e.g., "15.75 m").
     */
    private function formatStockWithUnit()
    {
        $formattedStock = $this->formatStock();
        $unitText = $this->unit ? $this->unit->text : '';
        
        return $formattedStock . ($unitText ? ' ' . $unitText : '');
    }

    /**
     * Format quantity fields based on unit step.
     */
    private function formatQuantity($quantity)
    {
        if ($quantity === null) {
            return null;
        }
        
        if ($this->unit && $this->unit->step == 1) {
            return (int) $quantity; // tam sayı
        }
        return (float) $quantity;   // ondalık
    }
}
