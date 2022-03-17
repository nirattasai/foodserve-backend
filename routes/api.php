<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UserManageController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\OrderController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'auth:sanctum'
    // 'prefix' => 'auth'
], function ($router) {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('refresh', [LoginController::class, 'refresh']);
    Route::get('me', [LoginController::class, 'me']);
});

Route::post('login', [LoginController::class, 'login'])->name('login');

Route::post('create-user', [UserManageController::class, 'createUser']);

Route::post('create-merchant', [MerchantController::class, 'createMerchant']);
Route::post('update-status-merchant', [MerchantController::class, 'updateStatusMerchant']);

Route::post('create-catagory', [MerchantController::class, 'createCatagory']);
Route::post('edit-catagory', [MerchantController::class, 'editCatagory']);
Route::post('delete-catagory', [MerchantController::class, 'deleteCatagory']);

Route::post('create-menu', [MerchantController::class, 'createMenu']);
Route::post('update-status-menu', [MerchantController::class, 'updateStatusMenu']);
Route::post('edit-menu', [MerchantController::class, 'editMenu']);
Route::post('delete-menu', [MerchantController::class, 'deleteMenu']);

Route::post('checkin-table', [OrderController::class, 'checkinTable']);
Route::post('create-order', [OrderController::class, 'createOrder']);
Route::post('update-status-order', [OrderController::class, 'updateStatusOrder']);

Route::post('create-bill', [OrderController::class, 'createBill']);
Route::post('update-bill', [OrderController::class, 'updateBill']);

//query merchant

Route::post('merchant-id', [MerchantController::class, 'getMerchantWithId']);
Route::post('merchant-user', [MerchantController::class, 'getMerchantWithUser']);
Route::post('merchant-catagories', [MerchantController::class, 'getCatagories']);
Route::post('catagory-menus', [MerchantController::class, 'getMenuWithCatagoryId']);
Route::post('merchant-menus', [MerchantController::class, 'getMenus']);

// query order

Route::post('get-orders', [OrderController::class, 'getOrders']);
Route::post('orders-with-status', [OrderController::class, 'getOrdersWithStatus']);
Route::post('order-menus', [OrderController::class, 'getMenuInOrder']);