<?php

use App\Http\Controllers\AclController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailedReportController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PreviousPaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Auth::routes();

Route::get('/', [AuthController::class, 'login']);
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'postLogin'])->name('post_login');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'postRegister'])->name('post_register');
Route::get('/logout', [AuthController::class, 'logout'])->name('postlogout');

Route::group(['prefix' => '', 'middleware' => 'auth'], function () {
        
    //Admin Routes
    Route::group(['prefix' => 'admin'], function () {

        //Dashboard Routes
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        //Users Routes
        Route::group(['prefix' => '/users'], function () {
            Route::get('/{customers}', [UserController::class, 'index'])->name('admin.users.list');
            Route::get('/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/store', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::post('/update/{id}', [UserController::class, 'update'])->name('admin.users.update');
            Route::post('/delete', [UserController::class, 'destroy'])->name('admin.users.destroy');
            Route::get('/sync', [UserController::class, 'sync'])->name('admin.users.sync');

            Route::post('/delete-multiple-users', [UserController::class, 'deleteMultipleUsers'])->name('delete_multiple_users');
            Route::get('/trashed', [UserController::class, 'trashed'])->name('admin.users.trashed');
            Route::get('/restore/{id}', [UserController::class, 'restore'])->name('admin.users.restore');
            Route::post('/export', [UserController::class, 'export'])->name('admin.users.export');
        });
        
        //ACL Routes
        Route::group(['prefix' => '/acl'], function () {
            Route::get('/roles/{status?}', [AclController::class, 'index'])->name('admin.acl.roles');
            Route::get('/role/create', [AclController::class, 'create'])->name('admin.acl.role.create');
            Route::post('/role/store', [AclController::class, 'store'])->name('admin.acl.role.store');
            Route::get('/role/{id}/edit', [AclController::class, 'edit'])->name('admin.acl.role.edit');
            Route::post('/role/update/{id}', [AclController::class, 'update'])->name('admin.acl.role.update');
        });
    });
    
    //User Routes
    Route::group(['prefix' => 'user'], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/list', [UserController::class, 'users'])->name('user.list');
        Route::get('/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/store', [UserController::class,'store'])->name('user.store');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::post('/update/{id}', [UserController::class, 'update'])->name('user.update');
    });
});