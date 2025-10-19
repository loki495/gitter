<?php

declare(strict_types=1);

use App\Actions\Machine\CreateMachine;
use App\Actions\Machine\DeleteMachine;
use App\Actions\Machine\UpdateMachine;
use App\Models\Machine;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('can create a machine successfully', function (): void {
    $action = new CreateMachine;

    $machine = $action->execute([
        'name' => 'Action Machine',
        'type' => 'work',
        'ssh_user' => 'ubuntu',
        'created_by' => $this->user->id,
    ]);

    expect($machine)->toBeInstanceOf(Machine::class)
        ->and($machine->name)->toBe('Action Machine')
        ->and($machine->ssh_port)->toBe(22)
        ->and($machine->creator->id)->toBe($this->user->id);

    $this->assertDatabaseHas('machines', ['id' => $machine->id]);
});

it('fails to create a machine with missing required fields', function (): void {
    $action = new CreateMachine;

    $action->execute([
        'type' => 'work', // missing name and ssh_user
    ]);
})->throws(\Illuminate\Validation\ValidationException::class);

it('encrypts ssh password when provided', function (): void {
    $action = new CreateMachine;

    $machine = $action->execute([
        'name' => 'Encrypted Machine',
        'type' => 'work',
        'ssh_user' => 'ubuntu',
        'ssh_password_encrypted' => 'secret123', // triggers encryption
        'created_by' => $this->user->id,
    ]);

    expect($machine->ssh_password_encrypted)
        ->not->toBe('secret123')
        ->and(decrypt($machine->ssh_password_encrypted))->toBe('secret123');
});

it('can update a machine successfully', function (): void {
    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
        'created_by' => $this->user->id,
    ]);

    $action = new UpdateMachine;
    $updated = $action->execute($machine, [
        'name' => 'Updated Name',
        'ssh_port' => 2222,
    ]);

    expect($updated->name)->toBe('Updated Name')
        ->and($updated->ssh_port)->toBe(2222);

    $this->assertDatabaseHas('machines', [
        'id' => $machine->id,
        'name' => 'Updated Name',
        'ssh_port' => 2222,
    ]);
});

it('encrypts ssh password when updating with one', function (): void {
    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
        'created_by' => $this->user->id,
    ]);

    $action = new UpdateMachine;
    $updated = $action->execute($machine, [
        'ssh_password_encrypted' => 'newpass456',
    ]);

    expect($updated->ssh_password_encrypted)
        ->not->toBe('newpass456')
        ->and(decrypt($updated->ssh_password_encrypted))->toBe('newpass456');
});

it('fails to update a machine with invalid data', function (): void {
    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
    ]);

    $action = new UpdateMachine;

    $action->execute($machine, [
        'ssh_port' => -5,
    ]);
})->throws(\Illuminate\Validation\ValidationException::class);

it('can delete a machine successfully', function (): void {
    $machine = Machine::factory()->create();

    $action = new DeleteMachine;
    $action->execute($machine);

    $this->assertDatabaseMissing('machines', ['id' => $machine->id]);
});
