<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;

class GalleryImagePolicy
{
    public function delete(User $user, GalleryImage $image): bool
    {
        return $user->isAdmin() || $image->school->user_id === $user->id;
    }
}
