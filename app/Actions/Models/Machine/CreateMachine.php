<?php

declare(strict_types=1);

namespace App\Actions\Models\Machine;

use App\Models\Machine;
use App\Models\SshKey;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateMachine
{
    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): Machine
    {
        Gate::authorize('create', Machine::class);

        // Validate input
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'ssh_user' => ['required', 'string', 'max:255'],
            'ssh_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_key_id' => ['required', 'exists:ssh_keys,id'],
            'ip' => ['nullable', 'min:7', 'max:255'],
            'notes' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        /** @var array<string, mixed> $validated * */
        $validated = $validator->validate();

        SshKey::where('filename', $validated['ssh_key_id'])->first();

        // Create the machine
        return Machine::create($validated);
    }
}
