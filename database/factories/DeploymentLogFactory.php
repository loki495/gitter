<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deployment;
use App\Models\DeploymentLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DeploymentLog> */
final class DeploymentLogFactory extends Factory
{
    protected $model = DeploymentLog::class;

    public function definition(): array
    {
        $action = $this->faker->randomElement(['pull', 'push', 'checkout', 'status', 'deploy']);
        $exit = $this->faker->randomElement([0, 0, 1, 2, null]);

        return [
            'deployment_id' => Deployment::factory(),
            'action' => $action,
            'command' => match ($action) {
                'pull' => 'git pull origin main',
                'push' => 'git push origin main',
                'checkout' => 'git checkout feature/x',
                default => $this->faker->sentence(),
            },
            'output' => $this->faker->paragraphs(3, true),
            'exit_code' => $exit,
            'executed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'duration_ms' => $this->faker->optional()->numberBetween(5, 5000),
        ];
    }
}
