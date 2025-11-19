<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Git\BaseAction;
use App\Models\Deployment;

/**
 * @property-read \App\Actions\Git\Branch $branch
 * @property-read \App\Actions\Git\Status $status
 *
 * @method \App\Actions\Git\Checkout checkout(\App\Models\Branch $branch)
 */
class GitService
{
    public function __construct(
        protected SshService $ssh,
        protected CliRunner $runner
    ) {}

    /**
     * Magic getter to resolve git subcommands dynamically
     */
    public function __get(string $name): BaseAction
    {
        $class = '\\App\\Actions\\Git\\'.ucfirst($name);

        if (! class_exists($class)) {
            throw new \RuntimeException("Git action class $class does not exist.");
        }

        /** @var BaseAction $instance */
        $instance = new $class($this);

        return $instance;
    }

    /**
     * Magic caller to resolve git subcommands dynamically
     *
     * @param  array<int,mixed>  $arguments
     */
    public function __call(string $name, array $arguments): BaseAction
    {
        $class = '\\App\\Actions\\Git\\'.ucfirst($name);

        if (! class_exists($class)) {
            throw new \RuntimeException("Git action class $class does not exist.");
        }

        /** @var BaseAction $instance */
        $instance = new $class($this, null, ...$arguments);

        return $instance;
    }

    /**
     * Run git command either remotely or locally
     *
     * Called by BaseAction::execute()
     *
     * @param  array<int,string>  $command
     * @return array{
     *      stdout: string,
     *      stderr: string,
     *      exit_code: int,
     *      method: 'local'|'ssh',
     *      command: array<int, string>
     * }
     */
    public function runCommand(array $command, Deployment $deployment): array
    {
        // Ensure the caller is a BaseAction subclass
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        $allowed = false;
        foreach ($backtrace as $frame) {
            if (isset($frame['class']) &&
                ($frame['class'] === BaseAction::class ||
                is_subclass_of($frame['class'], BaseAction::class))
            ) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            throw new \RuntimeException(
                'GitService::runCommand() can only be called from a Git BaseAction subclass.'
            );
        }

        if (! $deployment->machine) {
            throw new \RuntimeException('Deployment has no machine.');
        }

        $result = [
            'stdout' => '',
            'stderr' => '',
            'exit_code' => 0,
            'duration_ms' => 0,
            'method' => '',
            'command' => '',
        ];

        if ($deployment->is_local) {
            $result = $this->runner->run($command);
            $result['method'] = 'local';
        } else {
            $result = $this->ssh->run($deployment->machine, $command);
            $result['method'] = 'ssh';
        }

        $result['command'] = $command;

        return $result;
    }
}
