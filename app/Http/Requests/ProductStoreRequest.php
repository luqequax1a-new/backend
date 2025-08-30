<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IntegerIfStepOne;
use Illuminate\Validation\Rule;

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
        $unitId = $this->input('unit_id');
        
        return [
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:180|unique:products,slug',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'unit_id' => [
                'required',
                'exists:units,id',
                function ($attribute, $value, $fail) {
                    $unit = \App\Models\Unit::find($value);
                    if ($unit && !$unit->is_active) {
                        $fail('The selected unit is not active.');
                    }
                }
            ],
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'stock_qty' => [
                'required',
                'numeric',
                'min:0',
                new IntegerIfStepOne($unitId)
            ],
            'min_qty' => [
                'nullable',
                'numeric',
                'min:0',
                new IntegerIfStepOne($unitId)
            ],
            'max_qty' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_qty',
                new IntegerIfStepOne($unitId)
            ],
            'is_active' => 'boolean',
            'store_id' => 'nullable|exists:stores,id',
            'brand_id' => 'nullable|exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id'
        ];
    }
}