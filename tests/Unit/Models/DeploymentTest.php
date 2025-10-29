<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\Machine;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a deployment', function (): void {
    $deployment = Deployment::factory()->create();

    expect($deployment)->toBeInstanceOf(Deployment::class)
        ->and($deployment->path)->not()->toBeEmpty()
        ->and(is_int($deployment->website_id))->toBeTrue()
        ->and(is_int($deployment->machine_id))->toBeTrue();
});

it('requires unique website+machine combination', function (): void {
    $website = Website::factory()->create();
    $machine = Machine::factory()->create();

    Deployment::factory()->create([
        'website_id' => $website->id,
        'machine_id' => $machine->id,
    ]);

    $duplicate = fn () => Deployment::factory()->create([
        'website_id' => $website->id,
        'machine_id' => $machine->id,
    ]);

    expect($duplicate)->toThrow(\Illuminate\Database\QueryException::class);
});

it('can access website and machine relationships', function (): void {
    $deployment = Deployment::factory()->create();

    expect($deployment->website)->toBeInstanceOf(Website::class)
        ->and($deployment->machine)->toBeInstanceOf(Machine::class);
});

it('can access user relationship', function (): void {
    $user = User::factory()->create();
    $deployment = Deployment::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($deployment->user)->toBeInstanceOf(User::class)
        ->and($deployment->user->id)->toBe($user->id);
});

it('can correctly report is_primary attribute', function (): void {
    $deployment = Deployment::factory()->create(['is_primary' => true]);
    $deployment2 = Deployment::factory()->create(['is_primary' => false]);

    expect($deployment->is_primary)->toBeTrue()
        ->and($deployment2->is_primary)->toBeFalse();
});
