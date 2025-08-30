<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Unit;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;
        
        return [
            'name' => 'sometimes|required|string|max:150',
            'slug' => 'sometimes|required|string|max:180|unique:products,slug,' . $productId,
            'sku' => 'sometimes|nullable|string|max:80',
            'description' => 'sometimes|nullable|string',
            'unit_id' => 'sometimes|required|exists:units,id',
            'price' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'sometimes|required|numeric|min:0',
            'stock_qty' => 'sometimes|required|numeric|min:0',
            'min_qty' => 'sometimes|nullable|numeric|min:0',
            'max_qty' => 'sometimes|nullable|numeric|min:0|gte:min_qty',
            'is_active' => 'sometimes|boolean',
            'images' => 'sometimes|nullable|array',
            'brand_id' => 'sometimes|nullable|exists:brands,id',
            'category_id' => 'sometimes|nullable|exists:categories,id',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if unit is being changed and stock > 0
            if ($this->route('product') && $this->input('unit_id')) {
                $product = $this->route('product');
                if ($product->unit_id != $this->input('unit_id') && $product->stock_qty > 0) {
                    $validator->errors()->add('unit_id', 'Stok sıfır değilken birim değiştirilemez.');
                }
            }
            
            // Check integer constraint for step=1 units
            if ($this->input('unit_id')) {
                $unit = Unit::find($this->input('unit_id'));
                if ($unit && $unit->step == 1) {
                    $fields = ['stock_qty', 'min_qty', 'max_qty'];
                    foreach ($fields as $field) {
                        $value = $this->input($field);
                        if ($value !== null && floor($value) != $value) {
                            $validator->errors()->add($field, "Seçili birim için {$field} alanı tamsayı olmalıdır.");
                        }
                    }
                }
            }
        });
    }
}
