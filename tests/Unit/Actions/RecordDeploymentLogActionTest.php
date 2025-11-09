<?php

declare(strict_types=1);

use App\Actions\RecordDeploymentLog;
use App\Models\Deployment;
use App\Models\DeploymentLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('records a deployment log successfully', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $action = new RecordDeploymentLog;

    $timestamp = CarbonImmutable::now();

    $log = $action->execute(
        $deployment,
        'Deployment finished successfully',
        'command',
        'output',
        0,
        100,
        $timestamp
    );

    expect($log)->toBeInstanceOf(DeploymentLog::class)
        ->and($log->deployment_id)->toBe($deployment->id)
        ->and($log->action)->toBe('Deployment finished successfully')
        ->and($log->command)->toBe('command')
        ->and($log->exit_code)->toBe(0)
        ->and($log->duration_ms)->toBe(100)
        ->and($log->executed_at->toDateTimeString())->toBe($timestamp->toDateTimeString());
});
