<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Git;

use App\Models\Branch;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\User;
use App\Services\GitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithTemporaryGitRepository;

uses(RefreshDatabase::class, WithTemporaryGitRepository::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->setupGitRepository();
});

afterEach(function (): void {
    $this->cleanupGitRepository();
});

it('checks out a branch successfully', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'localhost',
        'ip' => '', // Ensure local execution
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => $this->repoPath,
    ]);

    $branch = Branch::factory()->create([
        'deployment_id' => $deployment->id,
        'name' => 'feature-branch',
    ]);

    $git = app(GitService::class);
    $result = $git->checkout($branch)->execute();

    expect($result->success())->toBeTrue();

    $currentBranch = $this->runInRepo('git rev-parse --abbrev-ref HEAD');
    expect(trim($currentBranch))->toBe('feature-branch');
});

it('fails to checkout a non-existent branch', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'localhost',
        'ip' => '',
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => $this->repoPath,
    ]);

    $branch = Branch::factory()->create([
        'deployment_id' => $deployment->id,
        'name' => 'non-existent-branch',
    ]);

    $git = app(GitService::class);
    $git->checkout($branch)->execute();
})->throws(\RuntimeException::class);

it('throws exception if deployment path is null', function (): void {
    $deployment = Deployment::factory()->make(['path' => null]);
    $branch = Branch::factory()->make();
    $branch->setRelation('deployment', $deployment);

    $git = app(GitService::class);
    $git->checkout($branch)->execute();
})->throws(\RuntimeException::class, 'Deployment with path is required');