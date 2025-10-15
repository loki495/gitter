<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'created_by',
        'updated_by',
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
     * @return BelongsTo<User, self>
     */
    public function creator() : BelongsTo
    {
        /** @var BelongsTo<User, self> */
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function updater() : BelongsTo
    {
        /** @var BelongsTo<User, self> */
        return $this->belongsTo(User::class, 'updated_by');
    }
}
