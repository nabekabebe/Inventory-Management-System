<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransactionController as TransactionControllerAlias;
use App\Http\Controllers\TransferController as TransferControllerAlias;
use App\Http\Controllers\WarehouseController as WarehouseControllerAlias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//protected routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    //    DB::listen(function ($query) {
    //        logger($query->sql);
    //    });

    Route::get(
        '/inventories/low',
        'App\Http\Controllers\InventoryController@lowOnStock'
    );

    Route::resource('/inventories', InventoryController::class);
    Route::post(
        '/inventories/{id}/refund/{transactionId}',
        'App\Http\Controllers\InventoryController@refund'
    );
    Route::post(
        '/inventories/{id}/sell',
        'App\Http\Controllers\InventoryController@sell'
    );
    Route::resource('/categories', CategoryController::class);
    Route::get(
        '/warehouses/low',
        'App\Http\Controllers\WarehouseController@lowInWarehouse'
    );
    Route::resource('/warehouses', WarehouseControllerAlias::class);
    Route::get(
        '/transactions/report',
        'App\Http\Controllers\TransactionController@report'
    );
    Route::resource('/transactions', TransactionControllerAlias::class);

    Route::resource('/transfers', TransferControllerAlias::class);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::fallback('App\Http\Controllers\AuthController@noSuchRoute');
