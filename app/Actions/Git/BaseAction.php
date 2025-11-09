<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;
use App\Services\GitService;
use Illuminate\Database\Eloquent\Model;

abstract class BaseAction
{
    /** @var array<int,string>|string */
    public array|string $output;

    /** @var array<int,string> */
    protected array $arguments = [];

    protected string $git_cmd;

    public string $command;
    public int $exitCode;
    public float $durationMs;
    public string $method;
    public string $error;

    public function __construct(
        protected GitService $git,
        protected ?Deployment $deployment = null,
        mixed ...$args
    )
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
    public function execute(?Deployment $deployment = null): self
    {
        if ($deployment) {
            $this->deployment = $deployment;
        }

        if (! $this->deployment) {
            throw new \RuntimeException('Deployment not found.');
        }

        // Merge arguments into command
        $command = $this->buildCommand($this->deployment);
        if ($this->arguments !== []) {
            $command = array_merge($command, $this->arguments);
        }

        // Run via GitService (decides SSH vs local)
        $output = $this->git->runCommand($command, $this->deployment);

        // Child class parses output
        if ($output['exit_code'] === 0) {
            $this->output = $this->parseOutput($output['stdout']);
            $this->method = $output['method'];
            $this->exitCode = $output['exit_code'];
            $this->durationMs = $output['duration_ms'];
            $this->command = implode(' ', $output['command']);
            $this->error = $output['stderr'];

            return $this;
        }

        throw new \RuntimeException($output['stderr']);
    }

    /**
     * Base git command array, e.g. ['git', 'branch']
     *
     * @return array<int, string>
     */
    abstract protected function buildCommand(): array;

    /**
     * Parse raw command output
     *
     * @return array<int,string>|string
     */
    abstract protected function parseOutput(string $output): array|string;

    /**
     * Parse raw command output
     */
    abstract public function success(): bool;
}
