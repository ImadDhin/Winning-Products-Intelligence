<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->json('options')->nullable();
            $table->decimal('price_min', 12, 2);
            $table->decimal('price_max', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('product_id');
            $table->index(['product_id', 'price_min']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
