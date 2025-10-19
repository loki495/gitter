<?php
declare(strict_types=1);

namespace App\Actions\Website;

use App\Models\Website;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteWebsite
{
    public function execute(int $id): bool
    {
        $website = Website::find($id);

        if (! $website) {
            throw new ModelNotFoundException("Website not found.");
        }

        return (bool) $website->delete();
    }
}
