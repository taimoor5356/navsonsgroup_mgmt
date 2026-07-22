<?php

use App\Models\AmountTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AclController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\ExpenseNameController;
use App\Http\Controllers\PaymentModeController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\UserVehicleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DetailedReportController;
use App\Http\Controllers\PreviousPaymentController;
use App\Http\Controllers\AmountTransactionController;

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

Route::post('search-vehicle', [VehicleController::class, 'searchVehicleByNumber'])->name('search_vehicle_by_number');
Route::post('search-vehicle-by-name', [VehicleController::class, 'searchVehicleByName'])->name('search_vehicle_by_name');
Route::post('search-vehicle-by-brand-name', [VehicleController::class, 'searchVehicleByBrandName'])->name('search_vehicle_brand_by_name');

Route::post('search-customer-address-by-name', [UserVehicleController::class, 'searchCustomerAddressByName'])->name('search_customer_address_by_name');

Route::get('/brands', [VehicleController::class, 'brands'])->name('brands');
Route::get('/models', [VehicleController::class, 'models'])->name('models');

Route::group(['prefix' => '', 'middleware' => 'auth'], function () {
    //Admin Routes
    Route::group(['prefix' => 'admin'], function () {
        //Dashboard Routes
        Route::get('/dashboard/{date?}', [DashboardController::class, 'index'])->name('admin.dashboard');
        //Users Routes
        Route::group(['prefix' => '/users'], function () {
            Route::get('/list/{type?}', [UserController::class, 'index'])->name('admin.users.list');
            Route::get('/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/store', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::post('/update/{id}', [UserController::class, 'update'])->name('admin.users.update');
            Route::post('/delete', [UserController::class, 'destroy'])->name('admin.users.destroy');

            Route::post('/delete-multiple-users', [UserController::class, 'deleteMultipleUsers'])->name('delete_multiple_users');
            Route::get('/trashed', [UserController::class, 'trashed'])->name('admin.users.trashed');
            Route::get('/restore/{id}', [UserController::class, 'restore'])->name('admin.users.restore');
            Route::post('/export', [UserController::class, 'export'])->name('admin.users.export');
        });
        //Services Routes
        Route::group(['prefix' => '/services'], function () {
            Route::get('/list/{service_type?}', [ServiceController::class, 'index'])->name('admin.services.list');
            Route::get('/create', [ServiceController::class, 'create'])->name('admin.services.create');
            Route::post('/store', [ServiceController::class, 'store'])->name('admin.services.store')->middleware('role:admin|manager');
            Route::get('/edit/{id}', [ServiceController::class, 'edit'])->name('admin.services.edit');
            Route::post('/update/{id}', [ServiceController::class, 'update'])->name('admin.services.update')->middleware('role:admin|manager');
            Route::post('/delete', [ServiceController::class, 'destroy'])->name('admin.services.destroy')->middleware('role:admin');

            // Update payment status
            Route::post('/update-payment-status/{id}', [ServiceController::class, 'updatePaymentStatus'])->name('admin.users.update_payment_status')->middleware('role:admin|manager');

            Route::post('/delete-multiple-services', [ServiceController::class, 'deleteMultipleServices'])->name('delete_multiple_services')->middleware('role:admin');
            Route::get('/trashed', [ServiceController::class, 'trashed'])->name('admin.services.trashed');
            Route::get('/restore/{id}', [ServiceController::class, 'restore'])->name('admin.services.restore')->middleware('role:admin');
            Route::post('/export', [ServiceController::class, 'export'])->name('admin.services.export');


            Route::post('/update-payment-mode', [ServiceController::class, 'updatePaymentMode'])->name('admin.services.update_payment_mode');
            Route::post('/complaint', [ServiceController::class, 'complaint'])->name('admin.services.complaint');
            Route::post('/update-additional-services', [ServiceController::class, 'updateAdditionalServices'])->name('admin.services.update_additional_services');

            // customer phone duplicate check (used by the add/edit service form)
            Route::post('/check-phone', [ServiceController::class, 'checkPhoneDuplicate'])->name('admin.services.check_phone');

            // service types / rates
            Route::get('/types', [ServiceTypeController::class, 'index'])->name('admin.services.types.list');
            Route::post('/types/update-price', [ServiceTypeController::class, 'updatePrice'])->name('admin.services.types.update_price')->middleware('role:admin|manager');
        });

        //Accounts Routes
        Route::group(['prefix' => '/accounts'], function () {

            //Expenses Routes
            Route::group(['prefix' => '/expenses'], function () {
                Route::get('/list', [ExpenseController::class, 'index'])->name('admin.expenses.list');
                Route::get('/create', [ExpenseController::class, 'create'])->name('admin.expenses.create');
                Route::post('/store', [ExpenseController::class, 'store'])->name('admin.expenses.store')->middleware('role:admin|manager');
                Route::get('/edit/{id}', [ExpenseController::class, 'edit'])->name('admin.expenses.edit');
                Route::post('/update/{id}', [ExpenseController::class, 'update'])->name('admin.expenses.update')->middleware('role:admin');
                Route::post('/delete', [ExpenseController::class, 'destroy'])->name('admin.expenses.destroy')->middleware('role:admin');

                Route::post('/delete-multiple-expenses', [ExpenseController::class, 'deleteMultipleexpenses'])->name('delete_multiple_expenses')->middleware('role:admin');
                Route::get('/trashed', [ExpenseController::class, 'trashed'])->name('admin.expenses.trashed');
                Route::get('/restore/{id}', [ExpenseController::class, 'restore'])->name('admin.expenses.restore')->middleware('role:admin');
                Route::post('/export', [ExpenseController::class, 'export'])->name('admin.expenses.export');

                // Expense Names
                Route::get('/expense-names/list', [ExpenseNameController::class, 'index'])->name('admin.expense_name.list');
                Route::get('/expense-names/create', [ExpenseNameController::class, 'create'])->name('admin.expense_name.create');
                Route::post('/expense-names/store', [ExpenseNameController::class, 'store'])->name('admin.expense_name.store')->middleware('role:admin|manager');
                Route::get('/expense-names/edit/{id}', [ExpenseNameController::class, 'edit'])->name('admin.expense_name.edit');
                Route::post('/expense-names/update/{id}', [ExpenseNameController::class, 'update'])->name('admin.expense_name.update')->middleware('role:admin');
                Route::post('/expense-names/delete', [ExpenseNameController::class, 'destroy'])->name('admin.expense_name.destroy')->middleware('role:admin');

                Route::post('/fetch-expense-names', [ExpenseNameController::class, 'fetchExpenseNames'])->name('fetch_expense_names');
            });

            //Fines Routes
            Route::group(['prefix' => '/fines'], function () {
                Route::get('/list', [FineController::class, 'index'])->name('admin.fines.list');
                Route::get('/create', [FineController::class, 'create'])->name('admin.fines.create');
                Route::post('/store', [FineController::class, 'store'])->name('admin.fines.store')->middleware('role:admin|manager');
                Route::get('/edit/{id}', [FineController::class, 'edit'])->name('admin.fines.edit');
                Route::post('/update/{id}', [FineController::class, 'update'])->name('admin.fines.update')->middleware('role:admin');
                Route::post('/delete', [FineController::class, 'destroy'])->name('admin.fines.destroy')->middleware('role:admin');

                Route::post('/delete-multiple-fines', [FineController::class, 'deleteMultiplefines'])->name('delete_multiple_fines')->middleware('role:admin');
                Route::get('/trashed', [FineController::class, 'trashed'])->name('admin.fines.trashed');
                Route::get('/restore/{id}', [FineController::class, 'restore'])->name('admin.fines.restore')->middleware('role:admin');
                Route::post('/export', [FineController::class, 'export'])->name('admin.fines.export');
            });

            //Payment Types Routes
            Route::group(['prefix' => '/payment-modes'], function () {
                Route::get('/list', [PaymentModeController::class, 'index'])->name('admin.payment_modes.list');
                Route::get('/create', [PaymentModeController::class, 'create'])->name('admin.payment_modes.create');
                Route::post('/store', [PaymentModeController::class, 'store'])->name('admin.payment_modes.store')->middleware('role:admin|manager');
                Route::get('/edit/{id}', [PaymentModeController::class, 'edit'])->name('admin.payment_modes.edit');
                Route::post('/update/{id}', [PaymentModeController::class, 'update'])->name('admin.payment_modes.update')->middleware('role:admin');
                Route::post('/delete', [PaymentModeController::class, 'destroy'])->name('admin.payment_modes.destroy')->middleware('role:admin');

                Route::get('/trashed', [PaymentModeController::class, 'trashed'])->name('admin.payment_modes.trashed');
                Route::get('/restore/{id}', [PaymentModeController::class, 'restore'])->name('admin.payment_modes.restore')->middleware('role:admin');
                Route::post('/export', [PaymentModeController::class, 'export'])->name('admin.payment_modes.export');
            });
        });
        //ACL Routes
        Route::group(['prefix' => '/acl'], function () {
            Route::get('/roles/{status?}', [AclController::class, 'index'])->name('admin.acl.roles');
            Route::get('/role/create', [AclController::class, 'create'])->name('admin.acl.role.create');
            Route::post('/role/store', [AclController::class, 'store'])->name('admin.acl.role.store');
            Route::get('/role/{id}/edit', [AclController::class, 'edit'])->name('admin.acl.role.edit');
            Route::post('/role/update/{id}', [AclController::class, 'update'])->name('admin.acl.role.update');
        });
        //Payment Routes
        Route::group(['prefix' => '/payments'], function () {
            Route::post('/cash-in-hand', [AmountTransactionController::class, 'addCashInHandAmount'])->name('admin.payments.add_cash_in_hand');
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