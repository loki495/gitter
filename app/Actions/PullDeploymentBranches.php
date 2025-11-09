<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Branch;
use App\Models\Deployment;
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

        /** @var \App\Actions\Git\Branch $action */
        $action = $this->git->branch
            ->addArgument('--no-color')
            ->execute($deployment);

        $deployment->branches()->delete();

        if (! $action->success()) {
            throw new \RuntimeException("Error refreshing branches:\n".$action->outputRaw);
        }

        $models = [];

        /** @var array<int,array{name:string,active:bool}> $branches */
        $branches = $action->result();

        // Update DB
        foreach ($branches as $branch) {
            $models[] = Branch::updateOrCreate(
                ['deployment_id' => $deployment->id, 'name' => $branch['name']],
                [
                    'is_active' => $branch['active'],
                    'is_tracking_remote' => str_starts_with((string) $branch['name'], 'remotes/'),
                    'last_checked_at' => now(),
                ]
            );
        }

        // Record log
        app(RecordDeploymentLog::class)->execute(
            $deployment,
            'refresh_branches',
            $action->command,
            $action->outputRaw,
            $action->exitCode,
            (int) ((microtime(true) - $start) * 1000),
            now()
        );

        return $models;
    }
}
