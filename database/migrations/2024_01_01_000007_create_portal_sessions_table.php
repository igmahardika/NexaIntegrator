<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('client_mac', 17)->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->string('method', 50)->default('survey');
            $table->string('identifier', 100)->nullable()
                  ->comment('voucher code, username, or campaign_id depending on method');
            $table->timestamp('login_time')->nullable();
            $table->timestamp('logout_time')->nullable();
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->enum('status', ['active', 'expired', 'disconnected'])->default('active');

            $table->index('client_mac');
            $table->index(['location_id', 'status']);
            $table->index(['status', 'login_time']);
            $table->index('login_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_sessions');
    }
};
