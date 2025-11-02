<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use App\Services\CliRunner;
use App\Services\SshService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('builds ssh command and calls runner', function (): void {
    $sshKey = SshKey::factory()->create([
        'user_id' => $this->user->id
    ]);

    $machine = Machine::factory()->make([
        'ssh_user' => 'ubuntu',
        'ip' => '192.168.1.5',
        'ssh_key_id' => $sshKey->id,
        'ssh_port' => 2222,
        'user_id' => $this->user->id
    ]);

    $fakeRunner = Mockery::mock(CliRunner::class);
    $fakeRunner->shouldReceive('run')
        ->once()
        ->with(Mockery::on(function (array $cmd) use ($machine) {
            // Assert that ssh command is properly constructed
            return
                $cmd[0] == "/usr/bin/ssh" &&
                $cmd[1] == "-o" &&
                $cmd[2] == "StrictHostKeyChecking=no" &&
                $cmd[3] == "-o" &&
                $cmd[4] == "ConnectTimeout=5" &&
                $cmd[5] == "-o" &&
                $cmd[6] == "UserKnownHostsFile=/dev/null" &&
                $cmd[7] == "-o" &&
                $cmd[8] == "LogLevel=ERROR" &&
                $cmd[9] == "-i" &&
                $cmd[10] == $machine->sshKey->fullPath &&
                $cmd[11] == "-p" &&
                $cmd[12] == "2222" &&
                $cmd[13] == "ubuntu@192.168.1.5" &&
                $cmd[14] == "\"uptime\"" &&
                1;
        }))
        ->andReturn([
            'stdout' => 'ok',
            'stderr' => '',
            'exit_code' => 0,
        ]);

    $ssh = new SshService($fakeRunner);
    $result = $ssh->run($machine, 'uptime');

    expect($result['stdout'])->toBe('ok')
        ->and($result['stderr'])->toBe('')
        ->and($result['exit_code'])->toBe(0);
});

it('handles missing key path and port', function (): void {
    $machine = Machine::factory()->make([
        'ssh_user' => 'ubuntu',
        'ip' => '10.0.0.2',
        'ssh_key_path' => null,
        'ssh_port' => null,
    ]);

    $fakeRunner = Mockery::mock(CliRunner::class);
    $fakeRunner->shouldReceive('run')
        ->once()
        ->with(Mockery::on(function (array $cmd) use ($machine): bool {
            // Should not include -i or -p
            $joined = implode(' ', $cmd);

            return
                ! str_contains($joined, '-p')
                && str_contains($joined, "{$machine->ssh_user}@{$machine->ip}");
        }))
        ->andReturn(['stdout' => 'pong', 'stderr' => '', 'exit_code' => 0]);

    $ssh = new SshService($fakeRunner);
    $result = $ssh->run($machine, 'ping');

    expect($result['stdout'])->toBe('pong');
});
