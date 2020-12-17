<?php

use App\Http\Controllers\GoodsController;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'goods_categories'], function () {
    Route::post('create', [GoodsController::class, 'goodsCategoryCreate']);
    Route::post('update', [GoodsController::class, 'goodsCategoryUpdate']);
    Route::post('delete', [GoodsController::class, 'goodsCategoryDelete']);
    Route::get('list', [GoodsController::class, 'goodsCategoryList']);
});

Route::group(['prefix' => 'goods'], function () {
    Route::post('create', [GoodsController::class, 'goodsCreate']);
    Route::post('update', [GoodsController::class, 'goodsUpdate']);
    Route::post('delete', [GoodsController::class, 'goodsDelete']);
    Route::get('list/{cat}', [GoodsController::class, 'goodsList']);
});
