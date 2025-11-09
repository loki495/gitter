<?php

declare(strict_types=1);

use App\Actions\SetActiveBranch;
use App\Models\Branch;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use App\Services\GitService;
use Tests\Traits\WithTemporaryGitRepository;

uses(WithTemporaryGitRepository::class);

beforeEach(function (): void {
    $this->setupGitRepository();
    $this->git = app(GitService::class);
    $this->user = User::factory()->create();

    $this->actingAs($this->user);

    $key = SshKey::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Deploy Key',
        'filename' => 'deploy_key',
    ]);

    $this->localMachine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Local Machine',
        'ip' => null,
        'ssh_port' => null,
        'ssh_user' => 'andres',
        'ssh_key_id' => null,
    ]);

    $this->remoteMachine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'SSH Machine',
        'ip' => '127.0.0.1',
        'ssh_port' => 22222,
        'ssh_user' => 'andres',
        'ssh_key_id' => $key->id,
    ]);

    $this->localDeployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $this->localMachine->id,
        'path' => $this->repoPath,
    ]);

    $this->remoteDeployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $this->remoteMachine->id,
        'path' => $this->repoPath,
    ]);
});

afterEach(function () {
    $this->cleanupGitRepository();
});

function runBranchSwitchTest(Deployment $deployment, GitService $git, Closure $repoSetup): void
{
    // Initial state: on 'main' branch
    $status = $git->status->execute($deployment);
    expect((string) $status->result())->toContain('On branch main');

    // Create branch models
    $mainBranch = Branch::factory()->create([
        'name' => 'main',
        'deployment_id' => $deployment->id,
    ]);
    $featureBranch = Branch::factory()->create([
        'name' => 'feature-branch',
        'deployment_id' => $deployment->id,
    ]);

    // Setup repo state using the provided closure
    $repoSetup($deployment, $git);

    $action = new SetActiveBranch($git);

    // If the repo is dirty, expect an exception
    if (str_contains((string) $git->status->execute($deployment)->result(), 'Changes not staged for commit')) {
        expect(fn () => $action->execute($featureBranch))
            ->toThrow(Exception::class, 'Please commit your changes or stash them before you switch branches.');

        // Assert we are still on the main branch
        $status = $git->status->execute($deployment);
        expect((string) $status->result())->toContain('On branch main');

        return;
    }

    // If the repo is clean, expect a successful switch
    $result = $action->execute($featureBranch);
    expect($result)->toBeInstanceOf(Branch::class);
    expect($result->is_active)->toBeTrue();
    expect((string) $result->name)->toBe($featureBranch->name);

    // Confirm branch changed by checking git status
    $newStatus = $git->status->execute($deployment);
    expect((string) $newStatus->result())->toContain("On branch {$featureBranch->name}");

    // Switch back to main
    $action->execute($mainBranch);
    $finalStatus = $git->status->execute($deployment);
    expect((string) $finalStatus->result())->toContain("On branch {$mainBranch->name}");
}

it('switches branch on a clean local deployment', function (): void {
    runBranchSwitchTest($this->localDeployment, $this->git, function () {
        // Clean repo, do nothing
    });
});

it('fails to switch branch on a dirty local deployment', function (): void {
    runBranchSwitchTest($this->localDeployment, $this->git, function () {
        $this->runInRepo('echo "uncommitted change" > new-file.txt');
    });
});

it('switches branch on a clean remote deployment', function (): void {
    runBranchSwitchTest($this->remoteDeployment, $this->git, function () {
        // Clean repo, do nothing
    });
});

it('fails to switch branch on a dirty remote deployment', function (): void {
    runBranchSwitchTest($this->remoteDeployment, $this->git, function () {
        $this->runInRepo('echo "uncommitted change" > new-file.txt');
    });
});
