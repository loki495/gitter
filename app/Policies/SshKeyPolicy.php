<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SshKey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class SshKeyPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any SSH keys.
     */
    public function viewAny(User $user): bool
    {
        // Users can see their own keys
        return true;
    }

    /**
     * Determine whether the user can view a specific SSH key.
     */
    public function view(User $user, SshKey $sshKey): bool
    {
        return $this->owns($user, $sshKey);
    }

    /**
     * Determine whether the user can create SSH keys.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the SSH key.
     */
    public function update(User $user, SshKey $sshKey): bool
    {
        return $this->owns($user, $sshKey);
    }

    /**
     * Determine whether the user can delete the SSH key.
     */
    public function delete(User $user, SshKey $sshKey): bool
    {
        return $this->owns($user, $sshKey);
    }

    /**
     * Determine whether the user can restore the SSH key.
     */
    public function restore(User $user, SshKey $sshKey): bool
    {
        return $this->owns($user, $sshKey);
    }

    /**
     * Determine whether the user can permanently delete the SSH key.
     */
    public function forceDelete(User $user, SshKey $sshKey): bool
    {
        return $this->owns($user, $sshKey);
    }
}
