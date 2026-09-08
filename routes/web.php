<?php

use App\Http\Controllers\Admin\AccessPointController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DocumentationController;
use App\Http\Controllers\Admin\HotspotProfileController;
use App\Http\Controllers\Admin\IpBindingController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SiteRadiusController;
use App\Http\Controllers\Admin\SiteTemplateController;
use App\Http\Controllers\Admin\TenantContextController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PortalController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SuperadminMiddleware;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC CAPTIVE PORTAL
// ============================================================

Route::get('/portal', [PortalController::class, 'index'])->name('portal');
Route::get('/', fn() => redirect()->route('portal'));

// ============================================================
// AUTH (Protected by rate limiting)
// ============================================================

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================================
// ADMIN PANEL (requires authenticated admin/advertiser user)
// ============================================================

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth', AdminMiddleware::class])
    ->group(function () {

        // Default redirect
        Route::get('/', fn() => redirect()->route('admin.dashboard'));

        // Dashboard & Overview
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/active-users', [DashboardController::class, 'getActiveUsers'])->name('dashboard.active-users');

        // Tenant Context Switcher
        Route::post('/context/switch', [TenantContextController::class, 'switch'])->name('context.switch');

        // Campaigns & Survey Builder (Authorized per advertiser via Policy)
        Route::resource('campaigns', CampaignController::class)->except(['show']);

        // Analytics & Reports (Scoped to advertiser or global for superadmin)
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/export', [AnalyticsController::class, 'exportCsv'])->name('analytics.export');

        // ========================================================
        // SUPERADMIN ONLY INFRASTRUCTURE & NETWORK OPERATIONS
        // ========================================================
        Route::middleware(SuperadminMiddleware::class)->group(function () {

            // Active Device Disconnect from Dashboard
            Route::post('/dashboard/kick', [DashboardController::class, 'kickUser'])->name('dashboard.kick');

            // Sites & Customer Management
            Route::resource('sites', SiteController::class)->except(['show']);
            Route::post('/sites/{site}/toggle', [SiteController::class, 'toggle'])->name('sites.toggle');
            Route::post('/sites/{site}/test', [SiteController::class, 'testConnection'])->name('sites.test');

            // Site Template Management & Customizer
            Route::get('/templates', [SiteTemplateController::class, 'index'])->name('templates.index');
            Route::get('/sites/{site}/template', [SiteTemplateController::class, 'gallery'])->name('sites.template.gallery');
            Route::post('/sites/{site}/template/select/{templateId}', [SiteTemplateController::class, 'select'])->name('sites.template.select');
            Route::get('/sites/{site}/template/customizer', [SiteTemplateController::class, 'customizer'])->name('sites.template.customizer');
            Route::post('/sites/{site}/template/customizer', [SiteTemplateController::class, 'update'])->name('sites.template.update');

            // Site RADIUS & Router Integration
            Route::get('/sites/{site}/radius', [SiteRadiusController::class, 'show'])->name('sites.radius.show');
            Route::post('/sites/{site}/radius', [SiteRadiusController::class, 'update'])->name('sites.radius.update');
            Route::post('/sites/{site}/radius/test-coa', [SiteRadiusController::class, 'testCoa'])->name('sites.radius.test-coa');

            // Layer-2 Policy Engine (Bypass Whitelist & Block Blacklist)
            Route::get('/policy/bindings', [IpBindingController::class, 'index'])->name('policy.bindings');
            Route::post('/policy/bindings', [IpBindingController::class, 'store'])->name('policy.bindings.store');
            Route::delete('/policy/bindings/{ipBinding}', [IpBindingController::class, 'destroy'])->name('policy.bindings.destroy');
            Route::post('/policy/bindings/sync/{site}', [IpBindingController::class, 'sync'])->name('policy.bindings.sync');

            // Hotspot Profiles & QoS Engine
            Route::resource('profiles', HotspotProfileController::class)->except(['create', 'show', 'edit']);

            // Access Point Watchdog
            Route::resource('ap', AccessPointController::class)->except(['create', 'show', 'edit']);
            Route::post('/ap/{accessPoint}/ping', [AccessPointController::class, 'ping'])->name('ap.ping');
            Route::post('/ap/ping-all', [AccessPointController::class, 'pingAll'])->name('ap.ping-all');

            // Connected Devices Management
            Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
            Route::post('/devices/kick', [DeviceController::class, 'kick'])->name('devices.kick');
            Route::post('/devices/block', [DeviceController::class, 'block'])->name('devices.block');
            Route::delete('/devices/blacklist/{blacklistedDevice}', [DeviceController::class, 'unblock'])->name('devices.unblock');
            Route::get('/monitoring', [DeviceController::class, 'monitoring'])->name('devices.monitoring');

            // Vouchers
            Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
            Route::post('/vouchers/generate', [VoucherController::class, 'generate'])->name('vouchers.generate');
            Route::get('/vouchers/print/{batchName}', [VoucherController::class, 'printBatch'])->name('vouchers.print');
            Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
            Route::delete('/vouchers/batch/destroy', [VoucherController::class, 'destroyBatch'])->name('vouchers.destroyBatch');

            // Locations / Routers (legacy compatibility)
            Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
            Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
            Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
            Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
            Route::post('/locations/{location}/test', [LocationController::class, 'testConnection'])->name('locations.test');
            Route::get('/locations/{location}/health', [LocationController::class, 'health'])->name('locations.health');

            // Members / Staff
            Route::get('/members', [MemberController::class, 'index'])->name('members.index');
            Route::post('/members', [MemberController::class, 'store'])->name('members.store');
            Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
            Route::post('/members/{member}/toggle', [MemberController::class, 'toggle'])->name('members.toggle');

            // Integration Documentation & Developer Hub
            Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
            Route::get('/docs/print/{topic}', [DocumentationController::class, 'printDoc'])->name('docs.print');
        });
    });
