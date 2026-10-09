<?php

use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetClassificationController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetIncidentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\IncidentConfigurationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    Route::resource('staff', StaffController::class)->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
    ]);

    Route::resource('assets', AssetController::class)->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
    ]);

    Route::patch(
        '/assets/{asset}/archive',
        [AssetController::class, 'archive']
    )->name('assets.archive');

    Route::get(
        '/assets/{asset}/assign',
        [AssetAssignmentController::class, 'create']
    )->name('assets.assign.create');

    Route::post(
        '/assets/{asset}/assign',
        [AssetAssignmentController::class, 'store']
    )->name('assets.assign.store');

    Route::get(
        '/assets/{asset}/return',
        [AssetAssignmentController::class, 'createReturn']
    )->name('assets.return.create');

    Route::post(
        '/assets/{asset}/return',
        [AssetAssignmentController::class, 'storeReturn']
    )->name('assets.return.store');

    Route::get(
        '/assets/{asset}/incidents/create',
        [AssetIncidentController::class, 'create']
    )->name('assets.incidents.create');

    Route::post(
        '/assets/{asset}/incidents',
        [AssetIncidentController::class, 'store']
    )->name('assets.incidents.store');

    Route::get(
        '/assets/{asset}/incidents/{incident}/edit',
        [AssetIncidentController::class, 'edit']
    )->name('assets.incidents.edit');

    Route::put(
        '/assets/{asset}/incidents/{incident}',
        [AssetIncidentController::class, 'update']
    )->name('assets.incidents.update');

    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
        ]);

        Route::get(
            '/asset-classification',
            [AssetClassificationController::class, 'index']
        )->name('asset-classification.index');

        Route::post(
            '/asset-classification/types',
            [AssetClassificationController::class, 'storeAssetType']
        )->name('asset-classification.types.store');

        Route::put(
            '/asset-classification/types/{assetType}',
            [AssetClassificationController::class, 'updateAssetType']
        )->name('asset-classification.types.update');

        Route::post(
            '/asset-classification/subtypes',
            [AssetClassificationController::class, 'storeAssetSubtype']
        )->name('asset-classification.subtypes.store');

        Route::put(
            '/asset-classification/subtypes/{assetSubtype}',
            [AssetClassificationController::class, 'updateAssetSubtype']
        )->name('asset-classification.subtypes.update');

        Route::post(
            '/asset-classification/statuses',
            [AssetClassificationController::class, 'storeAssetStatus']
        )->name('asset-classification.statuses.store');

        Route::put(
            '/asset-classification/statuses/{assetStatus}',
            [AssetClassificationController::class, 'updateAssetStatus']
        )->name('asset-classification.statuses.update');

        Route::post(
            '/asset-classification/conditions',
            [AssetClassificationController::class, 'storeAssetCondition']
        )->name('asset-classification.conditions.store');

        Route::put(
            '/asset-classification/conditions/{assetCondition}',
            [AssetClassificationController::class, 'updateAssetCondition']
        )->name('asset-classification.conditions.update');

        Route::get(
            '/incident-configuration',
            [IncidentConfigurationController::class, 'index']
        )->name('incident-configuration.index');

        Route::post(
            '/incident-configuration/types',
            [IncidentConfigurationController::class, 'storeIncidentType']
        )->name('incident-configuration.types.store');

        Route::put(
            '/incident-configuration/types/{incidentType}',
            [IncidentConfigurationController::class, 'updateIncidentType']
        )->name('incident-configuration.types.update');

        Route::post(
            '/incident-configuration/statuses',
            [IncidentConfigurationController::class, 'storeIncidentStatus']
        )->name('incident-configuration.statuses.store');

        Route::put(
            '/incident-configuration/statuses/{incidentStatus}',
            [IncidentConfigurationController::class, 'updateIncidentStatus']
        )->name('incident-configuration.statuses.update');
    });
});