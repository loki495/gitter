<?php
declare(strict_types=1);

namespace App\Actions\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class CreateWebsite
{
    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    public function execute(array $data): Website
    {
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:websites,name'],
            'description' => ['nullable', 'string'],
        ])->validate();

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        return Website::create($validated);
    }
}

