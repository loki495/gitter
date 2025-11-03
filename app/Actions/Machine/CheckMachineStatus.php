<?php

declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\Machine;
use App\Services\CliRunner;
use App\Services\SshService;

class CheckMachineStatus
{
    public function __construct(protected CliRunner $runner, protected SshService $ssh) {}

    /**
     * Execute the action.
     *
     * @return array<string, mixed>
     */
    public function execute(Machine $machine): array
    {
        try {
            $result = $machine->ip === '' ? $this->runner->run(['echo', 'ping']) : $this->ssh->run($machine, ['echo', 'ping']);

            return [
                'reachable' => $result['exit_code'] === 0,
                'stdout' => $result['stdout'],
                'stderr' => $result['stderr'],
                'command' => $result['exit_code'] === 0 ? 'ssh command executed' : null,
            ];
        } catch (\Exception $e) {
            return [
                'reachable' => false,
                'stdout' => '',
                'stderr' => $e->getMessage(),
                'command' => null,
            ];
        }
    }
}
