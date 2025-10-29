<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Deployment;

/**
 * Policy for Deployment model.
 *
 * Uses BasePolicy::owns for ownership checks (maps created_by -> user_id at runtime).
 */
final class DeploymentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the deployment.
     *
     * @param User $user
     * @param Deployment $deployment
     * @return bool
     */
    public function view(User $user, Deployment $deployment): bool
    {
        // All authenticated users can view deployments by default.
        return $user !== null;
    }

    /**
     * Determine whether the user can create deployments.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Any authenticated user may create a deployment.
        return $user !== null;
    }

    /**
     * Determine whether the user can update the deployment.
     *
     * @param User $user
     * @param Deployment $deployment
     * @return bool
     */
    public function update(User $user, Deployment $deployment): bool
    {
        return $this->owns($user, $deployment);
    }

    /**
     * Determine whether the user can delete the deployment.
     *
     * @param User $user
     * @param Deployment $deployment
     * @return bool
     */
    public function delete(User $user, Deployment $deployment): bool
    {
        return $this->owns($user, $deployment);
    }
}
