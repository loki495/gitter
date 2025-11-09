<?php

declare(strict_types=1);

use App\Actions\Models\SshKey\CreateSshKey;
use App\Actions\Models\SshKey\DeleteSshKey;
use App\Actions\Models\SshKey\UpdateSshKey;
use App\Models\SshKey;
use App\Models\User;
use App\Services\SshFingerprintService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $mock = Mockery::mock(SshFingerprintService::class);
    $mock->shouldReceive('compute')->andReturn('FAKE-FINGERPRINT');
    app()->instance(SshFingerprintService::class, $mock);

    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('creates a new SSH key successfully', function (): void {
    $action = app(CreateSshKey::class);
    $file = UploadedFile::fake()->create('id_rsa', 12);

    $key = $action->execute(
        name: 'Test Key',
        file: $file,
        type: 'private'
    );

    expect($key)
        ->toBeInstanceOf(SshKey::class)
        ->and($key->name)->toBe('Test Key')
        ->and(file_exists($key->fullPath))->toBeTrue();
});

it('fails creating a new SSH key with invalid file extension', function (): void {
    $action = app(CreateSshKey::class);
    $file = UploadedFile::fake()->create('id_rsa.ext', 12);

    $key = $action->execute(
        name: 'Test Key',
        file: $file,
        type: 'private'
    );

})->throws(RuntimeException::class, 'Invalid file type.');

it('fails creating ssh key with missing file', function (): void {
    $action = app(CreateSshKey::class);

    $action->execute(name: 'Broken', file: null, type: 'rsa');
})->throws(TypeError::class);

it('updates an existing ssh key successfully', function (): void {
    $create = app(CreateSshKey::class);
    $update = app(UpdateSshKey::class);

    $mock = Mockery::mock(SshFingerprintService::class);
    $mock->shouldReceive('compute')->andReturn('FAKE-FINGERPRINT');
    app()->instance(SshFingerprintService::class, $mock);

    $file = UploadedFile::fake()->create('id_ed25519', 10);
    $key = $create->execute('Old Key', $file, 'private');

    $newFile = UploadedFile::fake()->create('new', 10);
    $update->execute($key, [
        'name' => 'Updated Key',
        'file' => $newFile,
        'type' => 'private',
    ]);

    $key->refresh();

    expect($key->name)->toBe('Updated Key')
        ->and(file_exists($key->fullPath))->toBeTrue();
});

it('fails to update an existing ssh key with invalid file extension', function (): void {
    $create = app(CreateSshKey::class);
    $update = app(UpdateSshKey::class);

    $mock = Mockery::mock(SshFingerprintService::class);
    $mock->shouldReceive('compute')->andReturn('FAKE-FINGERPRINT');
    app()->instance(SshFingerprintService::class, $mock);

    $file = UploadedFile::fake()->create('id_ed25519', 10);
    $key = $create->execute('Old Key', $file, 'private');

    $newFile = UploadedFile::fake()->create('new.ext', 10);
    $key = $update->execute($key, [
        'name' => 'Updated Key',
        'file' => $newFile,
        'type' => 'private',
    ]);
})->throws(RuntimeException::class, 'Invalid file type.');

it('updates ssh key without replacing file', function (): void {
    $create = app(CreateSshKey::class);
    $update = app(UpdateSshKey::class);

    $file = UploadedFile::fake()->create('id_rsa', 10);
    $key = $create->execute('Key', $file, 'private');

    $oldPath = $key->file_path;

    $update->execute($key, [
        'name' => 'No Replace Key',
        'type' => 'private',
    ]);

    $key->refresh();

    expect($key->name)->toBe('No Replace Key')
        ->and($key->file_path)->toBe($oldPath);
});

it('fails updating ssh key with invalid file', function (): void {
    $create = app(CreateSshKey::class);
    $update = app(UpdateSshKey::class);

    $file = UploadedFile::fake()->create('id_rsa', 10);
    $key = $create->execute('Key', $file, 'private');

    $this->expectException(ValidationException::class);
    $update->execute($key, [
        'name' => 'Invalid File Key',
        'type' => 'public',
        'file' => 'not_a_file',
    ]);
});

it('deletes an ssh key successfully', function (): void {
    $create = app(CreateSshKey::class);
    $delete = app(DeleteSshKey::class);

    $file = UploadedFile::fake()->create('id_rsa', 10);
    $key = $create->execute('Delete Me', $file, 'private');

    expect(file_exists($key->fullPath))->toBeTrue();

    $delete->execute($key);

    expect(SshKey::find($key->id))->toBeNull()
        ->and(file_exists($key->fullPath))->toBeFalse();
});

it('prevents user from updating someone else’s ssh key', function (): void {
    $create = app(CreateSshKey::class);
    $update = app(UpdateSshKey::class);

    $otherUser = User::factory()->create();
    $file = UploadedFile::fake()->create('id_rsa', 10);
    $key = $create->execute('Foreign Key', $file, 'private');
    $key->user()->associate($otherUser)->save();

    $this->actingAs($this->user);
    $this->expectException(AuthorizationException::class);

    $update->execute($key, [
        'name' => 'Hack Attempt',
        'type' => 'private',
    ]);
});

it('throws when key file is not actually stored', function (): void {
    // Mock the Storage facade directly
    Storage::shouldReceive('disk')
        ->andReturnSelf();
    Storage::shouldReceive('storeAs')
        ->andReturn('ssh/ghostfile.pub');
    Storage::shouldReceive('putFileAs')
        ->andReturn('ssh/ghostfile.pub');
    Storage::shouldReceive('exists')
        ->andReturnFalse();

    $file = UploadedFile::fake()->create('badkey.pub', 10, 'text/plain');

    $action = app(CreateSshKey::class);

    $action->execute('Broken Key', $file, 'private');

})->throws(RuntimeException::class, 'Failed to store SSH key.');

it('throws when updated key file fails to store', function (): void {
    // Mock the Storage facade directly
    Storage::shouldReceive('disk')
        ->andReturnSelf();
    Storage::shouldReceive('storeAs')
        ->andReturn('ssh/ghostfile.pub');
    Storage::shouldReceive('putFileAs')
        ->andReturn('ssh/ghostfile.pub');
    Storage::shouldReceive('exists')
        ->andReturnFalse();

    $key = SshKey::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Broken Key',
        'type' => 'private',
    ]);

    $file = UploadedFile::fake()->create('badkey.pub', 10, 'text/plain');

    $action = app(UpdateSshKey::class);

    $action->execute($key, [
        'name' => 'Broken Key',
        'file' => $file,
        'type' => 'private',
    ]);

})->throws(RuntimeException::class, 'Failed to store SSH key.');
