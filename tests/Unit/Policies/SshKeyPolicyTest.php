<?php

declare(strict_types=1);

use App\Models\SshKey;
use App\Models\User;
use App\Policies\SshKeyPolicy;

beforeEach(function () {
    $this->user = new User();
    $this->user->id = 1;

    $this->otherUser = new User();
    $this->otherUser->id = 2;
});

it('allows user to manage own SSH keys', function () {
    $key = new SshKey();
    $key->user_id = $this->user->id;

    $nonOwnerKey = new SshKey();
    $nonOwnerKey->user_id = $this->otherUser->id;

    $policy = new SshKeyPolicy();

    expect($policy->viewAny($this->user))->toBeTrue()
        ->and($policy->view($this->user, $key))->toBeTrue()
        ->and($policy->create($this->user))->toBeTrue()
        ->and($policy->update($this->user, $key))->toBeTrue()
        ->and($policy->delete($this->user, $key))->toBeTrue()
        ->and($policy->restore($this->user, $key))->toBeTrue()
        ->and($policy->forceDelete($this->user, $key))->toBeTrue()
        ->and($policy->view($this->user, $nonOwnerKey))->toBeFalse()
        ->and($policy->update($this->user, $nonOwnerKey))->toBeFalse()
        ->and($policy->delete($this->user, $nonOwnerKey))->toBeFalse()
        ->and($policy->restore($this->user, $nonOwnerKey))->toBeFalse()
        ->and($policy->forceDelete($this->user, $nonOwnerKey))->toBeFalse();
});

