<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('site_id')->nullable()->after('password');
            $table->string('role', 50)->default('operator')->change();
            $table->boolean('is_active')->default(true)->after('site_id');

            $table->foreign('site_id')->references('id')->on('locations')->nullOnDelete();
            $table->index('site_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn(['site_id', 'is_active']);
        });
    }
};
