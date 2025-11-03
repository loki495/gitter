<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Branch;
use App\Models\Deployment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class CreateBranch
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Deployment $deployment, array $data): Branch
    {
        Gate::authorize('create', [Branch::class, $deployment]);

        $validated = Validator::make($data, [
            'name' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9._\-\/]+$/',
                'unique:branches,name,NULL,id,deployment_id,'.$deployment->id,
            ],
            'is_active' => ['sometimes', 'boolean'],
            'is_tracking_remote' => ['sometimes', 'boolean'],
            'last_commit' => ['sometimes', 'nullable', 'string'],
            'last_checked_at' => ['sometimes', 'nullable', 'date'],
        ])->validate();

        return Branch::create([
            'deployment_id' => $deployment->id,
            'name' => $validated['name'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_tracking_remote' => (bool) ($validated['is_tracking_remote'] ?? false),
            'last_commit' => $validated['last_commit'] ?? null,
            'last_checked_at' => isset($validated['last_checked_at']) ? now() : null,
        ]);
    }
}
