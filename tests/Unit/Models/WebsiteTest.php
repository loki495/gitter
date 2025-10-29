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

it('can access creator and updatedBy relationships', function (): void {
    $user = User::factory()->create();

    $website = Website::factory()->create([
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    expect($website->createdBy)->toBeInstanceOf(User::class)
        ->and($website->createdBy->id)->toBe($user->id)
        ->and($website->updatedBy)->toBeInstanceOf(User::class)
        ->and($website->updatedBy->id)->toBe($user->id);
});
