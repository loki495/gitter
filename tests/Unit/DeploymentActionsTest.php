<?php
declare(strict_types=1);

use App\Actions\Deployment\{
    CreateDeployment,
    UpdateDeployment,
    DeleteDeployment
};
use App\Models\{Deployment, Machine, Website};
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function (): void {
    $this->actingAs(\App\Models\User::factory()->create());
});

it('creates a deployment successfully', function (): void {
    $website = Website::factory()->create();
    $machine = Machine::factory()->create();

    $action = new CreateDeployment();
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
    $website = Website::factory()->create();
    $action = new CreateDeployment();

    expect(fn() => $action->execute([
        'website_id' => $website->id,
        'machine_id' => 999,
        'path' => '/invalid',
    ]))->toThrow(ValidationException::class);
});

it('updates a deployment successfully', function (): void {
    $deployment = Deployment::factory()->create(['path' => '/var/www/old']);
    $action = new UpdateDeployment();

    $updated = $action->execute($deployment, [
        'path' => '/var/www/new',
        'url' => 'https://newsite.com',
        'is_primary' => false,
    ]);

    expect($updated->path)->toBe('/var/www/new');
});

it('deletes a deployment successfully', function (): void {
    $deployment = Deployment::factory()->create();
    $action = new DeleteDeployment();

    $result = $action->execute($deployment->id);
    expect($result)->toBeTrue()
        ->and(Deployment::find($deployment->id))->toBeNull();
});

it('throws when deleting non-existent deployment', function (): void {
    $action = new DeleteDeployment();
    expect(fn() => $action->execute(999))
        ->toThrow(ModelNotFoundException::class);
});

