<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Branch;
use App\Models\Deployment;
use App\Services\GitService;

class Checkout extends BaseAction
{
    public function __construct(protected GitService $git, protected Branch $branch)
    {
        parent::__construct($git);
    }

    /**
     * @return array<int,string>
     */
    protected function buildCommand(Deployment $deployment): array
    {
        return [
            'cd',
            $this->branch->deployment->path,
            '&&',
            $this->git_cmd,
            'checkout',
            $this->branch->name,
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
