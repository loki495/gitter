<?php

namespace App\Actions\Machine;

use App\Models\Machine;

class DeleteMachine
{
    /**
     * Execute the action.
     *
     * @param  Machine  $machine
     * @return void
     */
    public function execute(Machine $machine): void
    {
        $machine->delete();
    }
}

