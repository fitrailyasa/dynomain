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
            'webserver_type' => 'required|in:nginx,apache',
            'target_type' => 'required|in:proxy,webroot,laravel,wordpress,redirect',
            'target_destination' => 'nullable|string|max:255',
            'redirect_url' => 'nullable|string|max:255',
            'server_id' => 'required|exists:servers,id',
            'ssl_type' => 'required|in:none,cloudflare,certbot,custom',
            'ssl_cert_path' => 'nullable|string|max:255',
            'ssl_key_path' => 'nullable|string|max:255',
            'custom_nginx_config' => 'nullable|string',
            'custom_config_mode' => 'nullable|in:default,replace,add',
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
            'domain_id.required' => 'Domain is required.',
        ];
    }
}
