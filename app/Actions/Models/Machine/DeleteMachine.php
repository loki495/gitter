<?php

declare(strict_types=1);

namespace App\Actions\Models\Machine;

use App\Models\Machine;
use Illuminate\Support\Facades\Gate;

class DeleteMachine
{
    /**
     * Execute the action.
     */
    public function execute(Machine $machine): bool
    {
        Gate::authorize('delete', $machine);

        return (bool) $machine->delete();
    }
}
