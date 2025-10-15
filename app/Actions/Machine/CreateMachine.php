<?php

namespace App\Actions\Machine;

use App\Models\Machine;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateMachine
{
    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $data
     * @return Machine
     *
     * @throws ValidationException
     */
    public function execute(array $data): Machine
    {
        // Validate input
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'ssh_user' => ['required', 'string', 'max:255'],
            'ssh_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_key_path' => ['nullable', 'string', 'max:1024'],
            'ssh_password_encrypted' => ['nullable', 'string'],
            'ip' => ['nullable', 'ip'],
            'notes' => ['nullable', 'string'],
            'created_by' => ['nullable', 'exists:users,id'],
        ]);

        $validated = $validator->validate();

        // If ssh_password_encrypted is set, encrypt it
        if (!empty($validated['ssh_password_encrypted'])) {
            $validated['ssh_password_encrypted'] = encrypt($validated['ssh_password_encrypted']);
        }

        // Create the machine
        return Machine::create($validated);
    }
}

