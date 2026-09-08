<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('full_name');
            $table->enum('role', ['staff', 'vip'])->default('staff');
            $table->string('rate_limit', 20)->default('20M/20M');
            $table->unsignedTinyInteger('shared_users')->default(2)
                  ->comment('Max concurrent devices allowed');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index('username');
            $table->index(['is_active', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_members');
    }
};
