<?php

declare(strict_types=1);

namespace App\Actions\Deployment;

use App\Models\Deployment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

final class DeleteDeployment
{
    public function execute(Deployment $deployment): bool
    {
        Gate::authorize('delete', $deployment);
        return (bool) $deployment->delete();
    }
}
