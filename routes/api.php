<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Row\RowController;
use App\Http\Controllers\Api\Upload\XlsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
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

Route::post('/login', [AuthController::class,'authenticate']);
Route::post('/register', [AuthController::class,'register']);
Route::get('/broadcast', function () {
    event(new \App\Events\RowEvent('$filename', '$progress'));

});

Route::group(['prefix' => 'redis'], function () {
    Route::get('/progress', function (Request $request){
        $redis = new Redis();
        $redis->connect('redis');
        return $redis->hGetAll($request->key);
    });
    Route::get('/keys', function (Request $request){
        $redis = new Redis();
        $redis->connect('redis');
        return $redis->keys('*');
    });
});

Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::get('/auth/user', function (Request $request) {
        return ['data' => $request->user()];
    });

    Route::group(['prefix' => 'upload'], function () {
        Route::post('/file_rows', [XlsController::class,'upload']);
    });

    Route::get('/batch/{batchId}', function (string $batchId) {
        return Bus::findBatch($batchId);
    });

    Route::group(['prefix' => 'rows'], function () {
        Route::get('/', [RowController::class,'show']);
        Route::delete('/clean', [RowController::class,'clean']);
    });

    Route::delete('/logout', [AuthController::class,'logout']);
});
