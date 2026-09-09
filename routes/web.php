<?php

use App\Http\Controllers\Admin\AccessPointController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DocumentationController;
use App\Http\Controllers\Admin\EdgeGatewayController;
use App\Http\Controllers\Admin\HotspotProfileController;
use App\Http\Controllers\Admin\HotspotUserController;
use App\Http\Controllers\Admin\IpBindingController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SiteRadiusController;
use App\Http\Controllers\Admin\SiteTemplateController;
use App\Http\Controllers\Admin\TenantContextController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PortalController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SuperadminMiddleware;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC CAPTIVE PORTAL
// ============================================================

Route::get('/portal', [PortalController::class, 'index'])->name('portal');
Route::get('/', fn() => redirect()->route('portal'));

// ============================================================
// LEGACY COMPATIBILITY PERMANENT REDIRECTS (HTTP 301)
// ============================================================
Route::redirect('/locations', '/admin/sites', 301);
Route::redirect('/vouchers', '/admin/hotspot-users', 301);
Route::redirect('/members', '/admin/hotspot-users?tab=member', 301);

// ============================================================
// AUTH (Protected by rate limiting)
// ============================================================

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================================
// ADMIN PANEL (requires authenticated dashboard user)
// ============================================================

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth', AdminMiddleware::class])
    ->group(function () {

        // Default redirect
        Route::get('/', fn() => redirect()->route('admin.dashboard'));

        // Dashboard & Overview (All Authenticated Dashboard Users)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/active-users', [DashboardController::class, 'getActiveUsers'])->name('dashboard.active-users');

        // Tenant Context Switcher
        Route::post('/context/switch', [TenantContextController::class, 'switch'])->name('context.switch');

        // Legacy Internal Admin Redirects (301 Permanent)
        Route::redirect('locations', '/admin/sites', 301);
        Route::redirect('vouchers', '/admin/hotspot-users', 301);
        Route::redirect('members', '/admin/hotspot-users?tab=member', 301);

        // ========================================================
        // 1. CAMPAIGNS & MARKETING (Superadmin & Advertiser)
        // ========================================================
        Route::middleware('role:superadmin,advertiser')->group(function () {
            Route::resource('campaigns', CampaignController::class)->except(['show']);
        });

        // ========================================================
        // 2. CASHIER / VOUCHER DESK (Cashier, Operator, Site Admin, Superadmin)
        // ========================================================
        Route::middleware('role:superadmin,site_admin,operator,cashier')->group(function () {
            Route::get('/hotspot-users', [HotspotUserController::class, 'index'])->name('hotspot-users.index');
            Route::post('/hotspot-users/access-code', [HotspotUserController::class, 'storeAccessCode'])->name('hotspot-users.access-code');
            Route::post('/hotspot-users/generate', [HotspotUserController::class, 'generateVouchers'])->name('hotspot-users.generate');
            Route::get('/hotspot-users/print/{batchName}', [HotspotUserController::class, 'printBatch'])->name('hotspot-users.print');
        });

        // ========================================================
        // 3. OPERATOR DESK (Operator, Site Admin, Superadmin)
        // ========================================================
        Route::middleware('role:superadmin,site_admin,operator')->group(function () {
            // Connected Devices & Monitoring
            Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
            Route::post('/devices/kick', [DeviceController::class, 'kick'])->name('devices.kick');
            Route::post('/devices/block', [DeviceController::class, 'block'])->name('devices.block');
            Route::delete('/devices/blacklist/{blacklistedDevice}', [DeviceController::class, 'unblock'])->name('devices.unblock');
            Route::get('/monitoring', [DeviceController::class, 'monitoring'])->name('devices.monitoring');

            // Advanced Hotspot User Operations (Member, WA, MAC Bypass, Hotel Room, Toggle, Kick)
            Route::post('/hotspot-users/member', [HotspotUserController::class, 'storeMember'])->name('hotspot-users.member');
            Route::post('/hotspot-users/whatsapp', [HotspotUserController::class, 'storeWhatsapp'])->name('hotspot-users.whatsapp');
            Route::post('/hotspot-users/mac-bypass', [HotspotUserController::class, 'storeMacBypass'])->name('hotspot-users.mac-bypass');
            Route::post('/hotspot-users/hotel-room', [HotspotUserController::class, 'storeHotelRoom'])->name('hotspot-users.hotel-room');
            Route::post('/hotspot-users/{user}/toggle', [HotspotUserController::class, 'toggleStatus'])->name('hotspot-users.toggle');
            Route::delete('/hotspot-users/{user}', [HotspotUserController::class, 'destroy'])->name('hotspot-users.destroy');
            Route::delete('/hotspot-users/batch/destroy', [HotspotUserController::class, 'destroyBatch'])->name('hotspot-users.destroyBatch');
            Route::post('/hotspot-users/kick/{sessionId}', [HotspotUserController::class, 'kickSession'])->name('hotspot-users.kick');
        });

        // ========================================================
        // 4. SITE MANAGEMENT (Site Admin & Superadmin)
        // ========================================================
        Route::middleware('role:superadmin,site_admin')->group(function () {
            // Site Template Management & Customizer
            Route::get('/templates', [SiteTemplateController::class, 'index'])->name('templates.index');
            Route::get('/sites/{site}/template', [SiteTemplateController::class, 'gallery'])->name('sites.template.gallery');
            Route::post('/sites/{site}/template/select/{templateId}', [SiteTemplateController::class, 'select'])->name('sites.template.select');
            Route::get('/sites/{site}/template/customizer', [SiteTemplateController::class, 'customizer'])->name('sites.template.customizer');
            Route::post('/sites/{site}/template/customizer', [SiteTemplateController::class, 'update'])->name('sites.template.update');

            // Dedicated Edge Gateway & RADIUS Hub
            Route::get('/radius', [EdgeGatewayController::class, 'index'])->name('radius.index');
            Route::post('/radius/test-api/{site}', [EdgeGatewayController::class, 'testApi'])->name('radius.test-api');
            Route::post('/radius/test-coa/{site}', [EdgeGatewayController::class, 'testCoa'])->name('radius.test-coa');
            Route::get('/radius/download-login-html/{site}', [EdgeGatewayController::class, 'downloadLoginHtml'])->name('radius.download-login-html');
            Route::post('/radius/failsafe-bypass/{site}', [EdgeGatewayController::class, 'toggleEmergencyBypass'])->name('radius.failsafe-bypass');
            Route::get('/radius/traffic/{site}', [EdgeGatewayController::class, 'traffic'])->name('radius.traffic');
            Route::get('/radius/active-users/{site}', [EdgeGatewayController::class, 'activeUsers'])->name('radius.active-users');
            Route::post('/radius/kick-user/{site}', [EdgeGatewayController::class, 'kickUser'])->name('radius.kick-user');

            // Site RADIUS & Router Integration (Direct Route)
            Route::get('/sites/{site}/radius', [SiteRadiusController::class, 'show'])->name('sites.radius.show');
            Route::post('/sites/{site}/radius', [SiteRadiusController::class, 'update'])->name('sites.radius.update');
            Route::post('/sites/{site}/radius/test-coa', [SiteRadiusController::class, 'testCoa'])->name('sites.radius.test-coa');

            // Hotspot Profiles & QoS Engine
            Route::post('/profiles/sync-all', [HotspotProfileController::class, 'syncAll'])->name('profiles.sync-all');
            Route::post('/profiles/import', [HotspotProfileController::class, 'importFromRouter'])->name('profiles.import');
            Route::resource('profiles', HotspotProfileController::class)->except(['create', 'show', 'edit']);

            // Layer-2 Policy Engine (Bypass Whitelist & Block Blacklist)
            Route::get('/policy/bindings', [IpBindingController::class, 'index'])->name('policy.bindings');
            Route::post('/policy/bindings', [IpBindingController::class, 'store'])->name('policy.bindings.store');
            Route::delete('/policy/bindings/{ipBinding}', [IpBindingController::class, 'destroy'])->name('policy.bindings.destroy');
            Route::post('/policy/bindings/sync/{site}', [IpBindingController::class, 'sync'])->name('policy.bindings.sync');
        });

        // ========================================================
        // 5. GLOBAL / NOC MANAGEMENT (Superadmin Only)
        // ========================================================
        Route::middleware('role:superadmin')->group(function () {
            // Dashboard Emergency Kick
            Route::post('/dashboard/kick', [DashboardController::class, 'kickUser'])->name('dashboard.kick');

            // Sites & Customer Management
            Route::resource('sites', SiteController::class)->except(['show']);
            Route::get('/sites/{site}/provision', [SiteController::class, 'provision'])->name('sites.provision');
            Route::post('/sites/test-draft', [SiteController::class, 'testDraftConnection'])->name('sites.test-draft');
            Route::post('/sites/{site}/toggle', [SiteController::class, 'toggle'])->name('sites.toggle');
            Route::post('/sites/{site}/test', [SiteController::class, 'testConnection'])->name('sites.test');

            // Dashboard Operator & User Access Management
            Route::resource('users', UserController::class)->except(['show']);
            Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

            // Global Analytics & Reports
            Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
            Route::get('/analytics/export', [AnalyticsController::class, 'exportCsv'])->name('analytics.export');
            Route::get('/analytics/duration-log', [AnalyticsController::class, 'durationLogs'])->name('analytics.duration');
            Route::get('/analytics/duration-log/export', [AnalyticsController::class, 'exportDurationLogsCsv'])->name('analytics.duration.export');

            // Access Point Watchdog
            Route::resource('ap', AccessPointController::class)->except(['create', 'show', 'edit']);
            Route::post('/ap/{accessPoint}/ping', [AccessPointController::class, 'ping'])->name('ap.ping');
            Route::post('/ap/ping-all', [AccessPointController::class, 'pingAll'])->name('ap.ping-all');

            // Integration Documentation & Developer Hub
            Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
            Route::get('/docs/print/{topic}', [DocumentationController::class, 'printDoc'])->name('docs.print');
        });
    });
