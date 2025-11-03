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
use Illuminate\Validation\ValidationException;

final class UpdateSshKey
{
    public function __construct(private SshFingerprintService $fingerprints) {}

    /**
     * Create or update an SSH key.
     *
     * @param  array{name: string, type: string, file?: UploadedFile|null}  $data
     */
    public function execute(SshKey $key, array $data): SshKey
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

            if (! in_array($file->getClientOriginalExtension(), ['pem', 'key', 'pub', ''], true)) {
                throw new \RuntimeException('Invalid file type.');
            }

            $storedPath = $file->storeAs('ssh/' . Auth::id(), $filename);
            $absolute = Storage::path($storedPath);
            $fingerprint = $this->fingerprints->compute($absolute);

            File::chmod($absolute, 0600);
        }

        $key->fill([
            'name' => $data['name'],
            'type' => $data['type'],
            'filename' => $filename,
            'fingerprint' => $fingerprint
        ])->save();

        return $key;
    }
}

