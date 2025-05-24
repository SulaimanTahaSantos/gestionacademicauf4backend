<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modulo;
use App\Models\Cursar;
use App\Models\Grupo;
use App\Models\User;

class ModuloController extends Controller
{
  
    public function getModulos()
    {
        try {
            $modulos = Modulo::select(
                'modulos.id as modulo_id',
                'modulos.nombre as modulo_nombre',
                'modulos.codigo as modulo_codigo',
                'modulos.descripcion as modulo_descripcion',
                'grupo.nombre as grupo_nombre',
                'users.name as profesor_name',
                'users.surname as profesor_surname'
            )
            ->join('grupo', 'modulos.grupo_id', '=', 'grupo.id')
            ->join('users', 'modulos.user_id', '=', 'users.id')
            ->where('users.rol', 'profesor')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $modulos,
                'message' => 'Módulos obtenidos correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los módulos: ' . $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:100|unique:modulos,codigo',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'grupo_id' => 'required|exists:grupo,id',
                'user_id' => 'required|exists:users,id'
            ]);

            // Verificar que el usuario tenga rol profesor
            $profesor = User::where('id', $validated['user_id'])
                           ->where('rol', 'profesor')
                           ->first();

            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no es un profesor'
                ], 422);
            }

            $modulo = Modulo::create($validated);

            // Obtener los datos completos del módulo creado
            $moduloConDatos = Modulo::select(
                'modulos.id as modulo_id',
                'modulos.codigo as modulo_codigo',
                'modulos.nombre as modulo_nombre',
                'modulos.descripcion as modulo_descripcion',
                'grupo.nombre as grupo_nombre',
                'users.name as profesor_name',
                'users.surname as profesor_surname'
            )
            ->join('grupo', 'modulos.grupo_id', '=', 'grupo.id')
            ->join('users', 'modulos.user_id', '=', 'users.id')
            ->where('modulos.id', $modulo->id)
            ->where('users.rol', 'profesor')
            ->first();

            return response()->json([
                'success' => true,
                'data' => $moduloConDatos,
                'message' => 'Módulo creado correctamente'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el módulo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $modulo = Modulo::find($id);
            if (!$modulo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo no encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'codigo' => 'required|string|max:100|unique:modulos,codigo,' . $id,
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'grupo_id' => 'required|exists:grupo,id',
                'user_id' => 'required|exists:users,id'
            ]);

            $profesor = User::where('id', $validated['user_id'])
                           ->where('rol', 'profesor')
                           ->first();

            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no es un profesor'
                ], 422);
            }

            $modulo->update($validated);

            $moduloConDatos = Modulo::select(
                'modulos.id as modulo_id',
                'modulos.codigo as modulo_codigo',
                'modulos.nombre as modulo_nombre',
                'modulos.descripcion as modulo_descripcion',
                'grupo.nombre as grupo_nombre',
                'users.name as profesor_name',
                'users.surname as profesor_surname'
            )
            ->join('grupo', 'modulos.grupo_id', '=', 'grupo.id')
            ->join('users', 'modulos.user_id', '=', 'users.id')
            ->where('modulos.id', $modulo->id)
            ->where('users.rol', 'profesor')
            ->first();

            return response()->json([
                'success' => true,
                'data' => $moduloConDatos,
                'message' => 'Módulo actualizado correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el módulo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $modulo = Modulo::find($id);
            
            if (!$modulo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo no encontrado'
                ], 404);
            }

            $moduloData = [
                'modulo_id' => $modulo->id,
                'modulo_codigo' => $modulo->codigo,
                'modulo_nombre' => $modulo->nombre,
                'modulo_descripcion' => $modulo->descripcion
            ];

            $modulo->delete();

            return response()->json([
                'success' => true,
                'data' => $moduloData,
                'message' => 'Módulo eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el módulo: ' . $e->getMessage()
            ], 500);
        }
    }
}
