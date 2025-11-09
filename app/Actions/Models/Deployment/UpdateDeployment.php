<?php

declare(strict_types=1);

namespace App\Actions\Models\Deployment;

use App\Models\Deployment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class UpdateDeployment
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(Deployment $deployment, array $data): Deployment
    {
        Gate::authorize('update', $deployment);

        $validated = Validator::make($data, [
            'path' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url'],
            'is_primary' => ['boolean'],
        ])->validate();

        /** @var array<string, mixed> $validated */
        $deployment->update($validated);

        return $deployment->refresh();
    }
}
