<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Git\BaseAction;
use App\Models\Deployment;
use function Laravel\Prompts\warning;

class GitService
{

    public $lastCommand;
    public $lastOutput;
    public $lastExitCode;
    public $lastMethod;

    public function __construct(
        protected SshService $ssh,
        protected CliRunner $runner
    ) {}

    /**
     * Magic getter to resolve git subcommands dynamically
     */
    public function __get(string $name): BaseAction
    {
        $class = "\\App\\Actions\\Git\\" . ucfirst($name);

        if (!class_exists($class)) {
            throw new \RuntimeException("Git action class $class does not exist.");
        }

        return new $class($this); // inject GitService for execution
    }

    /**
     * Run git command either remotely or locally
     *
     * Called by BaseAction::execute()
     * @param array<int,mixed> $command
     * @return array{stdout:string,stderr:string,exit_code:int}
     */
    public function runCommand(array $command, Deployment $deployment): array
    {
        // Ensure the caller is a BaseAction subclass
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        $allowed = false;
        foreach ($backtrace as $frame) {
            if (isset($frame['class']) && $frame['class'] === BaseAction::class ||
                is_subclass_of($frame['class'], BaseAction::class)
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

        if ($deployment->is_local) {
            $result = $this->runner->run($command);
            $this->lastMethod = 'local';
        } else {
            $result = $this->ssh->run($deployment->machine, $command);
            $this->lastMethod = 'ssh';
        }

        $this->lastCommand = $command;
        $this->lastOutput = $result['stdout'];
        $this->lastExitCode = $result['exit_code'];

        return $result;
    }

}
