<?php

namespace App\Domain\Connector\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAudit extends Model
{
    protected $table = 'jobs_audit';

    public $timestamps = true;

    protected $fillable = [
        'source_id',
        'type',
        'started_at',
        'finished_at',
        'status',
        'items_processed',
        'error_message',
        'rate_limit_hits',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
