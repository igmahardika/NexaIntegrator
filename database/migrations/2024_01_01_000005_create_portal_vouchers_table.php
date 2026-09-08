<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('batch_name');
            $table->string('code', 20)->unique();
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('rate_limit', 20)->default('10M/10M')
                  ->comment('RouterOS rate limit string e.g. 10M/10M');
            $table->boolean('is_used')->default(false);
            $table->string('used_by_mac', 17)->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index(['batch_name', 'is_used']);
            $table->index('expired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_vouchers');
    }
};
