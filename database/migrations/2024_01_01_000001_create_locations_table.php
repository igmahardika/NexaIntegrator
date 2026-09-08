<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('address')->nullable();
            $table->string('router_ip', 45)->nullable();
            $table->unsignedSmallInteger('router_port')->default(8728);
            $table->string('router_user', 100)->nullable();
            $table->text('router_password')->nullable(); // stored encrypted
            $table->string('dns_name', 255)->nullable()->comment('e.g. wifi.login');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
