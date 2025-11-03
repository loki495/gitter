<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Deployment;
use App\Models\User;
use App\Policies\BranchPolicy;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('allows owner to manage branch', function (): void {
    $deployment = new Deployment;
    $deployment->user_id = $this->user->id;

    $branch = new Branch;
    $branch->deployment = $deployment;

    $policy = new BranchPolicy;

    expect($policy->view($this->user, $branch))->toBeTrue()
        ->and($policy->create($this->user, $deployment))->toBeTrue()
        ->and($policy->update($this->user, $branch))->toBeTrue()
        ->and($policy->delete($this->user, $branch))->toBeTrue();
});

it('denies non-owner', function (): void {
    $deployment = new Deployment;
    $deployment->user_id = $this->otherUser->id;

    $branch = new Branch;
    $branch->deployment = $deployment;

    $policy = new BranchPolicy;

    expect($policy->view($this->user, $branch))->toBeFalse()
        ->and($policy->create($this->user, $deployment))->toBeFalse()
        ->and($policy->update($this->user, $branch))->toBeFalse()
        ->and($policy->delete($this->user, $branch))->toBeFalse();
});
