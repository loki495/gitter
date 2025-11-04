<?php

declare(strict_types=1);

use App\Actions\Git\BaseAction;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use App\Services\GitService;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->ssh_key = SshKey::factory()->create([
        'user_id' => $this->user->id,
        'filename' => 'deploy_key',
    ]);
    $this->machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'ip' => '127.0.0.1',
        'ssh_port' => 22222,
        'ssh_user' => 'andres',
        'ssh_key_id' => $this->ssh_key->id,
    ]);
    $this->deployment = Deployment::factory()->make([
        'user_id' => $this->user->id,
        'machine_id' => $this->machine->id,
        'path' => '/home/andres/www/git',
        'url' => 'https://example.com',
        'is_primary' => true,
    ]);
    $this->actingAs($this->user);
});

// Named class extending BaseAction for testing
class TestGitAction extends BaseAction
{
    public function __construct(public GitService $git) {}

    public function executeCommand(array $command, Deployment $deployment): array
    {
        return $this->git->runCommand($command, $deployment);
    }

    // Implement abstract methods
    protected function buildCommand(Deployment $deployment): array
    {
        return [
            'cd',
            $deployment->path,
            '&&',
            'git',
            'status',
        ];
    }

    protected function parseOutput(string $output): mixed
    {
        return $output ?? null;
    }

    protected function success(): bool
    {
        return $output;
    }
}

it('throws exception when accessing non-existent git action', function (): void {
    $nonExistent = 'nope';

    $this->git = app(GitService::class);
    $this->git->{$nonExistent};
})->throws(\RuntimeException::class, 'Git action class \App\Actions\Git\Nope does not exist.');

it('throws exception when runCommand is called outside a BaseAction', function (): void {
    $this->git = app(GitService::class);
    $this->git->runCommand(['git', 'status'], $this->deployment);
})->throws(\RuntimeException::class, 'GitService::runCommand() can only be called from a Git BaseAction subclass.');

it('runs ssh command when deployment is not local', function (): void {

    $this->git = app(GitService::class);

    $action = app(TestGitAction::class);
    $result = $action->execute($this->deployment);

    expect($result)->toContain('On branch ');
    expect($this->git->lastMethod)->toBe('ssh');
    expect($this->git->lastCommand)->toBe([
        'cd',
        $this->deployment->path,
        '&&',
        'git',
        'status',
    ]);
    expect($this->git->lastOutput)->toContain('On branch ');
    expect($this->git->lastExitCode)->toBe(0);
});

it('runs local command from a BaseAction subclass', function (): void {
    $this->machine->ip = null;
    $this->machine->save();
    $this->machine->refresh();

    $this->git = app(GitService::class);

    $action = app(TestGitAction::class);
    $result = $action->execute($this->deployment);

    expect($result)->toContain('On branch ');
    expect($this->git->lastCommand)->toBe([
        'cd',
        $this->deployment->path,
        '&&',
        'git',
        'status',
    ]);

    expect($this->git->lastMethod)->toBe('local');
    expect($this->git->lastExitCode)->toBe(0);
});

it('throws exception when runCommand is called with a deployment with no machine', function (): void {
    $this->git = app(GitService::class);

    $deployment = Deployment::factory()->make([
        'user_id' => $this->user->id,
        'machine_id' => null,
        'path' => '/home/andres/www/git',
        'url' => 'https://example.com',
        'is_primary' => true,
    ]);
    $this->git->status->execute($deployment);
})->throws(\RuntimeException::class, 'Deployment has no machine.');
