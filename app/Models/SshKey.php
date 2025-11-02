<?php


declare(strict_types=1);


namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

final class SshKey extends Model
{
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
     * @return BelongsTo<Machine,$this>
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

    public function fullPath(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::path('ssh/' . $this->user_id . '/' . $this->filename)
        );
    }
}
