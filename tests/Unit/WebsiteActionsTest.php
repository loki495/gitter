<?php

declare(strict_types=1);

use App\Actions\Website\CreateWebsite;
use App\Actions\Website\DeleteWebsite;
use App\Actions\Website\UpdateWebsite;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('creates a website successfully', function (): void {
    $action = new CreateWebsite;
    $website = $action->execute([
        'name' => 'Test Site',
        'description' => 'My test site',
    ]);

    expect($website)
        ->toBeInstanceOf(Website::class)
        ->and($website->name)->toBe('Test Site');
});

it('fails to create a website with duplicate name', function (): void {
    $action = new CreateWebsite;
    $action->execute(['name' => 'DupSite']);
    expect(fn (): Website => $action->execute(['name' => 'DupSite']))
        ->toThrow(ValidationException::class);
});

it('updates a website successfully', function (): void {
    $website = Website::factory()->create();
    $action = new UpdateWebsite;

    $updated = $action->execute($website, [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);

    expect($updated->name)->toBe('Updated Name');
});

it('fails to update a website with duplicate name', function (): void {
    $w1 = Website::factory()->create(['name' => 'One']);
    $w2 = Website::factory()->create(['name' => 'Two']);
    $action = new UpdateWebsite;

    expect(fn (): Website => $action->execute($w2, ['name' => 'One']))
        ->toThrow(ValidationException::class);
});

it('deletes a website successfully', function (): void {
    $website = Website::factory()->create();
    $action = new DeleteWebsite;

    $result = $action->execute($website->id);
    expect($result)->toBeTrue()
        ->and(Website::find($website->id))->toBeNull();
});

it('throws when deleting a non-existent website', function (): void {
    $action = new DeleteWebsite;
    expect(fn (): bool => $action->execute(999))
        ->toThrow(ModelNotFoundException::class);
});
