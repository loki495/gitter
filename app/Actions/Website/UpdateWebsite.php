<?php

declare(strict_types=1);

namespace App\Actions\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Auth;
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
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:websites,name,'.$website->id],
            'description' => ['nullable', 'string'],
        ])->validate();

        $validated['updated_by'] = Auth::id();

        /** @var array<string, mixed> $validated */
        $website->update($validated);

        return $website->refresh();
    }
}
