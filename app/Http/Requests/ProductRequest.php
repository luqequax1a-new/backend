<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IntegerIfStepOne;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $id = $this->route('product') ?? $this->route('id');
        
        return [
            'name' => 'sometimes|required|string|max:150',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('products')->ignore($id)
            ],
            'sku' => [
                'sometimes',
                'nullable',
                'string',
                'max:80',
                Rule::unique('products')->ignore($id)
            ],
            'description' => 'sometimes|nullable|string',
            'unit_id' => [
                'sometimes',
                'required',
                'exists:units,id',
                function ($attribute, $value, $fail) {
                    $unit = \App\Models\Unit::find($value);
                    if ($unit && !$unit->is_active) {
                        $fail('The selected unit is not active.');
                    }
                }
            ],
            'price' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'sometimes|required|numeric|min:0|max:100',
            'stock_qty' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
                new IntegerIfStepOne($unitId)
            ],
            'min_qty' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                new IntegerIfStepOne($unitId)
            ],
            'max_qty' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'gte:min_qty',
                new IntegerIfStepOne($unitId)
            ],
            'is_active' => 'sometimes|boolean',
            'store_id' => 'sometimes|nullable|exists:stores,id',
            'brand_id' => 'sometimes|nullable|exists:brands,id',
            'category_ids' => 'sometimes|nullable|array',
            'category_ids.*' => 'exists:categories,id'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        // Unit change validation moved to controller to return 409 status
    }
}
