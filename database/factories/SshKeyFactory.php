<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SshKeyFactory extends Factory
{
    protected $model = SshKey::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'filename' => $this->faker->uuid() . '.pem',
            'type' => $this->faker->randomElement(['private', 'public']),
            'fingerprint' => $this->faker->md5(),
            'user_id' => User::factory(),
        ];
    }
}

