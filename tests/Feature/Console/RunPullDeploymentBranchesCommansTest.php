<?php

declare(strict_types=1);

use App\Console\Commands\RunPullDeploymentBranches;
use App\Models\Deployment;
use App\Models\Machine;
use App\Services\GitService;
use Illuminate\Console\Command;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    $this->user = \App\Models\User::factory()->create();
});

it('fails when the deployment does not exist', function (): void {
    $result = artisan(RunPullDeploymentBranches::class, [
        'deployment_id' => 9999,
    ]);

    $result->expectsOutput('Deployment with ID 9999 not found.')
        ->assertExitCode(Command::FAILURE);
});

it('fails when the action throws an exception', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '',
        'ssh_key_id' => null,
        'ssh_user' => null,
        'ssh_port' => null,
        'name' => 'localhost',
    ]);

    // Invalid path should trigger exception in the action
    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/nonexistent/path/for/testing',
    ]);

    $result = $this->artisan(RunPullDeploymentBranches::class, [
        'deployment_id' => $deployment->id,
    ]);

    $result
        ->expectsOutput("Running PullDeploymentBranches for Deployment ID {$deployment->id}...")
        ->expectsOutputToContain('Action failed')
        ->assertFailed();
});

it('runs the PullDeploymentBranches action successfully', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '',
        'ssh_key_id' => null,
        'ssh_user' => null,
        'ssh_port' => null,
        'name' => 'localhost',
    ]);

    // You already use a local test repo path for these tests
    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/home/andres/www/git',
    ]);

    $git = app(GitService::class);
    $result = $git->branch
        ->addArgument('--no-color')
        ->execute($deployment);

    expect($result->result())->toHaveKey('0.name', 'git')
        ->and($result->result())->toHaveKey('0.active', true)
        ->and($result->result())->toHaveKey('1.name', 'main')
        ->and($result->result())->toHaveKey('1.active', false);

    $result = artisan(RunPullDeploymentBranches::class, [
        'deployment_id' => $deployment->id,
    ]);

    $result
        ->expectsOutput("Running PullDeploymentBranches for Deployment ID {$deployment->id}...")
        ->expectsOutput('✅ PullDeploymentBranches completed successfully.')
        ->assertExitCode(Command::SUCCESS);
});
