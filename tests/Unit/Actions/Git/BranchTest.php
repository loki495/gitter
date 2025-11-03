<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\User;
use App\Models\Deployment;
use App\Services\GitService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('pulls branches successfully', function () {
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
    $branches = $git->branch
            ->addArgument('--no-color')
            ->execute($deployment);

    expect($branches)->toBe(['* git', 'main']);
});

it('fails to pull branches from wrong path', function () {
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
    $branches = $git->branch
            ->addArgument('--no-color')
            ->execute($deployment);

})->throws(Exception::class);
