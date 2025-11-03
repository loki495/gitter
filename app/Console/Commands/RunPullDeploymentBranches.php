<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Branch\PullDeploymentBranches;
use App\Models\Deployment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

final class RunPullDeploymentBranches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deployment:pull-branches {deployment_id : The ID of the deployment to pull branches for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the PullDeploymentBranches action for a given deployment ID.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deploymentId = (int) $this->argument('deployment_id');

        /** @var Deployment|null $deployment */
        $deployment = Deployment::find($deploymentId);

        if (! $deployment) {
            $this->error("Deployment with ID {$deploymentId} not found.");

            return self::FAILURE;
        }

        /** @var PullDeploymentBranches $action */
        $action = App::make(PullDeploymentBranches::class);

        $this->info("Running PullDeploymentBranches for Deployment ID {$deploymentId}...");

        try {
            $action->execute($deployment);

        } catch (\Throwable $e) {
            $this->error("❌ Action failed: {$e->getMessage()}");
            $this->info($e->getTraceAsString());

            return self::FAILURE;
        }

        $this->info('✅ PullDeploymentBranches completed successfully.');

        return self::SUCCESS;
    }
}
