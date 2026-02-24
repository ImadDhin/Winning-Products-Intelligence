<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backtest_results', function (Blueprint $table) {
            $table->id();
            $table->string('segment_key', 120);
            $table->date('from_date');
            $table->date('to_date');
            $table->json('predicted_winning_ids')->nullable();
            $table->json('actual_outcomes')->nullable(); // product_id => metric deltas
            $table->decimal('accuracy_metric', 8, 4)->nullable();
            $table->timestamp('computed_at');
            $table->timestamps();
        });
        Schema::table('backtest_results', function (Blueprint $table) {
            $table->index(['segment_key', 'from_date', 'to_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backtest_results');
    }
};
