<?php

declare(strict_types=1);

namespace App\Actions\Git;

use App\Models\Deployment;
use App\Services\GitService;

abstract class BaseAction
{
    /** @var array<int,mixed>|string */
    public array|string $parsedOutput;

    public string $outputRaw;

    /** @var array<int,string> */
    protected array $arguments = [];

    protected string $git_cmd;

    public string $command;

    public int $exitCode;

    public float $durationMs;

    public string $method;

    public string $error;

    // @phpstan-ignore constructor.unusedParameter
    public function __construct(
        protected GitService $git,
        protected ?Deployment $deployment = null,
        mixed ...$args
    ) {
        $this->git_cmd = trim(shell_exec('which git') ?: '', " \n");
    }

    /**
     * Add argument to the Git command, chainable
     */
    public function addArgument(string $arg): self
    {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Execute the git command for a deployment
     */
    public function execute(?Deployment $deployment = null): self
    {
        if ($deployment instanceof \App\Models\Deployment) {
            $this->deployment = $deployment;
        }

        if (! $this->deployment instanceof \App\Models\Deployment) {
            // TODO: needs coverage
            throw new \RuntimeException('Deployment not found.');
        }

        // Merge arguments into command
        $command = $this->buildCommand();
        if ($this->arguments !== []) {
            $command = array_merge($command, $this->arguments);
        }

        // Run via GitService (decides SSH vs local)
        /** @var array{
         *     stdout: string,
         *     stderr: string,
         *     exit_code: int,
         *     duration_ms: int,
         *     method: string,
         *     command: array<int, string>
         * } $output
         */
        $output = $this->git->runCommand($command, $this->deployment);

        // Child class parses output
        if ($output['exit_code'] === 0) {
            $this->outputRaw = $output['stdout'];
            $this->method = $output['method'];
            $this->exitCode = $output['exit_code'];
            $this->durationMs = $output['duration_ms'];
            $this->command = implode(' ', $output['command']);
            $this->error = $output['stderr'];

            $this->result();

            return $this;
        }

        throw new \RuntimeException($output['stderr']);
    }

    public function result(): mixed
    {
        return $this->parsedOutput ??= $this->parseOutput($this->outputRaw);
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
     * @return array<int,mixed>|string
     */
    abstract protected function parseOutput(string $output): array|string;

    /**
     * Assume success if parsed output is not empty
     */
    public function success(): bool
    {
        return isset($this->parsedOutput) && ($this->parsedOutput !== '' && $this->parsedOutput !== '0' && $this->parsedOutput !== []);
    }
}
