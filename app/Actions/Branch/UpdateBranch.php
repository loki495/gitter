<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class UpdateBranch
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Branch $branch, array $data): Branch
    {
        Gate::authorize('update', $branch);

        $validated = Validator::make($data, [
            'name' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9._\-\/]+$/',
                'unique:branches,name,'.$branch->id.',id,deployment_id,'.$branch->deployment_id,
            ],
            'is_active' => ['sometimes', 'boolean'],
            'is_tracking_remote' => ['sometimes', 'boolean'],
            'last_commit' => ['sometimes', 'nullable', 'string'],
            'last_checked_at' => ['sometimes', 'nullable', 'date'],
        ])->validate();

        $branch->fill([
            'name' => $validated['name'],
            'is_active' => (bool) ($validated['is_active'] ?? $branch->is_active),
            'is_tracking_remote' => (bool) ($validated['is_tracking_remote'] ?? $branch->is_tracking_remote),
            'last_commit' => $validated['last_commit'] ?? $branch->last_commit,
            'last_checked_at' => isset($validated['last_checked_at']) ? now() : $branch->last_checked_at,
        ]);

        // TODO: actual update via ssh
        // TODO: add log

        $branch->save();

        return $branch;
    }
}
