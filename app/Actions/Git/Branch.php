<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;

class Branch extends BaseAction
{
    /**
     * @return array<int,string>
     */
    protected function buildCommand(Deployment $deployment): array
    {
        return [
            'cd',
            $deployment->path,
            '&&',
            $this->git_cmd,
            'branch',
        ];
    }

    /**
     * @return array<int,string>
     */
    protected function parseOutput(string $output): array
    {
        return array_filter(array_map('trim', explode("\n", $output)));
    }
}
