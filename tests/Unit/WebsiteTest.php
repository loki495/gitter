<?php
declare(strict_types=1);

use App\Models\Website;

it('can create a website', function () {
    $website = Website::factory()->create();
    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->name)->not()->toBeEmpty();
});

it('requires unique name', function () {
    $name = 'My Site';
    Website::factory()->create(['name' => $name]);

    expect(fn () => Website::factory()->create(['name' => $name]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

