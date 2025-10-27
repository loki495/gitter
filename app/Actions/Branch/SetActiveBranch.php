<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Actions\Deployment\RecordDeploymentLog;
use App\Models\Branch;
use App\Services\CliRunner;

/**
 * SetActiveBranch - atomically set one branch active per deployment.
 */
final class SetActiveBranch
{
    /**
     * Mark the given branch active and clear others for the deployment.
     *
     * @param Branch $branch
     *
     * @return Branch
     */
    public function execute(Branch $branch): void
    {
        $deployment = $branch->deployment;

        // Deactivate other branches in same deployment
        $deployment->branches()
            ->where('id', '!=', $branch->id)
            ->update(['is_active' => false]);

        // Activate the selected branch
        $branch->update(['is_active' => true]);

        // Actually check out the branch on the machine
        $cli = app(CliRunner::class);
        $command = "cd {$deployment->path} && git checkout {$branch->name}";
        $result = $cli->run($deployment->machine, $command);

        // Record the result
        RecordDeploymentLog::execute($deployment, [
            'action' => 'checkout',
            'command' => $command,
            'output' => $result->output,
            'exit_code' => $result->exitCode,
            'executed_at' => now(),
            'duration_ms' => $result->durationMs,
        ]);
    }
}
