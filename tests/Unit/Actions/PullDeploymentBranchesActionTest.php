<?php

declare(strict_types=1);

use App\Actions\Git\BaseAction;
use App\Actions\Git\Branch as BranchAction;
use App\Actions\PullDeploymentBranches;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use App\Services\CliRunner;
use App\Services\GitService;
use App\Services\SshService;

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

it('throws an exception when the git branch action fails', function (): void {
    // Arrange
    $deployment = Deployment::factory()->create();
    $errorMessage = 'fatal: not a git repository';

    $branchActionMock = Mockery::mock(BranchAction::class);
    $branchActionMock->shouldReceive('addArgument')->with('--no-color')->andReturnSelf();
    $branchActionMock->shouldReceive('execute')->with($deployment)->andReturn($branchActionMock);
    $branchActionMock->shouldReceive('success')->once()->andReturn(false);
    $branchActionMock->outputRaw = $errorMessage;

    // Create a fake GitService that overrides __get
    $fakeGitService = new class(mock(SshService::class), mock(CliRunner::class)) extends GitService
    {
        public BranchAction $branchMock;

        public function __get(string $name): BaseAction
        {
            if ($name === 'branch') {
                return $this->branchMock;
            }

            return parent::__get($name);
        }
    };
    $fakeGitService->branchMock = $branchActionMock;

    $action = new PullDeploymentBranches($fakeGitService);

    // Act & Assert
    expect(fn (): array => $action->execute($deployment))
        ->toThrow(RuntimeException::class, "Error refreshing branches:\n".$errorMessage);
});
