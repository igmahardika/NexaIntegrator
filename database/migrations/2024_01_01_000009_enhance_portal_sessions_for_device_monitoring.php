<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_sessions', function (Blueprint $table) {
            // Device Intelligence
            $table->string('device_type', 30)->default('mobile')->after('identifier')
                  ->comment('mobile, tablet, desktop, unknown');
            $table->string('device_brand', 50)->default('Unknown')->after('device_type')
                  ->comment('Apple, Samsung, Xiaomi, Oppo, Vivo, etc.');
            $table->string('device_model', 100)->nullable()->after('device_brand');
            $table->string('device_os', 50)->default('Unknown')->after('device_model')
                  ->comment('iOS, Android, Windows, macOS, Linux');
            $table->string('browser', 50)->nullable()->after('device_os');
            $table->text('user_agent')->nullable()->after('browser');

            // MAC Intelligence
            $table->string('mac_vendor', 100)->nullable()->after('client_mac');
            $table->boolean('is_randomized_mac')->default(false)->after('mac_vendor');

            // Session tracking
            $table->string('acct_session_id', 100)->nullable()->after('status');
            $table->timestamp('last_activity_at')->nullable()->after('logout_time');

            $table->index(['device_brand', 'device_type']);
            $table->index('device_os');
        });
    }

    public function down(): void
    {
        Schema::table('portal_sessions', function (Blueprint $table) {
            $table->dropIndex(['device_brand', 'device_type']);
            $table->dropIndex(['device_os']);
            $table->dropColumn([
                'device_type',
                'device_brand',
                'device_model',
                'device_os',
                'browser',
                'user_agent',
                'mac_vendor',
                'is_randomized_mac',
                'acct_session_id',
                'last_activity_at',
            ]);
        });
    }
};
