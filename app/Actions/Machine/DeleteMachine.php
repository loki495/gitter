<?php

declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\Machine;

class DeleteMachine
{
    /**
     * Execute the action.
     */
    public function execute(Machine $machine): void
    {
        $machine->delete();
    }
}
