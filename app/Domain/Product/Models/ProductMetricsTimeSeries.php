<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMetricsTimeSeries extends Model
{
    protected $table = 'product_metrics_time_series';

    protected $fillable = ['product_id', 'ts_bucket', 'metric_key', 'value'];

    protected function casts(): array
    {
        return [
            'ts_bucket' => 'datetime',
            'value' => 'decimal:4',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
