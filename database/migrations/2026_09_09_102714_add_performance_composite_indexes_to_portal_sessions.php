<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portal_sessions', function (Blueprint $table) {
            $table->index(['location_id', 'login_time'], 'portal_sessions_loc_login_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_sessions', function (Blueprint $table) {
            $table->dropIndex('portal_sessions_loc_login_idx');
        });
    }
};
