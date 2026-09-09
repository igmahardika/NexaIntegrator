<?php

use App\Http\Controllers\Api\PortalAuthController;
use App\Http\Controllers\Api\RouterSyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WiFiPads Portal API Routes
|--------------------------------------------------------------------------
| These are consumed by the captive portal via AJAX.
| No CSRF required (stateless JSON API).
*/

Route::prefix('portal')
    ->name('api.portal.')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::post('/survey',   [PortalAuthController::class, 'submitSurvey'])->name('survey');
        Route::post('/voucher',  [PortalAuthController::class, 'submitVoucher'])->middleware('throttle:20,1')->name('voucher');
        Route::post('/member',   [PortalAuthController::class, 'submitMember'])->middleware('throttle:15,1')->name('member');
        Route::post('/whatsapp', [PortalAuthController::class, 'submitWhatsapp'])->name('whatsapp');
        Route::post('/quick',    [PortalAuthController::class, 'submitQuick'])->name('quick');
        Route::post('/email',    [PortalAuthController::class, 'submitEmail'])->name('email');
        Route::post('/pms',      [PortalAuthController::class, 'submitPms'])->name('pms');
    });

/*
|--------------------------------------------------------------------------
| MikroTik Router Synchronization Engine (No-Tunnel Architecture)
|--------------------------------------------------------------------------
| Consumed by MikroTik /tool fetch scheduler.
*/
Route::prefix('router')
    ->name('api.router.')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/{site}/sync-script', [RouterSyncController::class, 'syncScript'])->name('sync-script');
        Route::get('/{site}/sync-status', [RouterSyncController::class, 'syncStatus'])->name('sync-status');
    });
