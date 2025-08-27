<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RejectInOutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller(LoginController::class)->prefix('login')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', 'index')->name('login');
        Route::post('/authenticate', 'authenticate');
    });

    Route::post('/unauthenticate', 'unauthenticate')->middleware('auth');
});


Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('index');
    });

    Route::controller(RejectInOutController::class)->prefix('reject-in-out')->group(function () {
        Route::get('/get-master-plan', 'getMasterPlan')->name("get-master-plan");
        Route::get('/get-size', 'getSize')->name("get-size");
        Route::get('/get-defect-type', 'getDefectType')->name("get-defect-type");
        Route::get('/get-defect-area', 'getDefectArea')->name("get-defect-area");
        Route::get('/get-reject-in-out-daily', 'getRejectInOutDaily')->name("get-reject-in-out-daily");
        Route::get('/get-reject-in-out-detail', 'getRejectInOutDetail')->name("get-reject-in-out-detail");
        Route::get('/get-reject-in-out-detail-total', 'getRejectInOutDetailTotal')->name("get-reject-in-out-detail-total");

        Route::post('/export-reject-in-out', 'exportRejectInOut')->name("export-reject-in-out");
    });

    Route::controller(ProductionController::class)->prefix('production-panel')->group(function () {
        Route::get('/{id}', 'index');
        Route::post('/unauthenticate', 'unauthenticate')->middleware('auth');
    });

    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        // Route::get('/{id}', 'index');
        Route::put('/update/{id}', 'update')->middleware('auth');
    });
});
