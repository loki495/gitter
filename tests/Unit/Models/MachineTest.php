<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can create a machine with minimal required fields', function (): void {
    $sshKey = SshKey::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $machine = Machine::create([
        'name' => 'Test Machine',
        'type' => 'work',
        'ip' => '127.0.0.1',
        'ssh_user' => 'ubuntu',
        'ssh_key_id' => $sshKey->id,
        'user_id' => $this->user->id,
    ]);

    expect($machine->id)->not()->toBeNull()
        ->and($machine->ssh_port)->toBe(22);

    $this->assertDatabaseHas('machines', [
        'id' => $machine->id,
        'name' => 'Test Machine',
        'type' => 'work',
        'ssh_user' => 'ubuntu',
    ]);
});

it('fails when required fields are missing', function (): void {
    Machine::create([
        'type' => 'work',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('has many deployments', function (): void {
    // Create a machine
    $machine = Machine::factory()->create();

    // Create some deployments linked to that machine
    $deployments = Deployment::factory()->count(2)->create([
        'machine_id' => $machine->id,
    ]);

    // Lazy-load relationship
    $related = $machine->deployments;

    // Assertions
    expect($related)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Deployment::class)
        ->and($related->pluck('id')->sort()->values()->all())
        ->toBe($deployments->pluck('id')->sort()->values()->all());
});
