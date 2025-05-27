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
            $modulos = Modulo::with([
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'user' => function($query) {
                    $query->select('id', 'name', 'surname', 'rol')->where('rol', 'profesor');
                }
            ])->get();

            $modulosData = $modulos->map(function ($modulo) {
                return [
                    'modulo_id' => $modulo->id,
                    'modulo_nombre' => $modulo->nombre,
                    'modulo_codigo' => $modulo->codigo,
                    'modulo_descripcion' => $modulo->descripcion,
                    'grupo_nombre' => $modulo->grupo->nombre ?? null,
                    'profesor_name' => $modulo->user->name ?? null,
                    'profesor_surname' => $modulo->user->surname ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $modulosData,
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

            // Verificar que el usuario tenga rol profesor usando findOrFail
            $profesor = User::findOrFail($validated['user_id']);

            if ($profesor->rol !== 'profesor') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no es un profesor'
                ], 422);
            }

            $modulo = Modulo::create($validated);

            $modulo->load([
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'user' => function($query) {
                    $query->select('id', 'name', 'surname', 'rol');
                }
            ]);

            $responseData = [
                'modulo_id' => $modulo->id,
                'modulo_codigo' => $modulo->codigo,
                'modulo_nombre' => $modulo->nombre,
                'modulo_descripcion' => $modulo->descripcion,
                'grupo_nombre' => $modulo->grupo->nombre,
                'profesor_name' => $modulo->user->name,
                'profesor_surname' => $modulo->user->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
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

            $profesor = User::findOrFail($validated['user_id']);

            if ($profesor->rol !== 'profesor') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no es un profesor'
                ], 422);
            }

            $modulo->update($validated);

            $modulo->load([
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'user' => function($query) {
                    $query->select('id', 'name', 'surname', 'rol');
                }
            ]);

            $responseData = [
                'modulo_id' => $modulo->id,
                'modulo_codigo' => $modulo->codigo,
                'modulo_nombre' => $modulo->nombre,
                'modulo_descripcion' => $modulo->descripcion,
                'grupo_nombre' => $modulo->grupo->nombre,
                'profesor_name' => $modulo->user->name,
                'profesor_surname' => $modulo->user->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
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

    public function getModulosProfesor()
    {
        try {
            $user = auth()->user();
            
            $modulos = Modulo::with([
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'user' => function($query) {
                    $query->select('id', 'name', 'surname', 'rol');
                }
            ])->where('user_id', $user->id)->get();

            $modulosData = $modulos->map(function ($modulo) {
                return [
                    'modulo_id' => $modulo->id,
                    'modulo_nombre' => $modulo->nombre,
                    'modulo_codigo' => $modulo->codigo,
                    'modulo_descripcion' => $modulo->descripcion,
                    'grupo_nombre' => $modulo->grupo->nombre ?? null,
                    'profesor_name' => $modulo->user->name ?? null,
                    'profesor_surname' => $modulo->user->surname ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $modulosData,
                'message' => 'Módulos obtenidos correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los módulos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeProfesor(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'codigo' => 'required|string|max:100|unique:modulos,codigo',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'grupo_id' => 'required|exists:grupo,id'
            ]);

            $validated['user_id'] = $user->id; // El profesor autenticado será el propietario

            $modulo = Modulo::create($validated);

            $modulo->load([
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'user' => function($query) {
                    $query->select('id', 'name', 'surname', 'rol');
                }
            ]);

            $responseData = [
                'modulo_id' => $modulo->id,
                'modulo_codigo' => $modulo->codigo,
                'modulo_nombre' => $modulo->nombre,
                'modulo_descripcion' => $modulo->descripcion,
                'grupo_nombre' => $modulo->grupo->nombre,
                'profesor_name' => $modulo->user->name,
                'profesor_surname' => $modulo->user->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
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

    public function updateProfesor(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $modulo = Modulo::where('id', $id)->where('user_id', $user->id)->firstOrFail();

            $validated = $request->validate([
                'codigo' => 'required|string|max:100|unique:modulos,codigo,' . $id,
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'grupo_id' => 'required|exists:grupo,id'
            ]);

            $validated['user_id'] = $user->id; // Mantener el mismo profesor

            $modulo->update($validated);

            $modulo->load([
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'user' => function($query) {
                    $query->select('id', 'name', 'surname', 'rol');
                }
            ]);

            $responseData = [
                'modulo_id' => $modulo->id,
                'modulo_codigo' => $modulo->codigo,
                'modulo_nombre' => $modulo->nombre,
                'modulo_descripcion' => $modulo->descripcion,
                'grupo_nombre' => $modulo->grupo->nombre,
                'profesor_name' => $modulo->user->name,
                'profesor_surname' => $modulo->user->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Módulo actualizado correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado o no tienes permisos para editarlo'
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

    public function destroyProfesor($id)
    {
        try {
            $user = auth()->user();
            $modulo = Modulo::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            
            $modulo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Módulo eliminado correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado o no tienes permisos para eliminarlo'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el módulo: ' . $e->getMessage()
            ], 500);
        }
    }
}
