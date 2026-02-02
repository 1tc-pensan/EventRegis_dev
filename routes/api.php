<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\AuthController;
use \App\Http\Controllers\Api\UserController;
use \App\Http\Controllers\Api\EventController;
use \App\Http\Controllers\Api\RegistrationController;
use Illuminate\Support\Facades\URL;
use App\Models\User;


//Without authentication
Route::get('/ping', function () {return response()->json(['message'=>'API működik']);});
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link.'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.'], 200);
    }

    if ($user->markEmailAsVerified()) {
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    return response()->json(['message' => 'Email verified successfully. You can now log in.'], 200);
})->middleware(['signed'])->name('verification.verify');

//Autheticated routes
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/me',[UserController::class,'me']);
    Route::put('/me',[UserController::class,'updateMe']);
    Route::post('/logout',[AuthController::class,'logout']);

    //Event CRUD
    Route::prefix('events')->group(function(){
        Route::get('/', [EventController::class,'index']);
        Route::get('/upcoming', [EventController::class,'upcoming']);
        Route::get('/past', [EventController::class,'past']);
        Route::get('/filter', [EventController::class,'filter']);
    
        //Event CRUD only Admin
        Route::post('/',[EventController::class,'store']);
        Route::put('/{id}',[EventController::class,'update']);
        Route::delete('/{id}',[EventController::class,'destroy']);

        //Registration
        Route::post('{event}/register', [RegistrationController::class,'register']); //user
        Route::delete('{event}/unregister', [RegistrationController::class,'unregister']); //user
        Route::delete('{event}/users/{user}', [RegistrationController::class,'adminRemoveUser']); //admin

    });
    
    Route::prefix('users')->group(function(){
        Route::get('/', [UserController::class],'index');
        Route::get('/{id}', [UserController::class],'show');
        Route::post('/', [UserController::class],'store');
        Route::put('/{id}', [UserController::class],'update');
        Route::delete('/{id}',[UserController::class,'destroy']);
    });

});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
