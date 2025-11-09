<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;
use App\Services\GitService;

class Branch extends BaseAction
{
    /**
     * @return array<int,string>
     */
    protected function buildCommand(): array
    {
        return [
            'cd',
            $this->deployment->path,
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
        foreach (explode("\n", $output) as $line) {
            $active = false;
            if (str_starts_with($line, '*')) {
                $line = trim(substr($line, 1));
                $active = true;
            }
            $this->branches[] = [
                'name' => trim($line),
                'active' => $active,
            ];
        }

        return $this->branches;
    }

    public function success(): bool
    {
        return $this->output !== '';
    }
}
