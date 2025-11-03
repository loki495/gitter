<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

final class SshKey extends Model
{
    /** @use HasFactory<\Database\Factories\SshKeyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'filename',
        'type',
        'machine_id',
        'fingerprint',
        'user_id',
    ];

    // Default attribute values using constructor-style attribute setup
    protected $attributes = [
        'type' => 'private',
    ];

    /**
     * @return HasMany<Machine,$this>
     */
    public function machines(): HasMany
    {
        return $this->HasMany(Machine::class);
    }

    /**
     * @return BelongsTo<User,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Attribute<int,string>
     */
    public function fullPath(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::path('ssh/'.$this->user_id.'/'.$this->filename)
        );
    }

    /**
     * @return HasManyThrough<Deployment,$this>
     */
    public function deployments() : HasManyThrough {
        return $this->hasManyThrough(Deployment::class, Machine::class);
    }
}
