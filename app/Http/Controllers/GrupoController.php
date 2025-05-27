<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Cursar;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;





class GrupoController extends Controller
{
    public function getGrupos()
    {
        try {
            $grupos = Grupo::with([
                'cursars.usuario' => function($query) {
                    $query->where('rol', 'user');
                },
                'modulos.cursar.usuario' => function($query) {
                    $query->where('rol', 'user');
                }
            ])->get();
            
            $resultado = $grupos->map(function($grupo) {
                // Get modulos through relationships
                $modulosConUsuarios = $grupo->modulos->map(function($modulo) {
                    $usuario = null;
                    
                    if ($modulo->cursar && $modulo->cursar->usuario && $modulo->cursar->usuario->rol === 'user') {
                        $usuario = [
                            'id' => $modulo->cursar->usuario->id,
                            'nombre' => $modulo->cursar->usuario->name,
                            'apellido' => $modulo->cursar->usuario->surname,
                            'email' => $modulo->cursar->usuario->email,
                            'dni' => $modulo->cursar->usuario->dni
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
    
    public function insertGrupoCompleto(Request $request)
    {
        try {
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

            $grupo = Grupo::create([
                'nombre' => $validated['nombre'],
                'user_id' => $validated['user_id']
            ]);

            $modulosCreados = [];

            foreach ($validated['modulos'] as $moduloData) {
                $cursar = null;
                
                if (isset($moduloData['usuario']) && isset($moduloData['usuario']['id'])) {
                    $usuario = User::find($moduloData['usuario']['id']);
                    
                    if ($usuario && $usuario->rol === 'user') {
                        $cursar = Cursar::create([
                            'user_id' => $usuario->id,
                            'grupo_id' => $grupo->id,
                            'fecha_inicio' => now(),
                            'fecha_fin' => null
                        ]);
                    }
                }

                $userId = null;
                if (isset($moduloData['usuario']) && isset($moduloData['usuario']['id'])) {
                    $userId = $moduloData['usuario']['id'];
                }

                $modulo = Modulo::create([
                    'nombre' => $moduloData['nombre'],
                    'codigo' => $moduloData['codigo'],
                    'descripcion' => $moduloData['descripcion'] ?? null,
                    'cursar_id' => $cursar ? $cursar->id : null,
                    'grupo_id' => $grupo->id,
                    'user_id' => $userId
                ]);

                $usuarioData = null;
                if ($userId) {
                    $modulo->load('user');
                    if ($modulo->user) {
                        $usuarioData = [
                            'id' => $modulo->user->id,
                            'nombre' => $modulo->user->name,
                            'apellido' => $modulo->user->surname,
                            'email' => $modulo->user->email,
                            'dni' => $modulo->user->dni
                        ];
                    }
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

    public function updateGrupoCompleto(Request $request, $id)
    {
        try {
            $grupo = Grupo::find($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'modulos' => 'required|array|min:1',
                'modulos.*.id' => 'nullable|exists:modulos,id',
                'modulos.*.nombre' => 'required|string|max:255',
                'modulos.*.codigo' => 'required|string|max:100',
                'modulos.*.descripcion' => 'nullable|string',
                'modulos.*.usuario' => 'nullable|array',
                'modulos.*.usuario.id' => 'nullable|exists:users,id'
            ]);

            $grupo->update([
                'nombre' => $validated['nombre'],
                'user_id' => $validated['user_id']
            ]);

            $grupo->load(['cursars', 'modulos.cursar']);
            $modulosExistentes = $grupo->modulos;
            $modulosExistentesIds = $modulosExistentes->pluck('id');

            $modulosActualizados = [];
            $modulosEnviados = collect($validated['modulos']);
            $modulosEnviadosIds = $modulosEnviados->pluck('id')->filter();

            $modulosAEliminar = $modulosExistentes->whereNotIn('id', $modulosEnviadosIds);
            foreach ($modulosAEliminar as $modulo) {
                if ($modulo->cursar_id) {
                    Cursar::find($modulo->cursar_id)?->delete();
                }
                $modulo->delete();
            }

            foreach ($validated['modulos'] as $moduloData) {
                $cursar = null;
                $modulo = null;

                if (isset($moduloData['usuario']) && isset($moduloData['usuario']['id'])) {
                    $usuario = User::find($moduloData['usuario']['id']);
                    
                    if ($usuario && $usuario->rol === 'user') {
                        
                        if (isset($moduloData['id'])) {
                            $moduloExistente = Modulo::find($moduloData['id']);
                            if ($moduloExistente && $moduloExistente->cursar_id) {
                                $cursar = Cursar::find($moduloExistente->cursar_id);
                                if ($cursar) {
                                    $cursar->update([
                                        'user_id' => $usuario->id,
                                        'grupo_id' => $grupo->id,
                                        'fecha_inicio' => $cursar->fecha_inicio, 
                                        'fecha_fin' => null
                                    ]);
                                }
                            }
                        }
                        
                        if (!$cursar) {
                            $cursar = Cursar::create([
                                'user_id' => $usuario->id,
                                'grupo_id' => $grupo->id,
                                'fecha_inicio' => now(),
                                'fecha_fin' => null
                            ]);
                        }
                    }
                }

                $userId = null;
                if (isset($moduloData['usuario']) && isset($moduloData['usuario']['id'])) {
                    $userId = $moduloData['usuario']['id'];
                }

                if (isset($moduloData['id'])) {
                    $modulo = Modulo::find($moduloData['id']);
                    if ($modulo) {
                        $modulo->update([
                            'nombre' => $moduloData['nombre'],
                            'codigo' => $moduloData['codigo'],
                            'descripcion' => $moduloData['descripcion'] ?? null,
                            'cursar_id' => $cursar ? $cursar->id : null,
                            'grupo_id' => $grupo->id,
                            'user_id' => $userId
                        ]);
                    }
                } else {
                    $modulo = Modulo::create([
                        'nombre' => $moduloData['nombre'],
                        'codigo' => $moduloData['codigo'],
                        'descripcion' => $moduloData['descripcion'] ?? null,
                        'cursar_id' => $cursar ? $cursar->id : null,
                        'grupo_id' => $grupo->id,
                        'user_id' => $userId
                    ]);
                }

                $usuarioData = null;
                if ($userId) {
                    // Load the user directly from the module's user_id
                    $modulo->load('user');
                    if ($modulo->user) {
                        $usuarioData = [
                            'id' => $modulo->user->id,
                            'nombre' => $modulo->user->name,
                            'apellido' => $modulo->user->surname,
                            'email' => $modulo->user->email,
                            'dni' => $modulo->user->dni
                        ];
                    }
                }

                $modulosActualizados[] = [
                    'id' => $modulo->id,
                    'nombre' => $modulo->nombre,
                    'codigo' => $modulo->codigo,
                    'descripcion' => $modulo->descripcion,
                    'usuario' => $usuarioData
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado exitosamente',
                'data' => [
                    'id' => $grupo->id,
                    'nombre' => $grupo->nombre,
                    'modulos' => $modulosActualizados
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo no encontrado'
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
                'message' => 'Error al actualizar el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteGrupoCompleto($id)
    {
        try {
            $grupo = Grupo::with(['cursars', 'modulos'])->findOrFail($id);

            foreach ($grupo->modulos as $modulo) {
                $modulo->delete();
            }

            foreach ($grupo->cursars as $cursar) {
                $cursar->delete();
            }

            $grupoNombre = $grupo->nombre;
            $grupo->delete();

            return response()->json([
                'success' => true,
                'message' => "Grupo '{$grupoNombre}' eliminado exitosamente junto con todos sus módulos y relaciones"
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo no encontrado'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getGruposProfesor()
    {
        try {
            $user = auth()->user();
            
            $grupos = Grupo::with([
                'cursars.usuario' => function($query) {
                    $query->where('rol', 'user');
                },
                'modulos' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'modulos.cursar.usuario' => function($query) {
                    $query->where('rol', 'user');
                }
            ])->whereHas('modulos', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();
            
            $resultado = $grupos->map(function($grupo) {
                $modulosConUsuarios = $grupo->modulos->map(function($modulo) {
                    $usuario = null;
                    
                    if ($modulo->cursar && $modulo->cursar->usuario && $modulo->cursar->usuario->rol === 'user') {
                        $usuario = [
                            'id' => $modulo->cursar->usuario->id,
                            'nombre' => $modulo->cursar->usuario->name,
                            'apellido' => $modulo->cursar->usuario->surname,
                            'email' => $modulo->cursar->usuario->email,
                            'dni' => $modulo->cursar->usuario->dni
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
                'data' => $resultado,
                'message' => 'Grupos obtenidos correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos: ' . $e->getMessage()
            ], 500);
        }
    }
}
