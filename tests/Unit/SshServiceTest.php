<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Services\CliRunner;
use App\Services\SshService;

it('builds ssh command and calls runner', function (): void {
    $machine = Machine::factory()->make([
        'ssh_user' => 'ubuntu',
        'ip' => '192.168.1.5',
        'ssh_key_path' => '/home/ubuntu/.ssh/id_rsa',
        'ssh_port' => 2222,
    ]);

    $fakeRunner = Mockery::mock(CliRunner::class);
    $fakeRunner->shouldReceive('run')
        ->once()
        ->with(Mockery::on(fn (array $cmd): bool =>
            // Assert that ssh command is properly constructed
            $cmd[0] === 'ssh'
            && in_array('-o', $cmd, true)
            && in_array('StrictHostKeyChecking=no', $cmd, true)
            && in_array('-p', $cmd, true)
            && in_array((string) $machine->ssh_port, $cmd, true)
            && in_array($machine->ssh_key_path, $cmd, true)
            && in_array("{$machine->ssh_user}@{$machine->ip}", $cmd, true)
            && str_ends_with((string) end($cmd), 'uptime')))
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

            return ! str_contains($joined, '-i')
                && ! str_contains($joined, '-p')
                && str_contains($joined, "{$machine->ssh_user}@{$machine->ip}");
        }))
        ->andReturn(['stdout' => 'pong', 'stderr' => '', 'exit_code' => 0]);

    $ssh = new SshService($fakeRunner);
    $result = $ssh->run($machine, 'ping');

    expect($result['stdout'])->toBe('pong');
});
