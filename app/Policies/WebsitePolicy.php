<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Website;

/**
 * Policy for Website model.
 *
 * Handles authorization for viewing, creating, updating and deleting websites.
 *
 * Uses BasePolicy::owns for ownership checks (maps created_by -> user_id at runtime).
 */
final class WebsitePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the website.
     */
    public function view(User $user, Website $website): bool
    {
        return $user instanceof \App\Models\User;
    }

    /**
     * Determine whether the user can create websites.
     */
    public function create(User $user): bool
    {
        return $user instanceof \App\Models\User;
    }

    /**
     * Determine whether the user can update the website.
     */
    public function update(User $user, Website $website): bool
    {
        return $this->owns($user, $website);
    }

    /**
     * Determine whether the user can delete the website.
     */
    public function delete(User $user, Website $website): bool
    {
        return $this->owns($user, $website);
    }
}
