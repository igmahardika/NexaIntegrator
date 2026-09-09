<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'gateway_mode')) {
                $table->string('gateway_mode', 30)->default('zero_tunnel')->after('business_type')
                      ->comment('zero_tunnel, direct_api, radius');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'gateway_mode')) {
                $table->dropColumn('gateway_mode');
            }
        });
    }
};
