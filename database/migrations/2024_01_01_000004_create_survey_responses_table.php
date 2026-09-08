<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('campaign_id')->constrained('survey_campaigns')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('client_mac', 17)->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->jsonb('answers')->nullable()
                  ->comment('key: question_id, value: answer string or array');
            $table->timestamp('created_at')->nullable();

            $table->index('client_mac');
            $table->index('created_at');
            $table->index(['campaign_id', 'created_at']);
            $table->index(['location_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
