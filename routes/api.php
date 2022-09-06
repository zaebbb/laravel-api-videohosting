<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\VideosController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/auth/reg", [AuthController::class, "index"]);
Route::post("/auth/login", [AuthController::class, "store"]);
Route::post("/auth/exit", [AuthController::class, "show"]);

Route::get("/videos", [VideosController::class, "index"]);
Route::post("/videos", [VideosController::class, "store"]);
Route::get("/videos/all", [VideosController::class, "show"]);
Route::post("/videos/{id}/options", [VideosController::class, "update"]);
Route::post("/videos/{id}/options/ban", [VideosController::class, "destroy"]);

Route::get("/videos/{id}/comments", [CommentsController::class, "index"]);
Route::post("/videos/{id}/comments", [CommentsController::class, "store"]);
