<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
    protected $fillable = [
        'school_id',
        'path',
        'sort_order',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function url(): string
    {
        return \Storage::disk('public')->url($this->path);
    }
}
