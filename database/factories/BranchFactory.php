<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Deployment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Branch> */
final class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'deployment_id' => Deployment::factory(),
            'name' => $this->faker->randomElement(['main', 'master', 'develop', 'feature/x']),
            'is_active' => $this->faker->boolean(20),
            'is_tracking_remote' => $this->faker->boolean(50),
            'last_commit' => $this->faker->optional()->sha1(),
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function active(): self
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}

