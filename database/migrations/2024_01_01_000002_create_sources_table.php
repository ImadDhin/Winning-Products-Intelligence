<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('connector_class');
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedSmallInteger('rate_limit_per_minute')->default(30);
            $table->unsignedTinyInteger('consecutive_failures')->default(0);
            $table->text('compliance_notes')->nullable();
            $table->string('schedule_cron')->default('*/5 * * * *');
            $table->timestamps();
        });
        Schema::table('sources', function (Blueprint $table) {
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
