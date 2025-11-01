<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Machine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SshService
{
    public function __construct(protected CliRunner $runner) {}

    /**
     * Run a command on a machine via SSH.
     *
     * @return array{stdout: string, stderr: string, exit_code: int}
     */
    public function run(Machine $machine, array|string $command): array
    {
        $sshCommand = [
            '/usr/bin/ssh',
            '-o', 'StrictHostKeyChecking=no',
            '-o', 'ConnectTimeout=5',
            //'-o', 'KexAlgorithms=diffie-hellman-group-exchange-sha256'
            '-o', 'UserKnownHostsFile=/dev/null',
            '-o', 'LogLevel=ERROR',
        ];

        if ($machine->sshKey) {
            $sshCommand[] = '-i';
            $sshCommand[] = Storage::path('ssh/'.Auth::id()) . '/' . $machine->sshKey->filename;
        }

        if ($machine->ssh_port) {
            $sshCommand[] = '-p';
            $sshCommand[] = (string) $machine->ssh_port;
        }

        $sshCommand[] = sprintf('%s@%s', $machine->ssh_user, $machine->ip ?? '127.0.0.1');

        if  (is_array($command)) {
            $sshCommand = implode(' ', $sshCommand);
            $sshCommand .= ' "' . implode(' ', $command).'"';
        } else{
            $sshCommand[] = '"' . $command . '"';
        }

        return $this->runner->run($sshCommand);
    }
}
