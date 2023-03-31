<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'comment' => 'required|max:255',
            'payment_method' => 'required|in:cash,bank',
            'transaction_type' => 'required|in:sell,refunded',
            'inventory_id' =>
                'required|exists:inventories,id,owner_token,' .
                Auth()->user()->managing_token
        ];
    }
}
