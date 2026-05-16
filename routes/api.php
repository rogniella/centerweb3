<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductosController;

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

Route::get('consulta_cristal', [ProductosController::class, 'consulta_cristal']);
Route::post('consulta_cristal', [ProductosController::class, 'consulta_cristal']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
