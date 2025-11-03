<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;

class Branch extends BaseAction
{
    protected function buildCommand(Deployment $deployment): array
    {
        return [
            'cd',
            $deployment->path,
            '&&',
            $this->git_cmd,
            'branch'
        ];
    }

    protected function parseOutput(string $output): array
    {
        return array_filter(array_map('trim', explode("\n", $output)));
    }
}
