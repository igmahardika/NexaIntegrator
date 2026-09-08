<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Customer / Site Profile
            $table->string('customer_name', 150)->nullable()->after('name');
            $table->string('contact_email', 100)->nullable()->after('customer_name');
            $table->string('contact_phone', 50)->nullable()->after('contact_email');
            $table->string('business_type', 50)->default('cafe')->after('contact_phone')
                  ->comment('cafe, hotel, retail, coworking, office, other');
            $table->text('notes')->nullable()->after('business_type');

            // Template System
            $table->string('active_template', 50)->default('modern-glass')->after('dns_name');
            $table->json('template_config')->nullable()->after('active_template')
                  ->comment('Branding, colors, logo, promo banner, enabled tabs, custom text');

            // RADIUS Configuration
            $table->boolean('radius_enabled')->default(false)->after('template_config');
            $table->string('radius_server_ip', 50)->nullable()->after('radius_enabled');
            $table->unsignedSmallInteger('radius_auth_port')->default(1812)->after('radius_server_ip');
            $table->unsignedSmallInteger('radius_acct_port')->default(1813)->after('radius_auth_port');
            $table->string('radius_secret', 100)->nullable()->after('radius_acct_port');
            $table->string('radius_nas_id', 50)->nullable()->after('radius_secret');
            $table->unsignedSmallInteger('radius_coa_port')->default(3799)->after('radius_nas_id');
            $table->string('default_rate_limit', 50)->default('5M/10M')->after('radius_coa_port');
            $table->unsignedInteger('default_session_timeout')->default(7200)->after('default_rate_limit')
                  ->comment('Timeout in seconds, default 2h');

            // Site Constraints
            $table->unsignedInteger('max_active_devices')->default(100)->after('default_session_timeout');
            $table->unsignedInteger('bandwidth_limit_mbps')->default(50)->after('max_active_devices');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'contact_email',
                'contact_phone',
                'business_type',
                'notes',
                'active_template',
                'template_config',
                'radius_enabled',
                'radius_server_ip',
                'radius_auth_port',
                'radius_acct_port',
                'radius_secret',
                'radius_nas_id',
                'radius_coa_port',
                'default_rate_limit',
                'default_session_timeout',
                'max_active_devices',
                'bandwidth_limit_mbps',
            ]);
        });
    }
};
