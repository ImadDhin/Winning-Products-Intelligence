<?php

namespace App\Domain\Leaderboard\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardSegment extends Model
{
    protected $table = 'leaderboard_segments';

    public $timestamps = false;

    protected $fillable = ['segment_key', 'redis_key', 'updated_at'];

    protected function casts(): array
    {
        return ['updated_at' => 'datetime'];
    }
}
