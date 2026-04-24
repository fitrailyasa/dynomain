<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Domain;
use Illuminate\Validation\Rule;

class DomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $db = new Domain();

        // dd($db->getConnection()->getDatabaseName());

        $id = $this->route('domain');

        return [
            'name' => [
                'required',
                'max:100',
                Rule::unique('domains', 'name')->ignore($id),
            ],
            'ip' => 'required|ip',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must be under 100 chars.',
            'name.unique' => 'Name already exists.',
            'ip.required' => 'IP is required.',
            'ip.ip' => 'IP must be a valid IP address.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be 0 or 1.',
        ];
    }
}
