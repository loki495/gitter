<?php
declare(strict_types=1);

use App\Models\Deployment;

it('can create a deployment', function () {
    $deployment = Deployment::factory()->create();
    expect($deployment)->toBeInstanceOf(Deployment::class)
        ->and($deployment->path)->not()->toBeEmpty();
});

it('requires unique website+machine', function () {
    $website = \App\Models\Website::factory()->create();
    $machine = \App\Models\Machine::factory()->create();
    Deployment::factory()->create(['website_id' => $website->id, 'machine_id' => $machine->id]);

    expect(fn () => Deployment::factory()->create(['website_id' => $website->id, 'machine_id' => $machine->id]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

