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
            'site_name_ar' => ['nullable', 'string', 'max:255'],
            'allow_registration' => ['nullable', 'boolean'],
            'allow_applications' => ['nullable', 'boolean'],
            'require_admin_approval' => ['nullable', 'boolean'],
            'link_preview_image' => ['nullable', 'image', 'max:2048'],
            'expo_logo' => ['nullable', 'image', 'max:2048'],
            'show_site_name_in_nav' => ['nullable', 'boolean'],
            'nav_logo_height' => ['required', 'integer', 'min:1'],
            'expo_starts_at' => ['nullable', 'date'],
            'expo_days' => ['nullable', 'integer', 'min:1', 'max:60'],
            'dev_mode_enabled' => ['nullable', 'boolean'],
            'dev_mode_message' => ['nullable', 'string', 'max:2000'],
            'dev_mode_ends_at' => ['nullable', 'date'],
        ];
    }
}
