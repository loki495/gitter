<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\User;
use App\Policies\MachinePolicy;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('allows owner to manage machine and creation for any user', function (): void {
    $machine = new Machine;
    $machine->user_id = $this->user->id;

    $nonOwnerMachine = new Machine;
    $nonOwnerMachine->user_id = $this->otherUser->id;

    $policy = new MachinePolicy;

    expect($policy->view($this->user, $machine))->toBeTrue()
        ->and($policy->update($this->user, $machine))->toBeTrue()
        ->and($policy->delete($this->user, $machine))->toBeTrue()
        ->and($policy->view($this->user, $nonOwnerMachine))->toBeFalse()
        ->and($policy->update($this->user, $nonOwnerMachine))->toBeFalse()
        ->and($policy->delete($this->user, $nonOwnerMachine))->toBeFalse()
        ->and($policy->create($this->user))->toBeTrue();
});
