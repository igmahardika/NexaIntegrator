<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklisted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->cascadeOnDelete();
            $table->string('mac_address', 17);
            $table->string('reason', 255)->nullable();
            $table->string('blocked_by', 100)->nullable();
            $table->timestamps();

            $table->index('mac_address');
            $table->index(['location_id', 'mac_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklisted_devices');
    }
};
