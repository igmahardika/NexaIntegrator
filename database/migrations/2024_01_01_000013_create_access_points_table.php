<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_points', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('ip_address', 45);
            $table->string('mac_address', 17)->nullable();
            $table->string('zone_location', 100)->nullable()->comment('E.g. Lobby, Room 101, Outdoor');
            $table->enum('status', ['online', 'offline', 'degraded'])->default('online');
            $table->unsignedInteger('last_latency_ms')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('downtime_started_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_points');
    }
};
