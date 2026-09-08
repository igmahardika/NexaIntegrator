<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('advertiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('sponsor_name')->nullable();
            $table->string('video_url')->nullable();
            $table->string('ad_banner_path')->nullable();
            $table->unsignedSmallInteger('min_watch_duration')->default(0)
                  ->comment('Minimum seconds user must watch video before unlocking form');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index('advertiser_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_campaigns');
    }
};
