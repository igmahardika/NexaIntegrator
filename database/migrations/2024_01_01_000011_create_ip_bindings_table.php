<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('mac_address', 17);
            $table->string('address', 45)->nullable()->comment('Fixed IP address (optional)');
            $table->string('to_address', 45)->nullable()->comment('NAT translation IP (optional)');
            $table->string('server', 50)->default('all')->comment('Hotspot server instance name');
            $table->enum('type', ['bypassed', 'blocked', 'regular'])->default('bypassed')
                  ->comment('bypassed = whitelist non-browser, blocked = blacklist layer-2, regular = normal');
            $table->string('device_category', 50)->default('iot')
                  ->comment('smart_tv, cctv, printer, pos, iot, console, other');
            $table->string('comment', 255)->nullable()->comment('E.g. Smart TV Suite 101, CCTV Lobby');
            $table->string('router_binding_id', 50)->nullable()->comment('RouterOS internal .id');
            $table->boolean('synced_to_router')->default(false);
            $table->timestamps();

            $table->index('mac_address');
            $table->index(['location_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_bindings');
    }
};
