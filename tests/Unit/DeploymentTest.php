<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\Machine;
use App\Models\Website;

it('can create a deployment', function (): void {
    $deployment = Deployment::factory()->create();
    expect($deployment)->toBeInstanceOf(Deployment::class)
        ->and($deployment->path)->not()->toBeEmpty();
});

it('requires unique website+machine', function (): void {
    $website = Website::factory()->create();
    $machine = Machine::factory()->create();
    Deployment::factory()->create(['website_id' => $website->id, 'machine_id' => $machine->id]);

    expect(fn () => Deployment::factory()->create(['website_id' => $website->id, 'machine_id' => $machine->id]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
