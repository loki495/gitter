<?php

declare(strict_types=1);

use App\Actions\Machine\CheckMachineStatus;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Unit');

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->ssh_key = SshKey::factory()->create([
        'user_id' => $this->user->id,
        'filename' => 'deploy_key',
    ]);
    $this->local_machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '',
    ]);
    $this->remote_machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '127.0.0.1',
        'ssh_port' => 22222,
        'ssh_user' => 'andres',
        'ssh_key_id' => $this->ssh_key->id,
    ]);
    $this->actingAs($this->user);
});

it('returns reachable status for a machine without SSH key', function (): void {
    $action = app(CheckMachineStatus::class);
    $result = $action->execute($this->local_machine);

    expect($result['reachable'])->toBeTrue()
        ->and($result['stdout'])->toBe('ping')
        ->and($result['stderr'])->toBe('')
        ->and($result['command'])->toBe('ssh command executed');
});

it('returns reachable status for a machine with SSH key', function (): void {

    $action = app(CheckMachineStatus::class);
    $result = $action->execute($this->remote_machine);

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

    $action = app(CheckMachineStatus::class);
    $result = $action->execute($machine);

    expect($result['reachable'])->toBeFalse();
});
