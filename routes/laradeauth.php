<?php

use Illuminate\Support\Facades\Route;
use Laradeauth\Http\Controllers\AuthenticatedSessionController;
use Laradeauth\Http\Controllers\MicrosoftAuthenticatedSessionController;
use Laradeauth\Http\Controllers\TwoFactorChallengeController;
use Laradeauth\Http\Controllers\TwoFactorSetupController;

Route::middleware('web')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('login/microsoft', [MicrosoftAuthenticatedSessionController::class, 'redirect'])
            ->name('login.microsoft.redirect');

        Route::get('login/microsoft/callback', [MicrosoftAuthenticatedSessionController::class, 'callback'])
            ->name('login.microsoft.callback');

        Route::get('login/two-factor', [TwoFactorChallengeController::class, 'create'])
            ->name('two-factor.challenge');

        Route::post('login/two-factor', [TwoFactorChallengeController::class, 'store'])
            ->name('two-factor.challenge.store');

        Route::post('login/two-factor/cancel', [TwoFactorChallengeController::class, 'destroy'])
            ->name('two-factor.challenge.cancel');
    });

    Route::middleware('auth')->group(function () {
        Route::get('two-factor/setup', [TwoFactorSetupController::class, 'create'])
            ->name('two-factor.setup');

        Route::post('two-factor/setup', [TwoFactorSetupController::class, 'store'])
            ->name('two-factor.setup.store');

        Route::delete('two-factor', [TwoFactorSetupController::class, 'destroy'])
            ->name('two-factor.destroy');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
});
