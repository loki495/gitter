<?php

declare(strict_types=1);

namespace App\Actions\Git;

class Branch extends BaseAction
{
    /** @var array<int,array{name:string,active:bool}> */
    public array $branches;

    /**
     * @return array<int,string>
     */
    protected function buildCommand(): array
    {
        if (! $this->deployment?->path) {
            throw new \RuntimeException('Deployment with path is required');
        }

        return [
            'cd',
            $this->deployment->path,
            '&&',
            $this->git_cmd,
            'branch',
        ];
    }

    /**
     * @return array<int,string>|string
     */
    protected function parseOutput(string $output): array|string
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
}
