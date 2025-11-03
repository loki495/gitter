<?php

declare(strict_types=1);

namespace App\Actions\SshKey;

use App\Models\SshKey;
use App\Services\SshFingerprintService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class CreateSshKey
{
    public function __construct(private SshFingerprintService $fingerprints) {}

    public function execute(string $name, UploadedFile $file, string $type): SshKey
    {
        Gate::authorize('create', SshKey::class);

        validator([
            'name' => $name,
            'type' => $type,
            'file' => $file,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:private,public'],
            'file' => ['required', 'file', 'max:512'],
        ])->validate();

        if (! in_array($file->getClientOriginalExtension(), ['pem', 'key', 'pub', ''], true)) {
            throw new RuntimeException('Invalid file type.');
        }

        $filename = $file->getClientOriginalName();
        $storedPath = $file->storeAs('ssh/'.Auth::id(), $filename);

        $fullPath = Storage::path($storedPath);
        File::chmod(dirname($fullPath), 0700);
        File::chmod($fullPath, 0600);

        return SshKey::create([
            'name' => $name,
            'filename' => $filename,
            'type' => $type,
            'fingerprint' => $this->fingerprints->compute($fullPath),
            'user_id' => Auth::id(),
        ]);
    }
}
