<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\User;
use App\Policies\MachinePolicy;

it('allows a user to view, update, delete their own machine', function () {
    $user = User::factory()->create();
    $machine = Machine::factory()->create(['user_id' => $user->id]);

    $policy = new MachinePolicy();

    expect($policy->view($user, $machine))->toBeTrue()
        ->and($policy->update($user, $machine))->toBeTrue()
        ->and($policy->delete($user, $machine))->toBeTrue();
});

it('denies a user from viewing, updating, deleting machines they do not own', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $machine = Machine::factory()->create(['user_id' => $otherUser->id]);

    $policy = new MachinePolicy();

    expect($policy->view($user, $machine))->toBeFalse()
        ->and($policy->update($user, $machine))->toBeFalse()
        ->and($policy->delete($user, $machine))->toBeFalse();
});

it('allows any user to create a machine', function () {
    $user = User::factory()->create();
    $policy = new MachinePolicy();

    expect($policy->create($user))->toBeTrue();
});

