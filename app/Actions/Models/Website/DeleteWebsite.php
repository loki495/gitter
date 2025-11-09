<?php

declare(strict_types=1);

namespace App\Actions\Models\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Gate;

final class DeleteWebsite
{
    public function execute(Website $website): bool
    {
        Gate::authorize('delete', $website);

        return (bool) $website->delete();
    }
}
