<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

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
        'ssh_key_path',
        'ssh_password_encrypted',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'ssh_port' => 'integer',
    ];

    protected $attributes = [
        'ssh_port' => 22,
    ];

    public function setSshPasswordEncryptedAttribute(?string $value): void
    {
        $this->attributes['ssh_password_encrypted'] = $value !== null && $value !== '' && $value !== '0' ? Crypt::encryptString($value) : null;
    }

    public function getSshPasswordEncryptedAttribute(?string $value): ?string
    {
        return $value !== null && $value !== '' && $value !== '0' ? Crypt::decryptString($value) : null;
    }

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
}
