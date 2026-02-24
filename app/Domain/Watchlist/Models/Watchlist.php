<?php

namespace App\Domain\Watchlist\Models;

use App\Models\User;
use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    protected $fillable = ['user_id', 'product_id', 'threshold_score'];

    protected function casts(): array
    {
        return ['threshold_score' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
