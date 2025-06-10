<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\Admin\ServiceRequestController as AdminServiceRequestController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::get('/services', [ServiceController::class, 'index']);

Route::post('/service-requests', [ServiceRequestController::class, 'store']);


Route::prefix('admin')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);


    Route::middleware('auth:admin')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);


        Route::prefix('service-requests')->group(function () {

            Route::get('/', [AdminServiceRequestController::class, 'index']);
            Route::put('/{serviceRequest}/approve', [AdminServiceRequestController::class, 'approve']);

            Route::put('/{serviceRequest}/deny', [AdminServiceRequestController::class, 'deny']);
            Route::put('/{serviceRequest}/complete', [AdminServiceRequestController::class, 'complete']);
        });

        // Services management
        Route::prefix('services')->group(function () {
            Route::get('/', [AdminServiceController::class, 'index']);
            Route::post('/', [AdminServiceController::class, 'store']);
            Route::get('/{service}', [AdminServiceController::class, 'show']);
            Route::put('/{service}', [AdminServiceController::class, 'update']);
            Route::delete('/{service}', [AdminServiceController::class, 'destroy']);
            Route::put('/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus']);
        });

        // Admin management
        Route::prefix('admins')->group(function () {
            Route::get('/', [AdminController::class, 'index']);
            Route::post('/', [AdminController::class, 'store']);
            Route::get('/{admin}', [AdminController::class, 'show']);
            Route::put('/{admin}', [AdminController::class, 'update']);
            Route::put('/{admin}/block', [AdminController::class, 'block']);
            Route::put('/{admin}/unblock', [AdminController::class, 'unblock']);
            Route::delete('/{admin}', [AdminController::class, 'destroy']);
        });

        // Stats routes for dashboards
        Route::prefix('stats')->group(function () {
            Route::get('/service-requests', [AdminServiceRequestController::class, 'getStats']);
            Route::get('/services', [AdminServiceController::class, 'getStats']);
            Route::get('/admins', [AdminController::class, 'getStats']);
        });

        Route::get('/permissions', [AdminController::class, 'permissions']);
    });
});
