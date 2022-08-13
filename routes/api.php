<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

// SIGNUP ADMIN
Route::post('/register/admin', [UserController::class, 'adminRegister']);

// SIGNUP USER
Route::post('/register', [UserController::class, 'userRegister']);

// LOGIN
Route::post('/authenticate', [UserController::class, 'authenticate']);

// Get all users
Route::get('/all-users', [UserController::class, 'getUsers']);

// Get all users
Route::get('/get-user/{name}', [UserController::class, 'getUser']);

// Reset password
Route::post('/reset-password', [UserController::class, 'resetPassword']);

// ActivateDeactivate id.
Route::post('admin/active-deactive-user', [UserController::class, 'activeDeactive']);

// Forget password
Route::post('/forget-password', [UserController::class, 'forgetPassword']);

// Set password
Route::post('forget-password/set', [UserController::class, 'setPassword']);


// TESTING API
Route::post('test_api', [UserController::class, 'testAPI']);