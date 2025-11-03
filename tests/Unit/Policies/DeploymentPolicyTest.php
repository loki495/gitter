<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\User;
use App\Policies\DeploymentPolicy;

beforeEach(function (): void {
    $this->user = new User;
    $this->user->id = 1;

    $this->otherUser = new User;
    $this->otherUser->id = 2;
});

it('allows any authenticated user to view/create deployment', function (): void {
    $deployment = new Deployment;
    $deployment->user_id = $this->user->id;

    $policy = new DeploymentPolicy;

    expect($policy->view($this->user, $deployment))->toBeTrue()
        ->and($policy->create($this->user))->toBeTrue();
});

it('allows update/delete for owner and denies for non-owner', function (): void {
    $deployment = new Deployment;
    $deployment->user_id = $this->user->id;

    $nonOwnerDeployment = new Deployment;
    $nonOwnerDeployment->user_id = $this->otherUser->id;

    $policy = new \App\Policies\DeploymentPolicy;

    expect($policy->update($this->user, $deployment))->toBeTrue()
        ->and($policy->delete($this->user, $deployment))->toBeTrue()
        ->and($policy->update($this->user, $nonOwnerDeployment))->toBeFalse()
        ->and($policy->delete($this->user, $nonOwnerDeployment))->toBeFalse();
});
