<?php

declare(strict_types=1);

namespace App\Actions\Git;

class Status extends BaseAction
{
    /**
     * @return array<int,string>
     */
    protected function buildCommand(): array
    {
        if (!$this->deployment?->path) {
            throw new \RuntimeException('Deployment with path is required');
        }
        return [
            'cd',
            $this->deployment->path,
            '&&',
            $this->git_cmd,
            'status',
        ];
    }

    protected function parseOutput(string $output): string
    {
        return trim($output, " \t\n\r\0\x0B");
    }

}
