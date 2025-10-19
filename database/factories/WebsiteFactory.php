<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Website;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class WebsiteFactory extends Factory
{
    protected $model = Website::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company,
            'description' => $this->faker->sentence,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
