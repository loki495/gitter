<?php

declare(strict_types=1);

use App\Models\User;

it('returns initials from full name', function (): void {
    $user = User::factory()->make(['name' => 'Ada Lovelace']);
    expect($user->initials())->toBe('AL');
});

it('returns single initial when name has one word', function (): void {
    $user = User::factory()->make(['name' => 'Plato']);
    expect($user->initials())->toBe('P');
});

it('handles extra spaces gracefully', function (): void {
    $user = User::factory()->make(['name' => '  Alan   Turing  ']);
    expect($user->initials())->toBe('AT');
});
