<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\BasePolicy;
use Illuminate\Database\Eloquent\Model;

it('correctly checks ownership', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Minimal concrete class to test protected method
    $policy = new class extends BasePolicy
    {
        public function testOwns(User $user, Model $model): bool
        {
            return $this->owns($user, $model);
        }
    };

    // Test model with user_id
    $model = new class extends Model
    {
        public $user_id;
    };

    $m = new $model;
    $m->user_id = 1;
    expect($policy->testOwns($user, $m))->toBeTrue();

    $m->user_id = 2;
    expect($policy->testOwns($user, $m))->toBeFalse();

    // Test null user_id
    $m->user_id = null;
    expect($policy->testOwns($user, $m))->toBeFalse();
});
