<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_source_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->mediumBinary('raw_payload_compressed')->nullable();
            $table->json('normalized')->nullable();
            $table->timestamp('fetched_at');
            $table->json('metrics_snapshot')->nullable();
            $table->timestamps();
        });
        Schema::table('product_source_snapshots', function (Blueprint $table) {
            $table->index(['product_id', 'source_id', 'fetched_at']);
            $table->index(['source_id', 'fetched_at']);
            $table->index(['source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_source_snapshots');
    }
};
