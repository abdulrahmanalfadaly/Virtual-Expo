<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'allow_registration' => ['nullable', 'boolean'],
            'allow_applications' => ['nullable', 'boolean'],
            'require_admin_approval' => ['nullable', 'boolean'],
            'link_preview_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
