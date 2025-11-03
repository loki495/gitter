<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\BasePolicy;

it('correctly checks ownership', function () {
    $user = new User();
    $user->id = 1;

    $otherUser = new User();
    $otherUser->id = 2;

    // Minimal concrete class to test protected method
    $policy = new class extends BasePolicy {
        public function testOwns(User $user, mixed $model): bool
        {
            return $this->owns($user, $model);
        }
    };

    // Test model with user_id
    $model = new class {
        public $user_id;
    };

    $m = new $model();
    $m->user_id = 1;
    expect($policy->testOwns($user, $m))->toBeTrue();

    $m->user_id = 2;
    expect($policy->testOwns($user, $m))->toBeFalse();

    // Test null user_id
    $m->user_id = null;
    expect($policy->testOwns($user, $m))->toBeFalse();
});

