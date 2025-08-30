<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Unit;

class ProductStoreRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:180|unique:products,slug',
            'sku' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'stock_qty' => 'required|numeric|min:0',
            'min_qty' => 'nullable|numeric|min:0',
            'max_qty' => 'nullable|numeric|min:0|gte:min_qty',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('unit_id')) {
                $unit = Unit::find($this->input('unit_id'));
                if ($unit && $unit->step == 1) {
                    // Check if quantities are integers for step=1 units
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
