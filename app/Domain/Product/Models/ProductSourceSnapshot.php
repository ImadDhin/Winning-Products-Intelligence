<?php

namespace App\Domain\Product\Models;

use App\Domain\Connector\Models\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSourceSnapshot extends Model
{
    protected $table = 'product_source_snapshots';

    protected $fillable = [
        'product_id',
        'source_id',
        'external_id',
        'raw_payload_compressed',
        'normalized',
        'fetched_at',
        'metrics_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'normalized' => 'array',
            'fetched_at' => 'datetime',
            'metrics_snapshot' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
