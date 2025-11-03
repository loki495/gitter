<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\DeploymentLog;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a deployment', function (): void {
    $deployment = Deployment::factory()->create();
    $log = DeploymentLog::factory()->create(['deployment_id' => $deployment->id]);

    expect($log->deployment)->toBeInstanceOf(Deployment::class);
    expect($log->deployment->id)->toEqual($deployment->id);
});

it('records realistic log data', function (): void {
    $log = DeploymentLog::factory()->create([
        'action' => 'pull',
        'command' => 'git pull origin main',
        'output' => 'Already up-to-date.',
        'exit_code' => 0,
    ]);

    expect($log->action)->toEqual('pull');
    expect($log->exit_code)->toBeInt();
    expect($log->executed_at)->toBeInstanceOf(CarbonInterface::class);
});
