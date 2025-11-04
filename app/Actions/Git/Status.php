<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;

class Status extends BaseAction
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
            'status',
        ];
    }

    protected function parseOutput(string $output): string
    {
        return trim($output, " \t\n\r\0\x0B");
    }

    public function success(): bool
    {
        return $this->output !== '';
    }
}
