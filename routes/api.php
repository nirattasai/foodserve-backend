<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UserManageController;
use App\Http\Controllers\Api\MerchantController;
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

Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
 });

Route::post('create-user', [UserManageController::class, 'createUser']);

Route::post('create-merchant', [MerchantController::class, 'createMerchant']);
Route::post('open-merchant', [MerchantController::class, 'openMerchant']);
Route::post('close-merchant', [MerchantController::class, 'closeMerchant']);


Route::post('create-catagory', [MerchantController::class, 'createCatagory']);
Route::post('edit-catagory', [MerchantController::class, 'editCatagory']);
Route::post('delete-catagory', [MerchantController::class, 'deleteCatagory']);

Route::post('create-menu', [MerchantController::class, 'createMenu']);
Route::post('close-menu', [MerchantController::class, 'notReadyMenu']);
Route::post('edit-menu', [MerchantController::class, 'editMenu']);
Route::post('delete-menu', [MerchantController::class, 'deleteMenu']);