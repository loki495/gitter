<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Branch;
use App\Services\GitService;

class Checkout extends BaseAction
{
    // @phpstan-ignore constructor.unusedParameter,missingType.parameter
    public function __construct(
        protected GitService $git,
        $dummy,
        public Branch $branch
    ) {
        parent::__construct($git, $this->branch->deployment);
    }

    /**
     * @return array<int,string>
     */
    protected function buildCommand(): array
    {
        if (!$this->branch->deployment?->path) {
            throw new \RuntimeException('Deployment with path is required');
        }

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
}
