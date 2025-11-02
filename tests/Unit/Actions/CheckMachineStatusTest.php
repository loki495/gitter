<?php

declare(strict_types=1);

use App\Actions\Machine\CheckMachineStatus;
use App\Models\Machine;
use App\Models\User;
use App\Services\SshService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Unit');

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('returns reachable status for a machine without SSH key', function (): void {
    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
        'ip' => '127.0.0.1',
        'user_id' => $this->user->id,
    ]);

    $fakeSsh = Mockery::mock(SshService::class);
    $fakeSsh->shouldReceive('run')
        ->once()
        ->with($machine, 'echo "ping"')
        ->andReturn([
            'stdout' => 'ping',
            'stderr' => '',
            'exit_code' => 0,
        ]);

    $this->app->instance(SshService::class, $fakeSsh);

    $action = app(CheckMachineStatus::class);
    $result = $action->execute($machine);

    expect($result['reachable'])->toBeTrue()
        ->and($result['stdout'])->toBe('ping')
        ->and($result['stderr'])->toBe('')
        ->and($result['command'])->toBe('ssh command executed');
});

it('returns reachable status for a machine with SSH key', function (): void {
    $machine = Machine::factory()->create([
        'ssh_user' => 'admin',
        'ip' => '192.168.1.50',
    ]);

    $fakeSsh = Mockery::mock(SshService::class);
    $fakeSsh->shouldReceive('run')
        ->once()
        ->with($machine, 'echo "ping"')
        ->andReturn([
            'stdout' => 'ping',
            'stderr' => '',
            'exit_code' => 0,
        ]);

    $this->app->instance(SshService::class, $fakeSsh);

    $action = app(CheckMachineStatus::class);
    $result = $action->execute($machine);

    expect($result['reachable'])->toBeTrue()
        ->and($result['stdout'])->toBe('ping')
        ->and($result['stderr'])->toBe('')
        ->and($result['command'])->toBe('ssh command executed');
});

it('returns unreachable status when SSH command fails', function (): void {
    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
        'ip' => '10.0.0.1',
    ]);

    $fakeSsh = Mockery::mock(SshService::class);
    $fakeSsh->shouldReceive('run')
        ->once()
        ->with($machine, 'echo "ping"')
        ->andThrow(new \RuntimeException('Connection refused')); // 👈 simulate exception

    $this->app->instance(SshService::class, $fakeSsh);

    $action = app(CheckMachineStatus::class);
    $result = $action->execute($machine);

    expect($result['reachable'])->toBeFalse()
        ->and($result['stdout'])->toBe('')
        ->and($result['stderr'])->toBe('Connection refused')
        ->and($result['command'])->toBeNull();
});
