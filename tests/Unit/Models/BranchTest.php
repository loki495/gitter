<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Deployment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a deployment', function () {
    $deployment = Deployment::factory()->create();
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);

    expect($branch->deployment)->toBeInstanceOf(Deployment::class);
    expect($branch->deployment->id)->toEqual($deployment->id);
});

it('has correct defaults', function () {
    $branch = Branch::factory()->create();

    expect($branch->is_active)->toBeBool();
    expect($branch->is_tracking_remote)->toBeBool();
});

