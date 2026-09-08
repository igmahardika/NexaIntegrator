<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('name', 50)->comment('Profile name on RouterOS');
            $table->string('display_name', 100)->nullable();
            $table->string('rate_limit', 50)->default('5M/10M')->comment('Rx/Tx e.g. 5M/10M');
            $table->unsignedSmallInteger('shared_users')->default(1)->comment('Concurrency: 1-500 devices');
            $table->unsignedInteger('session_timeout')->default(7200)->comment('Seconds (0 = unlimited)');
            $table->unsignedInteger('idle_timeout')->default(600)->comment('Seconds');
            $table->unsignedInteger('keepalive_timeout')->default(120)->comment('Seconds');
            $table->string('status_autorefresh', 20)->default('1m');
            $table->boolean('transparent_proxy')->default(false);
            $table->boolean('synced_to_router')->default(false);
            $table->string('router_profile_id', 50)->nullable()->comment('RouterOS internal .id');
            $table->timestamps();

            $table->unique(['location_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_profiles');
    }
};
