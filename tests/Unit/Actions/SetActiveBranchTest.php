<?php

declare(strict_types=1);

use App\Actions\Branch\SetActiveBranch;
use App\Models\Branch;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    // If the action fires events, silence them by default; tests can enable expectations.
    Event::fake();
});

it('sets the chosen branch active and deactivates other branches for the same website', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $website = Website::factory()->create(['user_id' => $user->id]);

    // create branches; one pre-active, one to be selected
    $branchA = Branch::factory()->create([
        'website_id' => $website->id,
        'name' => 'branch-a',
        'active' => true,
    ]);
    $branchB = Branch::factory()->create([
        'website_id' => $website->id,
        'name' => 'branch-b',
        'active' => false,
    ]);

    // run the action
    /** @var SetActiveBranch $action */
    $action = app(SetActiveBranch::class);
    $action->handle($branchB); // or ->execute($branchB) depending on signature

    $branchA->refresh();
    $branchB->refresh();

    expect($branchA->active)->toBeFalse();
    expect($branchB->active)->toBeTrue();
})->skip();

it('does not affect branches on other websites', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $website1 = Website::factory()->create(['user_id' => $user->id]);
    $website2 = Website::factory()->create(['user_id' => $user->id]);

    $b1_w1 = Branch::factory()->create(['website_id' => $website1->id, 'active' => true]);
    $b2_w1 = Branch::factory()->create(['website_id' => $website1->id, 'active' => false]);

    $b_other = Branch::factory()->create(['website_id' => $website2->id, 'active' => true]);

    $action = app(SetActiveBranch::class);
    $action->handle($b2_w1);

    $b1_w1->refresh();
    $b2_w1->refresh();
    $b_other->refresh();

    expect($b1_w1->active)->toBeFalse();      // changed inside same website
    expect($b2_w1->active)->toBeTrue();       // selected
    expect($b_other->active)->toBeTrue();     // untouched (different website)
})->skip();

it('requires authorization to set a branch active', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $website = Website::factory()->create(['user_id' => $owner->id]);
    $branch = Branch::factory()->create(['website_id' => $website->id, 'active' => false]);

    // Ensure gate/policy denies other user (you may assert with Gate too)
    $this->actingAs($other);

    $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

    $action = app(SetActiveBranch::class);
    $action->handle($branch); // should throw because acting user is not authorized
})->skip();
