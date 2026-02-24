<?php

namespace App\Domain\Product\Models;

use App\Domain\Connector\Models\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'fingerprint_hash',
        'title_normalized',
        'brand_normalized',
        'category_id',
        'current_score',
        'score_confidence',
        'score_updated_at',
        'first_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'current_score' => 'decimal:2',
            'score_confidence' => 'decimal:2',
            'score_updated_at' => 'datetime',
            'first_seen_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ProductAsset::class)->orderBy('sort_order');
    }

    public function sourceSnapshots(): HasMany
    {
        return $this->hasMany(ProductSourceSnapshot::class);
    }

    public function metricsTimeSeries(): HasMany
    {
        return $this->hasMany(ProductMetricsTimeSeries::class);
    }

    public function score(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domain\Scoring\Models\Score::class);
    }
}
