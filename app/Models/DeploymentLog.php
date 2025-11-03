<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DeploymentLog model
 *
 * @property int $id
 * @property int $deployment_id
 * @property string $action
 * @property string $command
 * @property string $output
 * @property int|null $exit_code
 * @property \Illuminate\Support\Carbon $executed_at
 * @property int|null $duration_ms
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
final class DeploymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'deployment_id',
        'action',
        'command',
        'output',
        'exit_code',
        'executed_at',
        'duration_ms',
    ];

    protected $casts = [
        'exit_code' => 'integer',
        'executed_at' => 'datetime',
        'duration_ms' => 'integer',
    ];

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}
