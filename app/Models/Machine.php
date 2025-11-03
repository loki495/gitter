<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    /** @use HasFactory<\Database\Factories\MachineFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'ip',
        'ssh_user',
        'ssh_port',
        'ssh_key_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'ssh_port' => 'integer',
    ];

    protected $attributes = [
        'ssh_port' => 22,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Deployment, $this>
     */
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    /**
     * @return HasOne<SshKey, $this>
     */
    public function sshKey(): BelongsTo
    {
        return $this->belongsTo(SshKey::class);
    }
}
