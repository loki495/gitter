<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Branch;
use App\Services\GitService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SetActiveBranch
{
    public function __construct(
        protected GitService $git
    ) {}

    /**
     * Switches the website's active branch.
     *
     * Steps:
     *  1. Authorize the user.
     *  2. Validate that the target branch exists and belongs to the website.
     *  3. Ask GitService to check out the branch.
     *  4. Only if Git succeeds, update DB in a transaction.
     *
     * @throws AuthorizationException
     */
    public function execute(Branch $branch): Branch
    {
        Gate::authorize('update', $branch);

        /** @var \App\Actions\Git\Checkout $action */
        $action = $this->git
            ->checkout($branch)
            ->execute();
        dd($action);

        if (! $action->success()) {
            // Prefer a domain-specific exception for clarity
            throw new \RuntimeException(
                sprintf('Failed to checkout branch "%s": %s', $branch->name, $action->errorOutput())
            );
        }

        // Step 2: persist new active state only after success
        DB::transaction(function () use ($branch): void {
            $branch->deployment->branches()
                ->where('id', '!=', $branch->id)
                ->update(['is_active' => false]);

            $branch->is_active = true;
            $branch->save();
        });

        return $branch;
    }
}
