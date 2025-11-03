<?php

declare(strict_types=1);

namespace App\Actions\SshKey;

use App\Models\SshKey;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class DeleteSshKey
{
    public function execute(SshKey $key): bool
    {
        Gate::authorize('delete', $key);
        Storage::delete('ssh/'.$key->user_id.'/'.$key->filename);

        return (bool) $key->delete();
    }
}
