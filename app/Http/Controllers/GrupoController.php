<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Cursar;
use App\Models\Modulo;
use App\Models\User;




class GrupoController extends Controller
{
    /**
     * Get grupos with modules and users
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrupos()
    {
        try {
            $grupos = Grupo::all();
            
            $resultado = $grupos->map(function($grupo) {
                $cursarsDelGrupo = Cursar::where('grupo_id', $grupo->id)->get();
                
                $cursarIds = $cursarsDelGrupo->pluck('id');
                $modulos = Modulo::whereIn('cursar_id', $cursarIds)->get();
                
                $modulosConUsuarios = $modulos->map(function($modulo) {
                    $cursar = Cursar::find($modulo->cursar_id);
                    $usuario = null;
                    
                    if ($cursar && $cursar->usuario && $cursar->usuario->rol === 'user') {
                        $usuario = [
                            'id' => $cursar->usuario->id,
                            'nombre' => $cursar->usuario->name,
                            'apellido' => $cursar->usuario->surname,
                            'email' => $cursar->usuario->email,
                            'dni' => $cursar->usuario->dni
                        ];
                    }
                    
                    return [
                        'id' => $modulo->id,
                        'nombre' => $modulo->nombre,
                        'codigo' => $modulo->codigo,
                        'descripcion' => $modulo->descripcion,
                        'usuario' => $usuario
                    ];
                });
                
                return [
                    'id' => $grupo->id,
                    'nombre' => $grupo->nombre,
                    'modulos' => $modulosConUsuarios
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a complete grupo with modules and users
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertGrupoCompleto(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'modulos' => 'required|array|min:1',
                'modulos.*.nombre' => 'required|string|max:255',
                'modulos.*.codigo' => 'required|string|max:100',
                'modulos.*.descripcion' => 'nullable|string',
                'modulos.*.usuario' => 'nullable|array',
                'modulos.*.usuario.id' => 'nullable|exists:users,id'
            ]);

            // Crear el grupo
            $grupo = Grupo::create([
                'nombre' => $validated['nombre'],
                'user_id' => $validated['user_id']
            ]);

            $modulosCreados = [];

            // Crear módulos y asociaciones
            foreach ($validated['modulos'] as $moduloData) {
                $cursar = null;
                
                // Si hay un usuario asociado, crear la relación Cursar
                if (isset($moduloData['usuario']) && isset($moduloData['usuario']['id'])) {
                    $usuario = User::find($moduloData['usuario']['id']);
                    
                    // Verificar que el usuario tenga rol 'user'
                    if ($usuario && $usuario->rol === 'user') {
                        $cursar = Cursar::create([
                            'user_id' => $usuario->id,
                            'grupo_id' => $grupo->id,
                            'fecha_inicio' => now(),
                            'fecha_fin' => null
                        ]);
                    }
                }

                // Crear el módulo
                $modulo = Modulo::create([
                    'nombre' => $moduloData['nombre'],
                    'codigo' => $moduloData['codigo'],
                    'descripcion' => $moduloData['descripcion'] ?? null,
                    'cursar_id' => $cursar ? $cursar->id : null
                ]);

                // Preparar datos del usuario para la respuesta
                $usuarioData = null;
                if ($cursar && $cursar->usuario) {
                    $usuarioData = [
                        'id' => $cursar->usuario->id,
                        'nombre' => $cursar->usuario->name,
                        'apellido' => $cursar->usuario->surname,
                        'email' => $cursar->usuario->email,
                        'dni' => $cursar->usuario->dni
                    ];
                }

                $modulosCreados[] = [
                    'id' => $modulo->id,
                    'nombre' => $modulo->nombre,
                    'codigo' => $modulo->codigo,
                    'descripcion' => $modulo->descripcion,
                    'usuario' => $usuarioData
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Grupo creado exitosamente',
                'data' => [
                    'id' => $grupo->id,
                    'nombre' => $grupo->nombre,
                    'modulos' => $modulosCreados
                ]
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
                'message' => 'Error al crear el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
