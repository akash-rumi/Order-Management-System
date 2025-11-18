<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() != null && $this->user()->hasAnyRole(['admin','vendor']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|max:255|distinct',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|lte:variants.*.price',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.initial_stock' => 'sometimes|integer|min:0'
        ];
    }
}
