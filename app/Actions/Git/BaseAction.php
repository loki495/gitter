<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;
use App\Services\GitService;

abstract class BaseAction
{
    /** @var array<int,string> */
    protected array $arguments = [];

    protected string $git_cmd;

    public function __construct(protected GitService $git)
    {
        $this->git_cmd = trim(shell_exec('which git') ?: '', " \n");
    }

    /**
     * Add argument to the Git command, chainable
     */
    public function addArgument(string $arg, ?string $value = null): self
    {
        $this->arguments[] = $arg;
        if ($value !== null) {
            $this->arguments[] = $value;
        }

        return $this;
    }

    /**
     * Execute the git command for a deployment
     */
    public function execute(Deployment $deployment): mixed
    {
        // Merge arguments into command
        $command = $this->buildCommand($deployment);
        if ($this->arguments !== []) {
            $command = array_merge($command, $this->arguments);
        }

        // Run via GitService (decides SSH vs local)
        $output = $this->git->runCommand($command, $deployment);

        // Child class parses output
        if ($output['exit_code'] === 0) {
            return $this->parseOutput($output['stdout']);
        }

        throw new \RuntimeException($output['stderr']);
    }

    /**
     * Base git command array, e.g. ['git', 'branch']
     *
     * @return array<int, string>
     */
    abstract protected function buildCommand(Deployment $deployment): array;

    /**
     * Parse raw command output
     */
    abstract protected function parseOutput(string $output): mixed;
}
