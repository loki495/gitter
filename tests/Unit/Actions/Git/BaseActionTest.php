<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Git;

use App\Actions\Git\Status;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\User;
use App\Services\GitService;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('gets status successfully', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'localhost',
        'ip' => '',
        'ssh_key_id' => null,
        'ssh_user' => null,
    ]);

    $deployment = Deployment::factory()->create([
        'user_id' => $this->user->id,
        'machine_id' => $machine->id,
        'path' => '/home/andres/www/git',
    ]);

    $git = app(GitService::class);
    $result = $git->status
        ->addArgument('--short')
        ->execute($deployment);

    expect($result->success())->toBeTrue();
    expect($result->result())->toBeString();
});

it('throws_runtime_exception_if_deployment_is_not_found', function (): void {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Deployment not found.');

    // TODO: needs coverage
    $action = new Status(app(GitService::class), null);

    $action->execute();
});
