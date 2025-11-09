<?php

declare(strict_types=1);

use App\Actions\RecordDeploymentLog;
use App\Models\Deployment;
use App\Models\DeploymentLog;
use App\Services\CliRunner;

it('records a log after running a cli command via CliRunner', function (): void {
    $deployment = Deployment::factory()->create();

    $mock = Mockery::mock(CliRunner::class)
        ->makePartial()
        ->shouldReceive('run')
        ->with('git pull')
        ->once()
        ->andReturn([
            'stdout' => 'Pulled successfully',
            'stderr' => '',
            'exit_code' => 0,
            'duration_ms' => 1000,
        ])
        ->getMock();

    // Bind mock to container so the action resolves it via app()
    app()->instance(CliRunner::class, $mock);

    /** @var CliRunner $runner */
    $runner = app(CliRunner::class);
    $result = $runner->run('git pull');

    $action = new RecordDeploymentLog;
    $log = $action->execute(
        $deployment,
        'pull',
        'git pull',
        $result['stdout'],
        $result['exit_code'],
        $result['duration_ms']
    );

    expect($log)->toBeInstanceOf(DeploymentLog::class);
    expect($log->exit_code)->toEqual(0);
    expect($log->duration_ms)->toBeInt();
});
