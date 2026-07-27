<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'type' => 'required|in:local,ssh',
            'host' => 'required_if:type,ssh|nullable|string|max:255',
            'port' => 'required_if:type,ssh|nullable|integer|min:1|max:65535',
            'username' => 'required_if:type,ssh|nullable|string|max:100',
            'auth_type' => 'required_if:type,ssh|in:password,key',
            'password' => 'nullable|string',
            'private_key' => 'nullable|string',
            'webserver_type' => 'required|in:nginx,apache',
            'config_path' => 'required|string|max:255',
            'symlink_path' => 'nullable|string|max:255',
            'reload_command' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ];
    }
}
