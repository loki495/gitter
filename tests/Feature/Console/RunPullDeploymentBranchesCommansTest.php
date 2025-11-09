<?php

declare(strict_types=1);

use App\Console\Commands\RunPullDeploymentBranches;
use App\Models\Deployment;
use App\Models\Machine;
use App\Services\GitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tests\Traits\WithTemporaryGitRepository;

use function Pest\Laravel\artisan;

uses(WithTemporaryGitRepository::class);

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
    $this->setupGitRepository();

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
        'path' => $this->repoPath,
    ]);

    $git = app(GitService::class);
    $result = $git->branch
        ->addArgument('--no-color')
        ->execute($deployment);

    $branches = collect($result->result());
    $mainBranch = $branches->firstWhere('name', 'main');
    $featureBranch = $branches->firstWhere('name', 'feature-branch');

    expect($mainBranch['active'])->toBeTrue();
    expect($featureBranch['active'])->toBeFalse();

    $result = artisan(RunPullDeploymentBranches::class, [
        'deployment_id' => $deployment->id,
    ]);
    $result->run();

    $result
        ->expectsOutput("Running PullDeploymentBranches for Deployment ID {$deployment->id}...")
        ->expectsOutput('✅ PullDeploymentBranches completed successfully.')
        ->assertExitCode(Command::SUCCESS);

    $this->cleanupGitRepository();
})->only();
