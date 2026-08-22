<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoothRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSchool();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'full_description' => ['nullable', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'zoom_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
