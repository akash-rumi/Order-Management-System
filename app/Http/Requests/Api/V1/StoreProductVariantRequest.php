<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize()
    {
        // route middleware already restricts; still double-check vendor owns product if vendor role
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        // product route-model binding
        $product = $this->route('product');
        return $product && $product->vendor_id === $user->id;
    }

    public function rules()
    {
        return [
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|lte:price',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable',
            'initial_stock' => 'sometimes|integer|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
        ];
    }
}
