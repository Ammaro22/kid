<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::post('signup',[UserController::class,'signup']);
Route::post('login', [UserController::class, 'login']);
Route::delete('user_delete/{id}',[UserController::class,'destroy']);
Route::group(["middleware"=>["auth:api"]],function (){
    Route::get('profile',[UserController::class,'profile']);
    Route::post('logout',[UserController::class,'logout']);
    Route::post('user_update',[UserController::class,'update']);

});
