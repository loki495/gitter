<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Branch extends Model
{
    use HasFactory;

    /** Match DB defaults */
    protected $attributes = [
        'is_active' => false,
        'is_tracking_remote' => false,
    ];

    /** Mass assignable */
    protected $fillable = [
        'deployment_id',
        'name',
        'is_active',
        'is_tracking_remote',
        'last_commit',
        'last_checked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_tracking_remote' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Deployment,$this>
     */
    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}

