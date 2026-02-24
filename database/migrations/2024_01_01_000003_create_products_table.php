<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint_hash', 64)->unique();
            $table->string('title_normalized');
            $table->string('brand_normalized')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('current_score', 5, 2)->default(0);
            $table->decimal('score_confidence', 3, 2)->default(0);
            $table->timestamp('score_updated_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('score_updated_at');
            $table->index(['category_id', 'current_score', 'score_updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
