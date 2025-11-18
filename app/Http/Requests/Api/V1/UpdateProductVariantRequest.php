<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        $variant = $this->route('variant');
        $product = $variant ? $variant->product : null;
        return $product && $product->vendor_id === $user->id;
    }

    public function rules()
    {
        $variantId = $this->route('variant') ? $this->route('variant')->id : null;

        return [
            'sku' => ['sometimes','string','max:255', Rule::unique('product_variants','sku')->ignore($variantId)],
            'price' => 'sometimes|numeric|min:0',
            'sale_price' => 'nullable|numeric|lte:price',
            'attributes' => 'nullable|array',
            'initial_stock' => 'sometimes|integer|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
        ];
    }
}
