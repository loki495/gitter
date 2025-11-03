<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    /**
     * Determine if the given model belongs to the user.
     */
    protected function owns(User $user, mixed $model): bool
    {
        return $model->user_id === $user->id;
    }
}
