<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Deployment;
use App\Models\DeploymentLog;
use Illuminate\Support\Carbon;

/**
 * RecordDeploymentLog action - persists a log entry for a deployment.
 */
final class RecordDeploymentLog
{
    /**
     * Persist a DeploymentLog record.
     */
    public function execute(
        Deployment $deployment,
        string $action,
        string $command,
        string $output,
        ?int $exitCode = null,
        ?int $durationMs = null,
        ?\DateTimeInterface $executedAt = null
    ): DeploymentLog {
        $executedAt = $executedAt instanceof \DateTimeInterface ? Carbon::instance($executedAt) : Carbon::now();

        /** @var DeploymentLog $log */
        $log = $deployment->logs()->create([
            'action' => $action,
            'command' => $command,
            'output' => $output,
            'exit_code' => $exitCode,
            'duration_ms' => $durationMs,
            'executed_at' => $executedAt,
        ]);

        return $log;
    }
}
