<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HomepageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'hero_headline' => ['required', 'string', 'max:255'],
            'hero_headline_ar' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'hero_description_ar' => ['nullable', 'string', 'max:1000'],
            'schools_heading_prefix' => ['nullable', 'string', 'max:255'],
            'schools_heading_prefix_ar' => ['nullable', 'string', 'max:255'],
            'schools_heading_highlight' => ['nullable', 'string', 'max:255'],
            'schools_heading_highlight_ar' => ['nullable', 'string', 'max:255'],
            'hero_overlay_opacity' => ['required', 'integer', 'min:0', 'max:100'],
            'about_content' => ['nullable', 'string', 'max:5000'],
            'about_content_ar' => ['nullable', 'string', 'max:5000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'contact_address_ar' => ['nullable', 'string', 'max:255'],
            'support_info' => ['nullable', 'string', 'max:1000'],
            'support_info_ar' => ['nullable', 'string', 'max:1000'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'footer_text_ar' => ['nullable', 'string', 'max:500'],
            'site_background' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
