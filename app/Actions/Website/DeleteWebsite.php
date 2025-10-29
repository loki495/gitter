<?php

declare(strict_types=1);

namespace App\Actions\Website;

use App\Models\Website;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

final class DeleteWebsite
{
    public function execute(Website $website): bool
    {
        Gate::authorize('delete', $website);
        return (bool) $website->delete();
    }
}
