<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\GithubSsh;
use Illuminate\Validation\Rule;

class GithubSshRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('github_ssh');

        return [
            'name' => [
                'required',
                'max:100',
                Rule::unique('github_ssh', 'name')->ignore($id),
            ],
            'username' => 'required|string|max:100',
            'pat' => $id ? 'nullable|string|max:255' : 'required|string|max:255',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must be under 100 chars.',
            'name.unique' => 'Name already exists.',
            'username.required' => 'GitHub username is required.',
            'pat.required' => 'Personal Access Token is required.',
            'status.required' => 'Status is required.',
        ];
    }
}
