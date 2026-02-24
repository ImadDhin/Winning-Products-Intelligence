<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->decimal('confidence', 3, 2);
            $table->json('components')->nullable();
            $table->timestamp('computed_at');
            $table->timestamps();
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->unique('product_id');
            $table->index(['product_id', 'computed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
