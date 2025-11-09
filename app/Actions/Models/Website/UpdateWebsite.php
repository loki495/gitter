<?php

declare(strict_types=1);

namespace App\Actions\Models\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class UpdateWebsite
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(Website $website, array $data): Website
    {
        Gate::authorize('update', $website);

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:websites,name,'.$website->id],
            'description' => ['nullable', 'string'],
        ])->validate();

        /** @var array<string, mixed> $validated */
        $website->update($validated);

        return $website->refresh();
    }
}
