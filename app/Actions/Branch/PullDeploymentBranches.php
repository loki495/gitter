<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Deployment;
use App\Models\Branch;
use App\Models\DeploymentLog;
use App\Services\GitService;

final class PullDeploymentBranches
{
    public function __construct(
        protected GitService $git
    ) {}

    /**
     * @return array<Branch>
     */
    public function execute(Deployment $deployment): array
    {
        $start = microtime(true);

        $output = $this->git->branch
            ->addArgument('--no-color')
            ->execute($deployment);

        $deployment->branches()->delete();

        // Parse branches
        $branches = [];
        foreach ($output as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $isActive = str_starts_with($line, '*');
            $name = ltrim($line, '* ');
            $isRemoteBranch = str_starts_with($name, 'remotes/');
            if ($isRemoteBranch) $name = substr($name, 8);
            $branches[] = [
                'name' => $name,
                'is_active' => $isActive,
                'is_tracking_remote' => $isRemoteBranch,
            ];
        }

        $models = [];

        // Update DB
        foreach ($branches as $b) {
            $models[] = Branch::updateOrCreate(
                ['deployment_id' => $deployment->id, 'name' => $b['name']],
                [
                    'is_active' => $b['is_active'],
                    'is_tracking_remote' => $b['is_tracking_remote'],
                    'last_checked_at' => now(),
                ]
            );
        }

        // Record log
        DeploymentLog::create([
            'deployment_id' => $deployment->id,
            'action' => 'refresh_branches',
            'command' => implode(' ', $this->git->lastCommand),
            'output' => $this->git->lastOutput,
            'exit_code' => $this->git->lastExitCode,
            'executed_at' => now(),
            'duration_ms' => (int)((microtime(true) - $start) * 1000),
        ]);

        return $models;
    }
}
