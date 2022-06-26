<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TokenController;
use App\Models\User;
use Illuminate\Http\Request;
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

Route::middleware(["auth:sanctum"])->group(function () {
    Route::middleware(["can:set,App\Models\User"])->group(function () {
        Route::apiResource("users", UserController::class);
    });
});

Route::post("/tokens/create", [TokenController::class, "create"]);

Route::get("/test", function (Request $request) {
    return response()->json(["message" => "testing"], 200);
});
