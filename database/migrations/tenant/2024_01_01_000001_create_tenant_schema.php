<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for tenant isolated database.
     */
    public function up(): void
    {
        // 1. Hotspot Profiles (QoS & Bandwidth Tiers)
        Schema::create('hotspot_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('rate_limit')->default('5M/10M'); // rx/tx
            $table->unsignedInteger('shared_users')->default(1);
            $table->unsignedInteger('uptime_limit')->default(7200); // 2 hours
            $table->unsignedBigInteger('quota_bytes')->default(0);  // 0 = unlimited
            $table->decimal('price', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Unified Hotspot Users (Vouchers, Members, Leads, One-Click)
        Schema::create('hotspot_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier')->unique(); // Voucher code, username, phone, email, MAC
            $table->string('secret')->nullable();   // Password or PIN
            $table->string('auth_method')->default('voucher'); // voucher, member, whatsapp, survey, quick_click, mac_bypass
            $table->uuid('profile_id')->nullable();
            $table->string('status')->default('ready'); // ready, active, depleted, expired, disabled
            $table->unsignedInteger('simultaneous_use')->default(1);
            $table->string('bound_mac', 17)->nullable();
            $table->unsignedInteger('uptime_limit')->nullable(); // in seconds
            $table->unsignedBigInteger('data_limit_bytes')->nullable();
            $table->unsignedInteger('used_uptime')->default(0);
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->timestamp('first_login_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('batch_name')->nullable();
            $table->json('guest_metadata')->nullable(); // name, room, phone, survey data
            $table->timestamps();

            $table->index('auth_method');
            $table->index('status');
            $table->index(['auth_method', 'status']);
            $table->index('batch_name');
        });

        // 3. Hotspot Sessions (Live Online Guests & Access History)
        Schema::create('hotspot_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hotspot_user_id')->nullable();
            $table->string('username');
            $table->string('mac_address', 17)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('auth_method')->default('voucher');
            $table->timestamp('session_start')->useCurrent();
            $table->timestamp('session_end')->nullable();
            $table->unsignedInteger('session_time')->default(0);
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->string('status')->default('active'); // active, closed
            $table->string('terminate_cause')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['status', 'mac_address']);
        });

        // 4. Captive Portal Template Configuration
        Schema::create('template_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('template_id')->default('modern-clean');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('welcome_message')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('background_path')->nullable();
            $table->string('primary_color', 10)->default('#22449E');
            $table->string('accent_color', 10)->default('#38BDF8');
            $table->json('enabled_methods')->nullable(); // ['voucher', 'member', 'whatsapp', 'quick_click']
            $table->text('custom_css')->nullable();
            $table->timestamps();
        });

        // 5. Layer-2 Policies (MAC Whitelist/Bypass & Blacklist)
        Schema::create('ip_bindings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mac_address', 17)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('type')->default('bypassed'); // bypassed, blocked
            $table->string('comment')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 6. Marketing Campaigns & Lead Capture Surveys
        Schema::create('survey_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->string('question');
            $table->string('type')->default('text'); // text, radio, checkbox, rating
            $table->json('options')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('survey_campaigns')->onDelete('cascade');
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->uuid('question_id');
            $table->string('mac_address', 17);
            $table->text('answer')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'mac_address']);
        });

        // 7. Access Point Hardware Watchdog
        Schema::create('access_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('ip_address', 45);
            $table->string('mac_address', 17)->nullable();
            $table->string('location_zone')->nullable();
            $table->string('status')->default('online'); // online, offline
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('connected_clients')->default(0);
            $table->timestamps();
        });

        // 8. Router User Queues (Ephemeral offline sync if needed)
        Schema::create('router_user_queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username');
            $table->string('password');
            $table->string('profile')->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->string('comment')->nullable();
            $table->string('limit_uptime')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('is_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('router_user_queues');
        Schema::dropIfExists('access_points');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('survey_campaigns');
        Schema::dropIfExists('ip_bindings');
        Schema::dropIfExists('template_configs');
        Schema::dropIfExists('hotspot_sessions');
        Schema::dropIfExists('hotspot_users');
        Schema::dropIfExists('hotspot_profiles');
    }
};
