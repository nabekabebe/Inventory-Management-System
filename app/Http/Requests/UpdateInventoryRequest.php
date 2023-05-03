<?php

namespace App\Http\Requests;

use App\Traits\AuthAccessControl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
            'name' => 'string|max:255',
            'identifier' => 'required|max:255|unique:inventories',
            'manufacturer' => 'string',
            'description' => 'string',
            'barcode' => 'string|unique:inventories',
            'purchase_price' => $this->isManager() ? 'numeric' : 'missing',
            'sell_price' => $this->isManager() ? 'numeric' : 'missing',
            'category_id' =>
                'nullable|exists:categories,id,owner_token,' .
                $this->userToken()
        ];
    }
}
