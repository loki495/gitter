<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Facades\Gate;

final class DeleteBranch
{
    public function execute(Branch $branch): bool
    {
        Gate::authorize('delete', $branch);

        return (bool) $branch->delete();
    }
}
