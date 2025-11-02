<?php

declare(strict_types=1);

use App\Actions\Branch\PullDeploymentBranches;
use App\Models\User;
use App\Models\Machine;
use App\Models\Deployment;
use App\Services\GitService;
use App\Services\SshService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('pulls branches locally if machine has no IP', function () {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '127.0.0.1',
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
    ]);

    // Inject a mock GitService
    $mockCli = Mockery::mock(GitService::class);
    $mockCli->shouldReceive('runCommand')
        ->once()
        ->andReturn([
            'stdout' => 'main
develop',
            'exit_code' => 0,
            'stderr' => '',
        ]);

    $action = new PullDeploymentBranches($mockCli);

    $branches = $action->execute($deployment);

    expect($branches)->toBe(['master', 'develop']);
})->only();

it('pulls branches remotely if machine has IP', function () {
    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => 1,
    ]);

    $mock = Mockery::mock(PullDeploymentBranches::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods();
    $mock->shouldReceive('fetchRemoteBranches')->once()->andReturn(['develop']);

    $branches = $mock->execute($deployment);

    expect($branches)->toContain('develop');
})->skip();

it('pulls branches locally when machine has no IP', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $action = Mockery::mock(PullDeploymentBranches::class)->makePartial();

    $action->shouldAllowMockingProtectedMethods();
    $action->shouldReceive('fetchLocalBranches')->once()->andReturn(['main', 'develop']);

    $branches = $action->execute($deployment);

    expect($branches)->toBeArray()
        ->and($branches)->toContain('main')
        ->and($branches)->toContain('develop');
})->skip();

it('pulls branches remotely when machine has IP', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '192.168.0.5',
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
    ]);

    $sshMock = Mockery::mock(SshService::class);
    $sshMock->shouldReceive('run')->once()->andReturn("main\ndevelop");

    app()->instance(SshService::class, $sshMock);

    $action = new PullDeploymentBranches();
    $branches = $action->execute($deployment);

    expect($branches)->toContain('main')->and($branches)->toContain('develop');
})->skip();

it('throws an exception if remote fetch fails', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '192.168.0.6',
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
    ]);

    $sshMock = Mockery::mock(SshService::class);
    $sshMock->shouldReceive('run')->andThrow(new Exception('SSH failure'));
    app()->instance(SshService::class, $sshMock);

    $action = new PullDeploymentBranches();

    expect(fn () => $action->execute($deployment))->toThrow(Exception::class);
})->skip();

it('throws an exception if local fetch fails', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);

    $action = Mockery::mock(PullDeploymentBranches::class)->makePartial();
    $action->shouldAllowMockingProtectedMethods();
    $action->shouldReceive('fetchLocalBranches')->andThrow(new Exception('Local git error'));

    expect(fn () => $action->execute($deployment))->toThrow(Exception::class);
})->skip();

