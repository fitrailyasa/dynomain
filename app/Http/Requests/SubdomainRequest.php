<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Subdomain;
use Illuminate\Validation\Rule;

class SubdomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $db = new Subdomain();

        // dd($db->getConnection()->getDatabaseName());

        $id = $this->route('subdomain');

        return [
            'name' => [
                'required',
                'max:100',
                Rule::unique('subdomains', 'name')->ignore($id),
            ],
            'ip' => 'required|ip',
            'status' => 'required|in:0,1',
            'domain_id' => 'required|exists:domains,id',
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
            'domain_id.required' => 'Domain is required.',
            'domain_id.exists' => 'Domain does not exist.',
        ];
    }
}
