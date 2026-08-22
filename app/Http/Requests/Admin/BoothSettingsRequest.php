<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BoothSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'booth_template' => ['nullable', 'image', 'mimes:png', 'max:4096'],
            'booth_logo_x' => ['required', 'numeric', 'min:0', 'max:100'],
            'booth_logo_y' => ['required', 'numeric', 'min:0', 'max:100'],
            'booth_logo_width' => ['required', 'numeric', 'min:20', 'max:60'],
            'booth_logo_max_height' => ['required', 'numeric', 'min:6', 'max:18'],
            'booth_name_curve' => ['required', 'numeric', 'min:0', 'max:160'],
            'booth_name_x' => ['required', 'numeric', 'min:0', 'max:100'],
            'booth_name_y' => ['required', 'numeric', 'min:0', 'max:100'],
            'booth_grid_columns' => ['required', 'integer', 'min:1', 'max:4'],
            'booth_grid_gap' => ['required', 'numeric', 'min:0', 'max:20'],
            'booth_modal_opacity' => ['required', 'integer', 'min:10', 'max:100'],
        ];
    }
}
