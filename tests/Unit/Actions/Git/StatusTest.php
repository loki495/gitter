<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\Machine;
use App\Models\User;
use App\Services\GitService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('pulls branches successfully', function (): void {
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

    $git = app(GitService::class);
    $status = $git->status
        ->addArgument('--branch', 'main')
        ->execute($deployment);

    expect($status)->toContain('On branch');
});

it('fails to pull branches from wrong path', function (): void {
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

    $git = app(GitService::class);
    $status = $git->status
        ->addArgument('--branch', 'main')
        ->execute($deployment);

})->throws(Exception::class);
