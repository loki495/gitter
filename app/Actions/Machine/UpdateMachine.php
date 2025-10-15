<?php

namespace App\Actions\Machine;

use App\Models\Machine;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateMachine
{
    /**
     * Execute the action.
     *
     * @param  Machine  $machine
     * @param  array<string, mixed>  $data
     * @return Machine
     *
     * @throws ValidationException
     */
    public function execute(Machine $machine, array $data): Machine
    {
        // Validate input
        $validator = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'ssh_user' => ['sometimes', 'required', 'string', 'max:255'],
            'ssh_port' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_key_path' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'ssh_password_encrypted' => ['sometimes', 'nullable', 'string'],
            'ip' => ['sometimes', 'nullable', 'ip'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'updated_by' => ['nullable', 'exists:users,id'],
        ]);

        $validated = $validator->validate();

        // Encrypt password if provided
        if (array_key_exists('ssh_password_encrypted', $validated) && !empty($validated['ssh_password_encrypted'])) {
            $validated['ssh_password_encrypted'] = encrypt($validated['ssh_password_encrypted']);
        }

        $machine->update($validated);

        return $machine->fresh();
    }
}

