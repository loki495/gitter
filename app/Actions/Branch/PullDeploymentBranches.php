<?php

declare(strict_types=1);

namespace App\Actions\Branch;

use App\Models\Branch;
use App\Models\Deployment;
use App\Models\DeploymentLog;
use App\Services\GitService;

final readonly class PullDeploymentBranches
{
    public function __construct(
        private GitService $git
    ) {}

    /**
     * @return array<Branch>
     */
    public function execute(Deployment $deployment): array
    {
        $start = microtime(true);

        $result = $this->git->branch
            ->addArgument('--no-color')
            ->execute($deployment);

        $deployment->branches()->delete();

        if (! $result->success()) {
            throw new \RuntimeException($result->output);
        }

        // Parse branches
        $branches = [];
        foreach ($result->output as $line) {
            $line = trim((string) $line);
            $isActive = str_starts_with($line, '*');
            $name = ltrim($line, '* ');
            $branches[] = [
                'name' => str_starts_with($name, 'remotes/') ? substr($name, 9) : $name,
                'is_active' => $isActive,
                'is_tracking_remote' => str_starts_with($name, 'remotes/'),
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
            'command' => in_array(implode(' ', $this->git->lastCommand ?? []), ['', '0'], true) ? [] : implode(' ', $this->git->lastCommand ?? []),
            'output' => $this->git->lastOutput,
            'exit_code' => $this->git->lastExitCode,
            'executed_at' => now(),
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
        ]);

        return $models;
    }
}
