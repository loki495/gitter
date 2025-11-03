<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->machine = Machine::factory()->create(['user_id' => $this->user->id]);
});

it('belongs to a user', function (): void {
    $key = SshKey::factory()->for($this->user)->create();

    expect($key->user->id)->toBe($this->user->id);
});

it('can belong to a machine', function (): void {
    $key = SshKey::factory()->for($this->user)->create();
    $this->machine->sshKey()->associate($key);
    $this->machine->save();

    expect($key->machines)->toBeCollection()
        ->and($key->machines->first()->id)->toBe($this->machine->id);
});

it('casts type to string and fingerprint to nullable string', function (): void {
    $key = SshKey::factory()->create([
        'type' => 'private',
        'fingerprint' => 'abc123',
    ]);

    expect($key->type)->toBe('private')
        ->and($key->fingerprint)->toBe('abc123');
});

it('has expected default attributes', function (): void {
    $key = new SshKey;

    expect($key->exists)->toBeFalse()
        ->and($key->created_at)->toBeNull()
        ->and($key->updated_at)->toBeNull();
});

test('we can get all deployments for a key', function (): void {
    $key = SshKey::factory()->for($this->user)->create();

    $user = User::factory()->create();
    $key = SshKey::factory()->for($user)->create();
    $machine = Machine::factory()->for($user)->create();
    $machine->sshKey()->associate($key);
    $machine->save();

    // Create deployments that belong to the same machine
    $deploymentA = Deployment::factory()->for($machine)->for($user)->create([
        'path' => '/srv/projectA',
    ]);
    $deploymentB = Deployment::factory()->for($machine)->for($user)->create([
        'path' => '/srv/projectB',
    ]);

    expect($key->deployments)->toHaveCount(2)
        ->and($key->deployments->pluck('id')->toArray())->toBe([$deploymentA->id, $deploymentB->id]);

});
