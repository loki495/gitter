<?php

declare(strict_types=1);

namespace App\Actions\Deployment;

use App\Models\Deployment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class CreateDeployment
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): Deployment
    {
        $validated = Validator::make($data, [
            'website_id' => ['required', 'integer', 'exists:websites,id'],
            'machine_id' => ['required', 'integer', 'exists:machines,id'],
            'path' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url'],
            'is_primary' => ['boolean'],
        ])->validate();

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        /** @var array<string, mixed> $validated */
        return Deployment::create($validated);
    }
}
