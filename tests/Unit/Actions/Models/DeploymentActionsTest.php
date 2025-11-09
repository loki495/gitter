<?php

declare(strict_types=1);

use App\Actions\Models\Deployment\CreateDeployment;
use App\Actions\Models\Deployment\DeleteDeployment;
use App\Actions\Models\Deployment\UpdateDeployment;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\User;
use App\Models\Website;
use Illuminate\Validation\ValidationException;

it('creates a deployment successfully', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $website = Website::factory()->create();
    $machine = Machine::factory()->create();

    $action = new CreateDeployment;
    $deployment = $action->execute([
        'website_id' => $website->id,
        'machine_id' => $machine->id,
        'path' => '/var/www/test',
        'url' => 'https://example.com',
        'is_primary' => true,
    ]);

    expect($deployment)
        ->toBeInstanceOf(Deployment::class)
        ->and($deployment->path)->toBe('/var/www/test');
});

it('fails to create deployment with invalid machine id', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $website = Website::factory()->create();
    $action = new CreateDeployment;

    expect(fn (): Deployment => $action->execute([
        'website_id' => $website->id,
        'machine_id' => 999,
        'path' => '/invalid',
    ]))->toThrow(ValidationException::class);
});

it('updates a deployment successfully', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deployment = Deployment::factory()->create([
        'user_id' => $user->id,
        'path' => '/var/www/old',
    ]);

    $action = new UpdateDeployment;

    $updated = $action->execute($deployment, [
        'path' => '/var/www/new',
        'url' => 'https://newsite.com',
        'is_primary' => false,
    ]);

    expect($updated->path)->toBe('/var/www/new')
        ->and($updated->url)->toBe('https://newsite.com')
        ->and($updated->is_primary)->toBeFalse();
});

it('deletes a deployment successfully', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deployment = Deployment::factory()->create([
        'user_id' => $user->id,
    ]);

    $action = new DeleteDeployment;

    $result = $action->execute($deployment);
    expect($result)->toBeTrue()
        ->and(Deployment::find($deployment))->toBeEmpty();
});

it('throws when deleting someone else deployment', function (): void {
    $user = User::factory()->create();

    $user2 = User::factory()->create();

    $deployment = Deployment::factory()->create([
        'user_id' => $user2->id,
    ]);

    $this->actingAs($user);

    $action = new DeleteDeployment;
    $action->execute($deployment);
})->throws('This action is unauthorized.');
