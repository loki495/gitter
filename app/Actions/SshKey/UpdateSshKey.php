<?php

declare(strict_types=1);

namespace App\Actions\SshKey;

use App\Models\SshKey;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class UpdateSshKey
{
    /**
     * Create or update an SSH key.
     *
     * @param  array{name: string, type: string, file?: UploadedFile|null}  $data
     */
    public function execute(?SshKey $key, array $data): SshKey
    {
        Gate::authorize('update', $key);

        validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:private,public'],
            'file' => ['nullable', 'file', 'max:512'],
        ])->validate();

        $filename = $key?->filename ?? null;
        $fingerprint = $key?->fingerprint ?? null;

        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            $file = $data['file'];
            $filename = $file->getClientOriginalName();

            if (!preg_match('/(\\.(pem|key|pub)|[^\\.])$/', $filename)) {
                throw ValidationException::withMessages([
                    'file' => 'Invalid file type. Allowed extensions: .pem, .key, .pub, or no extensions',
                ]);
            }

            $storedPath = $file->storeAs('ssh/' . Auth::id(), $filename);
            $absolute = Storage::path($storedPath);

            $fingerprint = $this->computeFingerprint($absolute);
            File::chmod($absolute, 0600);
        }

        if (!$key) {
            $key = new SshKey();
            $key->user_id = Auth::id();
        }

        $key->fill([
            'name' => $data['name'],
            'type' => $data['type'],
            'filename' => $filename,
            'fingerprint' => $fingerprint,
        ])->save();

        return $key;
    }

    private function computeFingerprint(string $path): ?string
    {
        return trim(shell_exec("ssh-keygen -lf {$path} | awk '{print $2}'"));
    }
}

