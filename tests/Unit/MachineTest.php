<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\User;

it('can create a machine with minimal required fields', function (): void {
    $machine = Machine::create([
        'name' => 'Test Machine',
        'type' => 'work',
        'ssh_user' => 'ubuntu',
    ]);

    expect($machine->id)->not()->toBeNull()
        ->and($machine->ssh_port)->toBe(22)
        ->and($machine->ssh_password_encrypted)->toBeNull();

    $this->assertDatabaseHas('machines', [
        'id' => $machine->id,
        'name' => 'Test Machine',
        'type' => 'work',
        'ssh_user' => 'ubuntu',
    ]);
});

it('encrypts and decrypts ssh password correctly', function (): void {
    $password = 'supersecret';
    $machine = Machine::create([
        'name' => 'Encrypted Machine',
        'type' => 'staging',
        'ssh_user' => 'admin',
        'ssh_password_encrypted' => $password,
    ]);

    $rawValue = Machine::find($machine->id)->getAttributes()['ssh_password_encrypted'];

    expect($rawValue)->not()->toBe($password)
        ->and($machine->ssh_password_encrypted)->toBe($password);
});

it('fails when required fields are missing', function (): void {
    Machine::create([
        'type' => 'work',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('sets creator and updater relationships correctly', function (): void {
    $user = User::factory()->create();

    $machine = Machine::create([
        'name' => 'Rel Machine',
        'type' => 'production',
        'ssh_user' => 'root',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    expect($machine->creator->id)->toBe($user->id)
        ->and($machine->updater->id)->toBe($user->id);
});
