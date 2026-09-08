<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('router_user_queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('location_id');
            $table->string('username', 100);
            $table->string('password', 100);
            $table->string('profile', 100)->default('default');
            $table->string('mac_address', 20)->nullable();
            $table->string('limit_uptime', 50)->nullable()->default('02:00:00');
            $table->string('comment', 255)->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->index(['location_id', 'is_synced']);
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_user_queues');
    }
};
