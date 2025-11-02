<?php

declare(strict_types=1);

use App\Actions\Machine\CreateMachine;
use App\Actions\Machine\DeleteMachine;
use App\Actions\Machine\UpdateMachine;
use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can create a machine successfully', function (): void {

    $sshKey = SshKey::factory()->create([
        'user_id' => $this->user->id
    ]);

    $action = new CreateMachine;

    $machine = $action->execute([
        'name' => 'Action Machine',
        'type' => 'work',
        'ssh_user' => 'ubuntu',
        'user_id' => $this->user->id,
        'ssh_key_id' => $sshKey->id,
        'ip' => '127.0.0.1',
    ]);

    expect($machine)->toBeInstanceOf(Machine::class)
        ->and($machine->name)->toBe('Action Machine')
        ->and($machine->ssh_port)->toBe(22)
        ->and($machine->user->id)->toBe($this->user->id);

    $this->assertDatabaseHas('machines', ['id' => $machine->id]);
});

it('fails to create a machine with missing required fields', function (): void {
    $action = new CreateMachine;

    $action->execute([
        'type' => 'work', // missing name and ssh_user
    ]);
})->throws(\Illuminate\Validation\ValidationException::class);

it('can update a machine successfully', function (): void {
    $sshKey = SshKey::factory()->create([
        'user_id' => $this->user->id
    ]);

    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
        'user_id' => $this->user->id,
        'ssh_key_id' => $sshKey->id,
    ]);

    $action = new UpdateMachine;
    $updated = $action->execute($machine, [
        'name' => 'Updated Name',
        'ssh_port' => 2222,
        'ssh_key_id' => $sshKey->id,
    ]);

    expect($updated->name)->toBe('Updated Name')
        ->and($updated->ssh_port)->toBe(2222);

    $this->assertDatabaseHas('machines', [
        'id' => $machine->id,
        'name' => 'Updated Name',
        'ssh_port' => 2222,
    ]);
});

it('fails to update a machine with invalid data', function (): void {

    $sshKey = SshKey::factory()->create([
        'user_id' => $this->user->id
    ]);

    $machine = Machine::factory()->create([
        'ssh_user' => 'ubuntu',
        'user_id' => $this->user->id,
        'ssh_key_id' => $sshKey->id,
    ]);

    $action = new UpdateMachine;

    $action->execute($machine, [
        'ssh_port' => -5,
    ]);
})->throws(\Illuminate\Validation\ValidationException::class);

it('can delete a machine successfully', function (): void {
    $machine = Machine::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $action = new DeleteMachine;
    $action->execute($machine);

    $this->assertDatabaseMissing('machines', ['id' => $machine->id]);
});
