<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    /**
     * Determine if the given model belongs to the user.
     */
    protected function owns(User $user, Model $model): bool
    {
        /** @phpstan-ignore-next-line */
        return $model->user_id === $user->id;
    }
}
