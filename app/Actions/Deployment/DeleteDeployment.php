<?php

declare(strict_types=1);

namespace App\Actions\Deployment;

use App\Models\Deployment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteDeployment
{
    public function execute(int $id): bool
    {
        $deployment = Deployment::find($id);

        if (! $deployment) {
            throw new ModelNotFoundException('Deployment not found.');
        }

        return (bool) $deployment->delete();
    }
}
