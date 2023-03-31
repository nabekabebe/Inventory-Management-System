<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
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
            'name' => 'required|max:255',
            'description' => 'required|string',
            'barcode' => 'required|string|unique:inventories',
            'purchase_price' => 'required|numeric',
            'variation.*.quantity' => 'numeric',
            'variation.*.size' => 'string',
            'variation.*.color' => 'json',
            'brand' => 'string',
            'manufacturer' => 'string',
            'sell_price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'category_id' =>
                'required|exists:categories,id,owner_token,' .
                Auth()->user()->managing_token,
            'warehouse_id' =>
                'required|exists:warehouses,id,owner_token,' .
                Auth()->user()->managing_token
        ];
    }
}
