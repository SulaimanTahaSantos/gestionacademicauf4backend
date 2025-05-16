<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Clase;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

function RetornarMensaje($mensaje){
    return response()->json(['mensaje' => $mensaje]);
}
 // public routes 
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/registro', [UserController::class, 'register']);
Route::post('/inicioSesion', [UserController::class, 'inicioSesion']);
// Route::get('/login', function () {
//     return RetornarMensaje('Login successful');
// });
Route::get('/fetchUsersAndGroupsAndClasses', [UserController::class, 'fetchUsersAndGroupsAndClasses']);


// Protected Routes
Route::middleware([IsUserAuth::class])->group(function(){
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'getUser']);
});

// Admin Routes

Route::middleware([IsAdmin::class])->group(function(){
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
?>