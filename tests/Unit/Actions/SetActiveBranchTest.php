<?php

declare(strict_types=1);

use App\Actions\Branch\SetActiveBranch;
use App\Models\Branch;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use App\Services\GitService;

beforeEach(function (): void {
    $this->repoPath = '/home/andres/www/git';
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

function runBranchSwitchTest(Deployment $deployment, GitService $git): void {
    $status = $git->status->execute($deployment);

    $currentBranch = preg_match('/^On branch (.*)/', $status->output, $matches) ? $matches[1] : null;
    if (! $currentBranch) {
        dd('no branch', $status->output);
    }

    // Get all branches
    $branches = $git->branch()->execute($deployment)->output;

    // Pick a branch that is not currently active
    $targetBranch = collect($branches)->first(fn($b) => $b['name'] !== $currentBranch)['name'];

    expect($targetBranch)->not()->toBeNull();

    $branch = Branch::factory()->create([
        'name' => $targetBranch,
        'deployment_id' => $deployment->id,
    ]);
    $action = new SetActiveBranch($git);
    try {
        $result = $action->execute($branch);
        dd($result);
        expect($result->output)->toContain('Please commit your changes or stash them before you switch branches.');

        // Otherwise, confirm branch changed
        $newStatus = $git->status($deployment)->execute($deployment);
        expect(trim($newStatus->output->branch))->toBe($targetBranch);

        // Cleanup: switch back to original branch
        $git->checkout($deployment, $currentBranch);

    } catch (\Exception $e) {
        $output = $e->getMessage();
        expect($output)->toContain('Please commit your changes or stash them before you switch branches.');
    }
}

it('switches branch on a local deployment safely', function (): void {
    runBranchSwitchTest($this->localDeployment, $this->git);
})->only();

it('switches branch on a remote deployment safely', function (): void {
    runBranchSwitchTest($this->remoteDeployment, $this->git);
});

