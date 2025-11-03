<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Machine;
use App\Models\User;

final class MachinePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the machine.
     */
    public function view(User $user, Machine $machine): bool
    {
        return $this->owns($user, $machine);
    }

    /**
     * Determine whether the user can create a machine.
     */
    public function create(User $user): bool
    {
        return $user->exists;
    }

    /**
     * Determine whether the user can update the machine.
     */
    public function update(User $user, Machine $machine): bool
    {
        return $this->owns($user, $machine);
    }

    /**
     * Determine whether the user can delete the machine.
     */
    public function delete(User $user, Machine $machine): bool
    {
        return $this->owns($user, $machine);
    }
}
