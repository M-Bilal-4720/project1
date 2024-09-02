<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\CategoryController;

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

Route::post('/user/register', [AuthController::class, 'store']);
Route::post('/user/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/category/save', [CategoryController::class, 'store']);
Route::get('/category', [CategoryController::class, 'show']);
Route::get('/category/edite/{id}', [CategoryController::class, 'edite']);
Route::delete('/category/delete/{id}', [CategoryController::class, 'destroy']);
Route::put('/category/update/{id}', [CategoryController::class, 'update']);
Route::put('/category/status/{id}', [CategoryController::class, 'status']);
Route::get('/user/logout', [AuthController::class, 'logout']);
Route::get('/user/profile', [AuthController::class, 'profile']);
Route::put('/user/update', [AuthController::class, 'update']);



});

Route::post('/admin/register', [AdminController::class, 'store']);
Route::post('/admin/login', [AdminController::class, 'login']);

Route::middleware('auth:admin')->group( function (){
Route::get('/admin/logout', [AdminController::class, 'logout']);
Route::get('/admin/profile', [AdminController::class, 'profile']);
Route::put('/admin/update', [AdminController::class, 'update']);
    
});


Route::post('/seller/register', [SellerController::class, 'store']);
Route::post('/seller/login', [SellerController::class, 'login']);

Route::middleware('auth:seller')->group( function(){
    Route::get('/seller/logout', [SellerController::class, 'logout']);
    Route::get('/seller/profile', [SellerController::class, 'profile']);
    Route::put('/seller/update', [SellerController::class, 'update']);
});

