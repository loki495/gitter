<?php

declare(strict_types=1);

use App\Actions\Branch\PullDeploymentBranches;
use App\Models\SshKey;
use App\Models\User;
use App\Models\Machine;
use App\Models\Deployment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('pulls branches locally if machine has no ip', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '',
        'ssh_key_id' => null,
        'ssh_user' => null,
        'ssh_port' => null,
        'name' => 'localhost',
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/home/andres/www/git',
    ]);

    $action = app(PullDeploymentBranches::class);

    $branches = $action->execute($deployment);

    expect($branches)->toBeArray()
        ->and($branches[0]['name'])->toBe('git')
        ->and($branches[1]['name'])->toBe('main');

});

it('pulls branches remotely if machine has ip', function (): void {
    $ssh_key = SshKey::factory()->create([
        'user_id' => 1,
        'filename' => 'deploy_key',
        'type' => 'private',
    ]);
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '192.168.1.145',
        'ssh_user' => 'andres',
        'ssh_key_id' => $ssh_key->id,
        'ssh_port' => 22222,
    ]);
    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/home/andres/www/git',
    ]);

    $action = app(PullDeploymentBranches::class);
    $branches = $action->execute($deployment);

    expect($branches)->toBeArray()
        ->and($branches[0]['name'])->toBe('git')
        ->and($branches[1]['name'])->toBe('main');
});


it('throws an exception if remote fetch fails', function (): void {
    $ssh_key = SshKey::factory()->create([
        'user_id' => 1,
        'filename' => 'deploy_key',
        'type' => 'private',
    ]);
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '192.168.1.3',
        'ssh_user' => 'andres',
        'ssh_key_id' => $ssh_key->id,
        'ssh_port' => 22222,
    ]);
    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/unknown/path',
    ]);

    $action = app(PullDeploymentBranches::class);

    expect(fn () => $action->execute($deployment))->toThrow(Exception::class);
});

it('throws an exception if local fetch fails', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '',
        'ssh_key_id' => null,
        'ssh_user' => null,
        'ssh_port' => null,
        'name' => 'localhost',
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/unknown/path',
    ]);

    $action = app(PullDeploymentBranches::class);

    expect(fn () => $action->execute($deployment))->toThrow(Exception::class);
});

