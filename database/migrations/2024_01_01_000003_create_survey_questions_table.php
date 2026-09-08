<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('survey_campaigns')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('question_type', ['single_choice', 'multiple_choice', 'text', 'rating'])
                  ->default('single_choice');
            $table->jsonb('options')->nullable()
                  ->comment('Array of choice strings for single/multiple_choice; null for text/rating');
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['campaign_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
