<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['namespace' => 'Api', 'prefix' => '/'], function() {
    Route::post('register', [AuthController::class, 'postRegister']);
    Route::post('login', [AuthController::class, 'postLogin']);
});

Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::post('billings', [BillingController::class, 'index']);
    Route::post('dashboard', [BillingController::class, 'dashboard']);
    Route::post('billing-details', [BillingController::class, 'billingDetails']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('logout', [AuthController::class, 'logout']);
});