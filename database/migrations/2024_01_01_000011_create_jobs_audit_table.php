<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40); // scrape_list, scrape_item, score
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 20)->default('running'); // running, success, failed
            $table->unsignedInteger('items_processed')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('rate_limit_hits')->default(0);
            $table->timestamps();
        });
        Schema::table('jobs_audit', function (Blueprint $table) {
            $table->index(['source_id', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_audit');
    }
};
