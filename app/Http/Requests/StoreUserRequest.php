<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreUserRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'full_name' => 'required|max:255|string',
            'email' => 'required|email|unique:users|max:255',
            'password' => ['required', Rules\Password::defaults()],
            'phone_number' => 'numeric|required|unique:users',
            'is_manager' => 'boolean|required',
            'managing_token' =>
                'required_if:is_manager,0|string|nullable|exists:users'
        ];
    }
    public function messages()
    {
        return [
            'managing_token.required_if' =>
                'Please request the owner for valid managing token!',
            'managing_token.exists' => 'Please use a valid managing token!'
        ];
    }
}
