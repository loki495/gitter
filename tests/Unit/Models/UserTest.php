<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;

it('returns initials from full name', function (): void {
    $user = User::factory()->make(['name' => 'Ada Lovelace']);
    expect($user->initials())->toBe('AL');
});

it('returns single initial when name has one word', function (): void {
    $user = User::factory()->make(['name' => 'Plato']);
    expect($user->initials())->toBe('P');
});

it('handles extra spaces gracefully', function (): void {
    $user = User::factory()->make(['name' => '  Alan   Turing  ']);
    expect($user->initials())->toBe('AT');
});

it('can have machines', function (): void {
    $user = User::factory()->create();
    $machine = Machine::factory()->create(['user_id' => $user->id]);
    expect($user->machines)->toBeCollection()
        ->and($user->machines->first()->id)->toBe($machine->id);
});

it('can have ssh keys', function (): void {
    $user = User::factory()->create();
    $sshKey = SshKey::factory()->create(['user_id' => $user->id]);
    expect($user->sshKeys)->toBeCollection()
        ->and($user->sshKeys->first()->id)->toBe($sshKey->id);
});
