<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'options', 'price_min', 'price_max', 'currency'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
