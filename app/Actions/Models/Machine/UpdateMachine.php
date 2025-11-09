<?php

declare(strict_types=1);

namespace App\Actions\Models\Machine;

use App\Models\Machine;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateMachine
{
    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(Machine $machine, array $data): ?Machine
    {
        Gate::authorize('update', $machine);

        // Validate input
        $validator = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'ssh_user' => ['sometimes', 'required', 'string', 'max:255'],
            'ssh_port' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_key_id' => ['required', 'exists:ssh_keys,id'],
            'ip' => ['sometimes', 'nullable', 'min:7'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        /** @var array<string, mixed> $validated * */
        $validated = $validator->validate();

        $machine->update($validated);

        return $machine->fresh();
    }
}
