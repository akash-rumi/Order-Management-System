<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null; // further checks in controller
    }

    public function rules()
    {
        return [
            'status' => ['required', Rule::in(['pending','processing','shipped','delivered','cancelled'])],
            'reason' => 'nullable|string|max:500',
        ];
    }
}
