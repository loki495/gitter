<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Branch;
use App\Models\Deployment;
use App\Models\User;

final class BranchPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the branch.
     */
    public function view(User $user, Branch $branch): bool
    {
        return $branch->deployment && $branch->deployment->user_id === $user->id;
    }

    public function create(User $user, Deployment $deployment): bool
    {
        return $deployment->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the branch.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $branch->deployment && $branch->deployment->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the branch.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $branch->deployment && $branch->deployment->user_id === $user->id;
    }
}
