<?php

namespace App\Domain\Connector\Models;

use App\Domain\Product\Models\ProductSourceSnapshot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $table = 'sources';

    protected $fillable = [
        'name',
        'slug',
        'connector_class',
        'config',
        'is_enabled',
        'last_run_at',
        'rate_limit_per_minute',
        'consecutive_failures',
        'compliance_notes',
        'schedule_cron',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_enabled' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ProductSourceSnapshot::class, 'source_id');
    }

    public function jobsAudit(): HasMany
    {
        return $this->hasMany(JobAudit::class, 'source_id');
    }

    public function getConnector(): \App\Domain\Connector\Contracts\ConnectorInterface
    {
        return app()->make($this->connector_class);
    }
}
