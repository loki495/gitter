<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\SshKey;
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
            'ip' => $this->faker->ipv4(),
            'ssh_user' => $this->faker->userName(),
            'ssh_port' => 22,
            'ssh_key_id' => SshKey::factory(),
            'notes' => $this->faker->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
