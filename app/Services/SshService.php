<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Machine;

class SshService
{
    public function __construct(protected CliRunner $runner) {}

    /**
     * Run a command on a machine via SSH.
     *
     * @return array{stdout: string, stderr: string, exit_code: int}
     */
    public function run(Machine $machine, string $command): array
    {
        $sshCommand = [
            'ssh',
            '-o', 'StrictHostKeyChecking=no',
            '-o', 'ConnectTimeout=5',
        ];

        if ($machine->ssh_key_path) {
            $sshCommand[] = '-i';
            $sshCommand[] = $machine->ssh_key_path;
        }

        if ($machine->ssh_port) {
            $sshCommand[] = '-p';
            $sshCommand[] = (string) $machine->ssh_port;
        }

        $sshCommand[] = sprintf('%s@%s', $machine->ssh_user, $machine->ip ?? '127.0.0.1');
        $sshCommand[] = $command;

        return $this->runner->run($sshCommand);
    }
}
