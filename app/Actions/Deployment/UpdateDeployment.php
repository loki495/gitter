<?php
declare(strict_types=1);

namespace App\Actions\Deployment;

use App\Models\Deployment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class UpdateDeployment
{
    /**
     * @param Deployment $deployment
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    public function execute(Deployment $deployment, array $data): Deployment
    {
        $validated = Validator::make($data, [
            'path' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url'],
            'is_primary' => ['boolean'],
        ])->validate();

        $validated['updated_by'] = Auth::id();
        $deployment->update($validated);

        return $deployment->refresh();
    }
}

