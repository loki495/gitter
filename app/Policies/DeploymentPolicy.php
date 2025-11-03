<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deployment;
use App\Models\User;

/**
 * Policy for Deployment model.
 *
 * Uses BasePolicy::owns for ownership checks (maps created_by -> user_id at runtime).
 */
final class DeploymentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the deployment.
     */
    public function view(User $user, Deployment $deployment): bool
    {
        // All authenticated users can view deployments by default.
        return $user instanceof \App\Models\User;
    }

    /**
     * Determine whether the user can create deployments.
     */
    public function create(User $user): bool
    {
        // Any authenticated user may create a deployment.
        return $user instanceof \App\Models\User;
    }

    /**
     * Determine whether the user can update the deployment.
     */
    public function update(User $user, Deployment $deployment): bool
    {
        return $this->owns($user, $deployment);
    }

    /**
     * Determine whether the user can delete the deployment.
     */
    public function delete(User $user, Deployment $deployment): bool
    {
        return $this->owns($user, $deployment);
    }
}
