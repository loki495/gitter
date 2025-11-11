<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Services\GitService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->repoPath = '/home/andres/www/git';
});

it('runs git status for a local deployment', function (): void {
    $machine = Machine::factory()->create([
        'name' => 'Localhost',
        'ip' => null,
        'ssh_port' => null,
    ]);

    $deployment = Deployment::factory()->create([
        'machine_id' => $machine->id,
        'path' => $this->repoPath,
    ]);

    $git = app(GitService::class);
    $result = $git->status->execute($deployment);

    expect($result->result())->toContain('On branch');
});

it('runs git status for a remote SSH deployment', function (): void {
    $key = SshKey::factory()->create([
        'name' => 'Deploy Key',
        'filename' => 'deploy_key',
    ]);

    $machine = Machine::factory()->create([
        'name' => 'SSH Machine',
        'ip' => '127.0.0.1',
        'ssh_port' => 22222,
        'ssh_key_id' => $key->id,
        'ssh_user' => 'andres',
    ]);

    $deployment = Deployment::factory()->create([
        'machine_id' => $machine->id,
        'path' => $this->repoPath,
    ]);

    $git = app(GitService::class);
    $result = $git->status->execute($deployment);

    expect($result->result())->toContain('On branch');
});

it('throws runtime exception if deployment path is missing', function (): void {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Deployment with path is required');

    $deployment = new Deployment();
    $deployment->path = null;

    $git = app(GitService::class);
    $git->status->execute($deployment);
});
