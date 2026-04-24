<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Era;
use App\Models\Franchise;
use App\Models\Category;

class TableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'perPage' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Search must be a string.',
            'search.max' => 'Search must not exceed 255 characters.',
            'perPage.integer' => 'Per Page must be an integer.',
            'perPage.in' => 'Per Page must be 10, 50, or 100.',
        ];
    }
}
