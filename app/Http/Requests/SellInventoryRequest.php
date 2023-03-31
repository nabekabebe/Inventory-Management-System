<?php

namespace App\Http\Requests;

use App\Traits\AuthAccessControl;
use Illuminate\Foundation\Http\FormRequest;

class SellInventoryRequest extends FormRequest
{
    use AuthAccessControl;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'warehouse_id' =>
                'required|exists:warehouses,id,owner_token,' .
                $this->userToken(),
            'comment' => 'string',
            'payment_method' => 'required|in:cash,bank',
            'quantity' => 'required|numeric'
        ];
    }
}
