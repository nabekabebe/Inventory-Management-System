<?php

namespace App\Http\Requests;

use App\Traits\AuthAccessControl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransferRequest extends FormRequest
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
            'source_id' =>
                'required|exists:warehouses,id,owner_token,' .
                $this->userToken(),
            'destination_id' =>
                'required|different:source_id|exists:warehouses,id,owner_token,' .
                $this->userToken(),
            'inventory_id' =>
                'required|exists:transfers,id,owner_token,' .
                $this->userToken(),
            'quantity' => 'required|numeric'
        ];
    }
}
