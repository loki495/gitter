<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    protected $model = Machine::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word().' Machine',
            'type' => $this->faker->randomElement(['work', 'staging', 'production']),
            'ip' => $this->faker->optional()->ipv4(),
            'ssh_user' => $this->faker->userName(),
            'ssh_port' => 22,
            'ssh_key_path' => $this->faker->optional()->filePath(),
            'ssh_password_encrypted' => $this->faker->optional()->password(),
            'notes' => $this->faker->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
