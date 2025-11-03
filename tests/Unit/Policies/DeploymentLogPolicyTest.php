<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\DeploymentLog;
use App\Models\User;
use App\Policies\DeploymentLogPolicy;

beforeEach(function (): void {
    $this->user = new User;
    $this->user->id = 1;

    $this->otherUser = new User;
    $this->otherUser->id = 2;
});

it('allows owner to manage deployment log', function (): void {
    $deployment = new Deployment;
    $deployment->user_id = $this->user->id;

    $log = new DeploymentLog;
    $log->deployment = $deployment;

    $policy = new DeploymentLogPolicy;

    expect($policy->view($this->user, $log))->toBeTrue()
        ->and($policy->create($this->user, $log))->toBeTrue()
        ->and($policy->update($this->user, $log))->toBeTrue()
        ->and($policy->delete($this->user, $log))->toBeTrue();
});

it('denies non-owner', function (): void {
    $deployment = new Deployment;
    $deployment->user_id = $this->otherUser->id;

    $log = new DeploymentLog;
    $log->deployment = $deployment;

    $policy = new DeploymentLogPolicy;

    expect($policy->view($this->user, $log))->toBeFalse()
        ->and($policy->create($this->user, $log))->toBeFalse()
        ->and($policy->update($this->user, $log))->toBeFalse()
        ->and($policy->delete($this->user, $log))->toBeFalse();
});
