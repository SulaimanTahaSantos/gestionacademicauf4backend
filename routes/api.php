<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\EntregaController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Clase;
use App\Models\Modulo;
use App\Models\Cursar;


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
Route::post('/insertUsersAndGroupsAndClasses',[UserController::class, 'insertUsersAndGroupsAndClasses']);
Route::put('/updateUserAndGroupsAndClasses/{id}',[UserController::class, 'updateUserAndGroupsAndClasses']);
Route::delete('/deleteUserAndGroupsAndClasses/{id}',[UserController::class, 'deleteUserAndGroupsAndClasses']);
// vista configuracion routes

Route::put('/updateUserSettings',[UserController::class, 'updateUserSettings']);
Route::put('/updateUserSettingsPassword',[UserController::class, 'updateUserSettingsPassword']);

// Vista grupos routes
Route::get('/grupos', [GrupoController::class, 'getGrupos']);
Route::post('/grupos', [GrupoController::class, 'insertGrupoCompleto']);
Route::put('/grupos/{id}', [GrupoController::class, 'updateGrupoCompleto']);
Route::delete('/grupos/{id}', [GrupoController::class, 'deleteGrupoCompleto']);

// Vista módulos routes
Route::get('/modulos', [ModuloController::class, 'getModulos']);
Route::post('/modulos', [ModuloController::class, 'store']);
Route::put('/modulos/{id}', [ModuloController::class, 'update']);
Route::delete('/modulos/{id}', [ModuloController::class, 'destroy']);
// Vista Notas routes
Route::get('/getNotas', [NotaController::class, 'getNotas']);
Route::post('/notas', [NotaController::class, 'store']);
Route::put('/notas/{id}', [NotaController::class, 'update']);
Route::delete('/notas/{id}', [NotaController::class, 'destroy']);

// Vista Entregas routes
Route::get('/entregas', [EntregaController::class, 'getEntregas']);
Route::post('/entregas', [EntregaController::class, 'store']);



// Protected Routes
Route::middleware([IsUserAuth::class])->group(function(){
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'getUser']);
});

// Admin Routes

Route::middleware([IsAdmin::class])->group(function(){
    Route::get('/users/{id}', [UserController::class, 'show']);
    // Route::post('/users', [UserController::class, 'store']);
    // Route::put('/users/{id}', [UserController::class, 'update']);
    // Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
?>