<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\User;
use App\Models\Website;

it('can create a website', function (): void {
    $website = Website::factory()->create();

    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->name)->not()->toBeEmpty();
});

it('requires unique name', function (): void {
    $name = 'My Unique Site';
    Website::factory()->create(['name' => $name]);

    expect(fn () => Website::factory()->create(['name' => $name]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('can access deployments relationship', function (): void {
    $website = Website::factory()->create();
    $deployment = Deployment::factory()->create(['website_id' => $website->id]);

    expect($website->deployments)->toHaveCount(1)
        ->and($website->deployments->first())->toBeInstanceOf(Deployment::class)
        ->and($website->deployments->first()->id)->toBe($deployment->id);
});

it('can access user relationship', function (): void {
    $user = User::factory()->create();

    $website = Website::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($website->user)->toBeInstanceOf(User::class)
        ->and($website->user->id)->toBe($user->id);
});
