<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use App\Models\Grupo;
use App\Models\Clase;


class UserController extends Controller
{
    public function index(){
        $users = User::all();
        if($users){
            return response()->json($users);
        } else {
            return response()->json(['message' => 'No users found'], 404);
        }
    }
    public function show($id){
        $user = User::find($id);
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
    public function store(Request $request){
        $user = new User();
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->dni = $request->input('dni');
        $user->rol = $request->input('rol');
        $user->save();
        return response()->json($user, 201);
    }
    public function update(Request $request, $id){
        $user = User::find($id);
        if ($user) {
            $user->name = $request->input('name');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->dni = $request->input('dni');
            $user->rol = $request->input('rol');
            $user->save();
            return response()->json($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
    public function destroy($id){
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'rol' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'dni' => $request->dni,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $user,
        ], 201);
    }

public function inicioSesion(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->input('email'))->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $credentials = $request->only('email', 'password');
    try{
        if(!$token = JWTAuth::attempt($credentials)){
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
        ],200);
    }catch (JWTException $e){
        return response()->json([
            'error' => 'could_not_create_token',
            'message'=> $e->getMessage(),
        
        ], 500);
    }


    // return response()->json([
    //     'message' => 'Inicio de sesión exitoso',
    //     'user' => $user
    // ]);
}

public function getUser(){
    $user = Auth::user();
    return response()->json([
        'message' => 'Usuario autenticado',
        'data' => $user
    ], 200);
}

public function logout(){
    try {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logout exitoso'], 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'No se pudo cerrar sesión'], 500);
    }
}

public function fetchUsersAndGroupsAndClasses()
{
    $users = User::with(['grupo', 'clase'])->get();
    
    // Para debug
    foreach ($users as $user) {
        \Log::info('User: ' . $user->name);
        \Log::info('Grupo: ' . ($user->grupo ? $user->grupo->nombre : 'null'));
        \Log::info('Clase: ' . ($user->clase ? $user->clase->nombre : 'null'));
    }

    return response()->json($users);
}

public function insertUsersAndGroupsAndClasses(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'surname' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'dni' => 'required|string|max:20|unique:users,dni',
        'rol' => 'required|in:user,profesor,admin',
        'grupo.nombre' => 'required|string|max:255',
        'clase.nombre' => 'required|string|max:255',
    ]);

    $user = new User();
    $user->name = $request->input('name');
    $user->surname = $request->input('surname');
    $user->email = $request->input('email');
    $user->password = Hash::make($request->input('password'));
    $user->dni = $request->input('dni');
    $user->rol = $request->input('rol');
    $user->save();

    $group = new Grupo();
    $group->nombre = $request->input('grupo.nombre');
    $group->user_id = $user->id;
    $group->save();

    $class = new Clase();
    $class->nombre = $request->input('clase.nombre');
    $class->user_id = $user->id;
    $class->save();

    // Recargar relaciones
    $user->load('grupo', 'clase');

    return response()->json($user, 201);
}

public function updateUserAndGroupsAndClasses(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'surname' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'dni' => 'required|string|max:20|unique:users,dni,' . $id,
        'rol' => 'required|in:user,profesor,admin',
        'grupo.nombre' => 'required|string|max:255',
        'clase.nombre' => 'required|string|max:255',
    ]);

    $user->name = $request->input('name');
    $user->surname = $request->input('surname');
    $user->email = $request->input('email');
    $user->password = Hash::make($request->input('password'));
    $user->dni = $request->input('dni');
    $user->rol = $request->input('rol');
    $user->save();

    // Actualizar grupo
    if ($user->grupo) {
        $user->grupo->nombre = $request->input('grupo.nombre');
        $user->grupo->save();
    } else {
        $group = new Grupo();
        $group->nombre = $request->input('grupo.nombre');
        $group->user_id = $user->id;
        $group->save();
    }

    if ($user->clase) {
        $user->clase->nombre = $request->input('clase.nombre');
        $user->clase->save();
    } else {
        $class = new Clase();
        $class->nombre = $request->input('clase.nombre');
        $class->user_id = $user->id;
        $class->save();
    }

    return response()->json($user, 200);
}

public function deleteUserAndGroupsAndClasses($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    if ($user->grupo) {
        $user->grupo->delete();
    }
    if ($user->clase) {
        $user->clase->delete();
    }

    $user->delete();

    return response()->json(['message' => 'User and related data deleted successfully'], 200);

}
}