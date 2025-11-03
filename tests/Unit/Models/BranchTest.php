<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Deployment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('belongs to a deployment', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);

    expect($branch->deployment)->toBeInstanceOf(Deployment::class);
    expect($branch->deployment->id)->toEqual($deployment->id);
});

it('has correct defaults', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);

    expect($branch->is_active)->toBeBool();
    expect($branch->is_tracking_remote)->toBeBool();
});

