<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Website;
use App\Policies\WebsitePolicy;

beforeEach(function (): void {
    $this->user = new User;
    $this->user->id = 1;

    $this->otherUser = new User;
    $this->otherUser->id = 2;
});

it('allows any authenticated user to view/create, only owner can update/delete', function (): void {
    $website = new Website;
    $website->user_id = $this->user->id;

    $nonOwnerWebsite = new Website;
    $nonOwnerWebsite->user_id = $this->otherUser->id;

    $policy = new WebsitePolicy;

    expect($policy->view($this->user, $website))->toBeTrue()
        ->and($policy->create($this->user))->toBeTrue()
        ->and($policy->update($this->user, $website))->toBeTrue()
        ->and($policy->delete($this->user, $website))->toBeTrue()
        ->and($policy->update($this->user, $nonOwnerWebsite))->toBeFalse()
        ->and($policy->delete($this->user, $nonOwnerWebsite))->toBeFalse();
});
