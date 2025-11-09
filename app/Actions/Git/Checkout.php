<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Branch;
use App\Models\Deployment;
use App\Services\GitService;
use function Laravel\Prompts\multisearch;

class Checkout extends BaseAction
{

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
