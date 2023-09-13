<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TeaPostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Carbon\Carbon;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
});

Route::get('/user/{id}', function (string $id) {
    $user = User::find($id);
    
    if($user){
        $userInfo = [
            "id"=> $user->id,
            "name"=> $user->name,
            "email"=> $user->email,
            "created_at"=> Carbon::parse($user->created_at)->diffForHumans(),
        ];
     
        return response()->json($userInfo, 200);
    }else{
        return ["message"=>"لا يوجد مستخدم!!"];
    }
    
});


Route::get('teaPosts',[TeaPostController::class,'index']);
Route::get('teaPost/{id}',[TeaPostController::class,'show']);
Route::post('teaPosts',[TeaPostController::class,'store']);
Route::put("teaPost/{id}",[TeaPostController::class,'update']);
Route::delete("teaPost/{id}",[TeaPostController::class,'delete']);

Route::post('addComment/{id}',[CommentController::class,'create']);