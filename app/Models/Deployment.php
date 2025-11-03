<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Deployment extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'website_id',
        'machine_id',
        'path',
        'url',
        'is_primary',
        'user_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the website this deployment belongs to.
     *
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the machine this deployment runs on.
     *
     * @return BelongsTo<Machine, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the user who created the deployment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branches for this deployment (future feature).
     *
     * @return HasMany<Branch, $this>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     User* Get the deployment logs for this deployment (future feature).
     * @return HasMany<DeploymentLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(DeploymentLog::class);
    }

    // Example: treat 'machine_id' == null as local
    public function getIsLocalAttribute(): bool
    {
        return ! $this->machine->ip;
    }
}
