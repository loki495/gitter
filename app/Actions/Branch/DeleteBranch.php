<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Branch;

final class UpdateBranch
{
    /**
    * @param array<string, mixed> $data
    **/
    public function execute(Branch $branch, array $data): Branch
    {
        $branch->update($data);
        return $branch;
    }
}
