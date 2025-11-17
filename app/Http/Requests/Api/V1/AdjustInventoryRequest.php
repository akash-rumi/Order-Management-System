<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize()
    {
        // admin/vendor guarded by middleware, but double-check:
        return $this->user() !== null && $this->user()->hasAnyRole(['admin','vendor']);
    }

    public function rules()
    {
        return [
            // delta: positive to increase stock, negative to decrease (e.g. -3)
            'delta' => ['required','integer'],
            'reason' => ['nullable','string','max:255'],
            // optional: change low_stock_threshold
            'low_stock_threshold' => ['nullable','integer','min:0'],
        ];
    }
}
