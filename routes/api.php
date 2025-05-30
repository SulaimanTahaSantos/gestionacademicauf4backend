<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\RubricaController;
use App\Http\Controllers\EnunciadoController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsProfesor;
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
Route::post('/registro', [UserController::class, 'register']);
Route::post('/inicioSesion', [UserController::class, 'inicioSesion']);

// vista clases routes




// Protected Routes
Route::middleware(['isUserAuth'])->group(function(){
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'getUser']);
    Route::put('/updateUserSettings',[UserController::class, 'updateUserSettings']);
    Route::put('/updateUserSettingsPassword',[UserController::class, 'updateUserSettingsPassword']);
    Route::get('/entregas', [EntregaController::class, 'getEntregas']);
    Route::post('/entregas', [EntregaController::class, 'store']);
    Route::get('/enunciados', [EnunciadoController::class, 'index']);
    Route::get('/grupos', [GrupoController::class, 'getGrupos']);
    Route::get('/modulos', [ModuloController::class, 'getModulos']);
    Route::get('/getNotas', [NotaController::class, 'getNotas']);
    Route::get('/rubricas', [RubricaController::class, 'index']);
    Route::get('/enunciados', [EnunciadoController::class, 'index']);
    Route::get('/fetchUsersAndGroupsAndClasses', [UserController::class, 'fetchUsersAndGroupsAndClasses']);

});

// Admin Routes

Route::middleware(['isAdmin'])->group(function(){
    Route::get('/users/{id}', [UserController::class, 'show']);
    
    Route::post('/insertUsersAndGroupsAndClasses',[UserController::class, 'insertUsersAndGroupsAndClasses']);
Route::put('/updateUserAndGroupsAndClasses/{id}',[UserController::class, 'updateUserAndGroupsAndClasses']);
Route::delete('/deleteUserAndGroupsAndClasses/{id}',[UserController::class, 'deleteUserAndGroupsAndClasses']);



// Vista grupos routes
Route::post('/grupos', [GrupoController::class, 'insertGrupoCompleto']);
Route::put('/grupos/{id}', [GrupoController::class, 'updateGrupoCompleto']);
Route::delete('/grupos/{id}', [GrupoController::class, 'deleteGrupoCompleto']);

// Vista módulos routes
Route::post('/modulos', [ModuloController::class, 'store']);
Route::put('/modulos/{id}', [ModuloController::class, 'update']);
Route::delete('/modulos/{id}', [ModuloController::class, 'destroy']);
// Vista Notas routes
Route::post('/notas', [NotaController::class, 'store']);
Route::put('/notas/{id}', [NotaController::class, 'update']);
Route::delete('/notas/{id}', [NotaController::class, 'destroy']);

// Vista Entregas routes
Route::put('/entregas/{id}', [EntregaController::class, 'update']);
Route::delete('/entregas/{id}', [EntregaController::class, 'destroy']);

// Vista Rubricas routes
Route::post('/rubricas', [RubricaController::class, 'store']);
Route::put('/rubricas/{id}', [RubricaController::class, 'update']);
Route::delete('/rubricas/{id}', [RubricaController::class, 'destroy']);

// Vista Enunciados routes
Route::post('/enunciados', [EnunciadoController::class, 'store']);
Route::put('/enunciados/{id}', [EnunciadoController::class, 'update']);
Route::delete('/enunciados/{id}', [EnunciadoController::class, 'destroy']);
});

Route::middleware(['isProfesor'])->group(function(){
    
    Route::get('/profesor/grupos', [GrupoController::class, 'getGruposProfesor']);
    Route::post('/profesor/grupos', [GrupoController::class, 'storeProfesor']);
    Route::put('/profesor/grupos/{id}', [GrupoController::class, 'updateProfesor']);
    Route::delete('/profesor/grupos/{id}', [GrupoController::class, 'destroyProfesor']);
    
    Route::get('/profesor/modulos', [ModuloController::class, 'getModulosProfesor']);
    Route::post('/profesor/modulos', [ModuloController::class, 'storeProfesor']);
    Route::put('/profesor/modulos/{id}', [ModuloController::class, 'updateProfesor']);
    Route::delete('/profesor/modulos/{id}', [ModuloController::class, 'destroyProfesor']);
    
    Route::get('/profesor/entregas', [EntregaController::class, 'getEntregasProfesor']);
    Route::put('/profesor/entregas/{id}', [EntregaController::class, 'updateProfesor']);
    Route::delete('/profesor/entregas/{id}', [EntregaController::class, 'destroyProfesor']);
    
    Route::get('/profesor/notas', [NotaController::class, 'getNotasProfesor']);
    Route::post('/profesor/notas', [NotaController::class, 'storeProfesor']);
    Route::put('/profesor/notas/{id}', [NotaController::class, 'updateProfesor']);
    Route::delete('/profesor/notas/{id}', [NotaController::class, 'destroyProfesor']);
    
    Route::get('/profesor/rubricas', [RubricaController::class, 'indexProfesor']);
    Route::post('/profesor/rubricas', [RubricaController::class, 'storeProfesor']);
    Route::put('/profesor/rubricas/{id}', [RubricaController::class, 'updateProfesor']);
    Route::delete('/profesor/rubricas/{id}', [RubricaController::class, 'destroyProfesor']);
    
    Route::get('/profesor/enunciados', [EnunciadoController::class, 'indexPorProfesor']);
    Route::post('/profesor/enunciados', [EnunciadoController::class, 'storePorProfesor']);
    Route::put('/profesor/enunciados/{id}', [EnunciadoController::class, 'updatePorProfesor']);
    Route::delete('/profesor/enunciados/{id}', [EnunciadoController::class, 'destroyPorProfesor']);
    
    Route::get('/profesor/clases', [UserController::class, 'fetchClassesProfesor']);
    Route::post('/profesor/clases', [UserController::class, 'storeClaseProfesor']);
    Route::put('/profesor/clases/{id}', [UserController::class, 'updateClaseProfesor']);
    Route::delete('/profesor/clases/{id}', [UserController::class, 'destroyClaseProfesor']);
    Route::get('/profesor/usuariosGruposClases', [UserController::class, 'fetchUsersGroupsAndClassesProfesor']);
});
?>