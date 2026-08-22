<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;

class SchoolPolicy
{
    public function view(User $user, School $school): bool
    {
        return $user->isAdmin() || $school->user_id === $user->id;
    }

    public function update(User $user, School $school): bool
    {
        return $user->isAdmin() || $school->user_id === $user->id;
    }

    public function delete(User $user, School $school): bool
    {
        return $user->isAdmin();
    }
}
