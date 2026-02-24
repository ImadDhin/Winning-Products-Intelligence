<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_segments', function (Blueprint $table) {
            $table->id();
            $table->string('segment_key', 120)->unique();
            $table->string('redis_key', 180)->nullable();
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_segments');
    }
};
