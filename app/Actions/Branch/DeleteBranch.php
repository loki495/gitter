<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Facades\Gate;

final class DeleteBranch
{
    /**
    * @param array<string, mixed> $data
    **/
    public function execute(Branch $branch): Branch
    {
        Gate::authorize('delete', $branch);
        return (bool) $branch->delete();
    }
}
