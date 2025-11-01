<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DeploymentLog;
use App\Models\User;

final class DeploymentLogPolicy extends BasePolicy
{
    public function view(User $user, DeploymentLog $log): bool
    {
        return $log->deployment && $log->deployment->user_id === $user->id;
    }

    public function create(User $user, DeploymentLog $log): bool
    {
        return $log->deployment && $log->deployment->user_id === $user->id;
    }

    public function update(User $user, DeploymentLog $log): bool
    {
        return $log->deployment && $log->deployment->user_id === $user->id;
    }

    public function delete(User $user, DeploymentLog $log): bool
    {
        return $log->deployment && $log->deployment->user_id === $user->id;
    }
}

