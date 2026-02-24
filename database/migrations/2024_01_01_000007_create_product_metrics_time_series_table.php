<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_metrics_time_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->dateTime('ts_bucket');
            $table->string('metric_key', 80);
            $table->decimal('value', 18, 4);
            $table->timestamps();
        });
        Schema::table('product_metrics_time_series', function (Blueprint $table) {
            $table->index(['product_id', 'ts_bucket', 'metric_key']);
            $table->index(['ts_bucket', 'metric_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_metrics_time_series');
    }
};
