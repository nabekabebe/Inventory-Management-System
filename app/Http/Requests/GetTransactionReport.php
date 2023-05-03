<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTransactionReport extends FormRequest
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
            'year' =>
                'required_if:report_type,annually|nullable|numeric|before_or_equal:' .
                now()->year,
            'month' =>
                'required_if:report_type,monthly|nullable|numeric|gte:1|lte:12',
            'week' => 'nullable|numeric|gte:1|lte:4',
            'report_type' => 'required|in:weekly,monthly,annually'
        ];
    }
}
