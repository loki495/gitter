<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deployment;
use App\Models\Website;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DeploymentFactory extends Factory
{
    protected $model = Deployment::class;

    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'machine_id' => Machine::factory(),
            'path' => '/var/www/' . $this->faker->word,
            'url' => $this->faker->url,
            'is_primary' => false,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
