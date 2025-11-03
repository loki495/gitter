<?php

declare(strict_types=1);

namespace App\Actions\SshKey;

use App\Models\SshKey;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class CreateSshKey
{
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
        $storedPath = $file->storeAs('ssh/' . Auth::id(), $filename);

        $fullPath = Storage::path($storedPath);
        File::chmod(dirname($fullPath), 0700);

        $fingerprint = $this->computeFingerprint($fullPath);
        File::chmod($fullPath, 0600);

        $key = SshKey::create([
            'name' => $name,
            'filename' => $filename,
            'type' => $type,
            'fingerprint' => $fingerprint,
            'user_id' => Auth::id(),
        ]);

        return $key;
    }

    private function computeFingerprint(string $path): ?string
    {
        return trim(shell_exec("/usr/bin/ssh-keygen -lf {$path} | awk '{print $2}'"));
    }
}

