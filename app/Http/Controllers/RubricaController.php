<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rubrica;
use App\Models\Practica;
use App\Models\User;
use App\Models\Nota;


use Illuminate\Support\Facades\Validator;

class RubricaController extends Controller
{
    /**
     * Get all rubricas with related data using Eloquent
     * Shows: rubrica name, assigned practica, document, evaluator (profesor), and criteria
     */
    public function index()
    {
        try {
            $rubricas = Rubrica::with([
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'profesor_id', 'fecha_entrega', 'enlace_practica');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'criterios' => function($query) {
                    $query->select('id', 'rubrica_id', 'nombre', 'puntuacion_maxima', 'descripcion');
                }
            ])->get();

            $rubricasData = $rubricas->map(function ($rubrica) {
                return [
                    'rubrica' => [
                        'id' => $rubrica->id,
                        'nombre' => $rubrica->nombre,
                        'documento' => $rubrica->documento,
                        'created_at' => $rubrica->created_at,
                        'updated_at' => $rubrica->updated_at
                    ],
                    'practica_asignada' => $rubrica->practica ? [
                        'id' => $rubrica->practica->id,
                        'identificador' => $rubrica->practica->identificador,
                        'titulo' => $rubrica->practica->titulo,
                        'descripcion' => $rubrica->practica->descripcion,
                        'nombre_practica' => $rubrica->practica->nombre_practica,
                        'fecha_entrega' => $rubrica->practica->fecha_entrega,
                        'enlace_practica' => $rubrica->practica->enlace_practica,
                        'profesor_id' => $rubrica->practica->profesor_id
                    ] : null,
                    'profesor_practica' => $rubrica->practica && $rubrica->practica->profesor ? [
                        'id' => $rubrica->practica->profesor->id,
                        'nombre' => $rubrica->practica->profesor->name,
                        'apellido' => $rubrica->practica->profesor->surname,
                        'email' => $rubrica->practica->profesor->email,
                        'rol' => $rubrica->practica->profesor->rol
                    ] : null,
                    'evaluador_asignado' => $rubrica->evaluador ? [
                        'id' => $rubrica->evaluador->id,
                        'nombre' => $rubrica->evaluador->name,
                        'apellido' => $rubrica->evaluador->surname,
                        'email' => $rubrica->evaluador->email,
                        'rol' => $rubrica->evaluador->rol
                    ] : null,
                    'criterios' => $rubrica->criterios->map(function ($criterio) {
                        return [
                            'id' => $criterio->id,
                            'nombre' => $criterio->nombre,
                            'puntuacion_maxima' => $criterio->puntuacion_maxima,
                            'descripcion' => $criterio->descripcion
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Rubricas obtenidas exitosamente',
                'data' => $rubricasData,
                'total' => $rubricasData->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las rubricas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'practica_id' => 'nullable|exists:practicas,id',
                'evaluador_id' => 'nullable|exists:users,id',
                'documento' => 'nullable|string|max:500',
                'criterios' => 'nullable|array',
                'criterios.*.nombre' => 'required_with:criterios|string|max:255',
                'criterios.*.puntuacion_maxima' => 'required_with:criterios|integer|min:1|max:100',
                'criterios.*.descripcion' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->evaluador_id) {
                $evaluador = User::find($request->evaluador_id);
                if (!$evaluador || $evaluador->rol !== 'profesor') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un usuario con rol de profesor'
                    ], 400);
                }
            }

            if ($request->practica_id) {
                $practica = Practica::find($request->practica_id);
                if (!$practica) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La práctica especificada no existe'
                    ], 400);
                }
            }

            $rubrica = Rubrica::create([
                'nombre' => $request->nombre,
                'practica_id' => $request->practica_id,
                'evaluador_id' => $request->evaluador_id,
                'documento' => $request->documento,
            ]);

            if ($request->has('criterios') && is_array($request->criterios)) {
                foreach ($request->criterios as $criterioData) {
                    $rubrica->criterios()->create([
                        'nombre' => $criterioData['nombre'],
                        'puntuacion_maxima' => $criterioData['puntuacion_maxima'],
                        'descripcion' => $criterioData['descripcion'] ?? null
                    ]);
                }
            }

            $rubrica->load([
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'profesor_id', 'fecha_entrega', 'enlace_practica');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'criterios' => function($query) {
                    $query->select('id', 'rubrica_id', 'nombre', 'puntuacion_maxima', 'descripcion');
                }
            ]);

            $responseData = [
                'rubrica' => [
                    'id' => $rubrica->id,
                    'nombre' => $rubrica->nombre,
                    'documento' => $rubrica->documento,
                    'created_at' => $rubrica->created_at,
                    'updated_at' => $rubrica->updated_at
                ],
                'practica_asignada' => $rubrica->practica ? [
                    'id' => $rubrica->practica->id,
                    'identificador' => $rubrica->practica->identificador,
                    'titulo' => $rubrica->practica->titulo,
                    'descripcion' => $rubrica->practica->descripcion,
                    'nombre_practica' => $rubrica->practica->nombre_practica,
                    'fecha_entrega' => $rubrica->practica->fecha_entrega,
                    'enlace_practica' => $rubrica->practica->enlace_practica,
                    'profesor_id' => $rubrica->practica->profesor_id
                ] : null,
                'profesor_practica' => $rubrica->practica && $rubrica->practica->profesor ? [
                    'id' => $rubrica->practica->profesor->id,
                    'nombre' => $rubrica->practica->profesor->name,
                    'apellido' => $rubrica->practica->profesor->surname,
                    'email' => $rubrica->practica->profesor->email,
                    'rol' => $rubrica->practica->profesor->rol
                ] : null,
                'evaluador_asignado' => $rubrica->evaluador ? [
                    'id' => $rubrica->evaluador->id,
                    'nombre' => $rubrica->evaluador->name,
                    'apellido' => $rubrica->evaluador->surname,
                    'email' => $rubrica->evaluador->email,
                    'rol' => $rubrica->evaluador->rol
                ] : null,
                'criterios' => $rubrica->criterios->map(function ($criterio) {
                    return [
                        'id' => $criterio->id,
                        'nombre' => $criterio->nombre,
                        'puntuacion_maxima' => $criterio->puntuacion_maxima,
                        'descripcion' => $criterio->descripcion
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Rúbrica creada exitosamente',
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la rúbrica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function update(Request $request, $id)
    {
        try {
            $rubrica = Rubrica::find($id);
            if (!$rubrica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rúbrica no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'practica_id' => 'sometimes|nullable|exists:practicas,id',
                'evaluador_id' => 'sometimes|nullable|exists:users,id',
                'documento' => 'sometimes|nullable|string|max:500',
                'criterios' => 'sometimes|nullable|array',
                'criterios.*.id' => 'sometimes|exists:criterios_rubrica,id',
                'criterios.*.nombre' => 'required_with:criterios|string|max:255',
                'criterios.*.puntuacion_maxima' => 'required_with:criterios|integer|min:1|max:100',
                'criterios.*.descripcion' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('evaluador_id') && $request->evaluador_id) {
                $evaluador = User::find($request->evaluador_id);
                if (!$evaluador || $evaluador->rol !== 'profesor') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un usuario con rol de profesor'
                    ], 400);
                }
            }

            if ($request->has('practica_id') && $request->practica_id) {
                $practica = Practica::find($request->practica_id);
                if (!$practica) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La práctica especificada no existe'
                    ], 400);
                }
            }

            $updateData = [];
            if ($request->has('nombre')) $updateData['nombre'] = $request->nombre;
            if ($request->has('practica_id')) $updateData['practica_id'] = $request->practica_id;
            if ($request->has('evaluador_id')) $updateData['evaluador_id'] = $request->evaluador_id;
            if ($request->has('documento')) $updateData['documento'] = $request->documento;

            $rubrica->update($updateData);

            // Manejar criterios si se proporcionan
            if ($request->has('criterios') && is_array($request->criterios)) {
                $criteriosIds = [];
                
                foreach ($request->criterios as $criterioData) {
                    if (isset($criterioData['id'])) {
                        $criterio = $rubrica->criterios()->find($criterioData['id']);
                        if ($criterio) {
                            $criterio->update([
                                'nombre' => $criterioData['nombre'],
                                'puntuacion_maxima' => $criterioData['puntuacion_maxima'],
                                'descripcion' => $criterioData['descripcion'] ?? null
                            ]);
                            $criteriosIds[] = $criterio->id;
                        }
                    } else {
                        $nuevoCriterio = $rubrica->criterios()->create([
                            'nombre' => $criterioData['nombre'],
                            'puntuacion_maxima' => $criterioData['puntuacion_maxima'],
                            'descripcion' => $criterioData['descripcion'] ?? null
                        ]);
                        $criteriosIds[] = $nuevoCriterio->id;
                    }
                }

                $rubrica->criterios()->whereNotIn('id', $criteriosIds)->delete();
            }

            $rubrica->load([
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'profesor_id', 'fecha_entrega', 'enlace_practica');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'criterios' => function($query) {
                    $query->select('id', 'rubrica_id', 'nombre', 'puntuacion_maxima', 'descripcion');
                }
            ]);

            $responseData = [
                'rubrica' => [
                    'id' => $rubrica->id,
                    'nombre' => $rubrica->nombre,
                    'documento' => $rubrica->documento,
                    'created_at' => $rubrica->created_at,
                    'updated_at' => $rubrica->updated_at
                ],
                'practica_asignada' => $rubrica->practica ? [
                    'id' => $rubrica->practica->id,
                    'identificador' => $rubrica->practica->identificador,
                    'titulo' => $rubrica->practica->titulo,
                    'descripcion' => $rubrica->practica->descripcion,
                    'nombre_practica' => $rubrica->practica->nombre_practica,
                    'fecha_entrega' => $rubrica->practica->fecha_entrega,
                    'enlace_practica' => $rubrica->practica->enlace_practica,
                    'profesor_id' => $rubrica->practica->profesor_id
                ] : null,
                'profesor_practica' => $rubrica->practica && $rubrica->practica->profesor ? [
                    'id' => $rubrica->practica->profesor->id,
                    'nombre' => $rubrica->practica->profesor->name,
                    'apellido' => $rubrica->practica->profesor->surname,
                    'email' => $rubrica->practica->profesor->email,
                    'rol' => $rubrica->practica->profesor->rol
                ] : null,
                'evaluador_asignado' => $rubrica->evaluador ? [
                    'id' => $rubrica->evaluador->id,
                    'nombre' => $rubrica->evaluador->name,
                    'apellido' => $rubrica->evaluador->surname,
                    'email' => $rubrica->evaluador->email,
                    'rol' => $rubrica->evaluador->rol
                ] : null,
                'criterios' => $rubrica->criterios->map(function ($criterio) {
                    return [
                        'id' => $criterio->id,
                        'nombre' => $criterio->nombre,
                        'puntuacion_maxima' => $criterio->puntuacion_maxima,
                        'descripcion' => $criterio->descripcion
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Rúbrica actualizada exitosamente',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la rúbrica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  
    public function destroy($id)
    {
        try {
            $rubrica = Rubrica::find($id);
            if (!$rubrica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rúbrica no encontrada'
                ], 404);
            }

            $rubrica->load([
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'profesor_id', 'fecha_entrega', 'enlace_practica');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'criterios' => function($query) {
                    $query->select('id', 'rubrica_id', 'nombre', 'puntuacion_maxima', 'descripcion');
                }
            ]);

            $deletedData = [
                'rubrica' => [
                    'id' => $rubrica->id,
                    'nombre' => $rubrica->nombre,
                    'documento' => $rubrica->documento,
                    'created_at' => $rubrica->created_at,
                    'updated_at' => $rubrica->updated_at
                ],
                'practica_asignada' => $rubrica->practica ? [
                    'id' => $rubrica->practica->id,
                    'identificador' => $rubrica->practica->identificador,
                    'titulo' => $rubrica->practica->titulo,
                    'descripcion' => $rubrica->practica->descripcion,
                    'nombre_practica' => $rubrica->practica->nombre_practica,
                    'fecha_entrega' => $rubrica->practica->fecha_entrega,
                    'enlace_practica' => $rubrica->practica->enlace_practica,
                    'profesor_id' => $rubrica->practica->profesor_id
                ] : null,
                'profesor_practica' => $rubrica->practica && $rubrica->practica->profesor ? [
                    'id' => $rubrica->practica->profesor->id,
                    'nombre' => $rubrica->practica->profesor->name,
                    'apellido' => $rubrica->practica->profesor->surname,
                    'email' => $rubrica->practica->profesor->email,
                    'rol' => $rubrica->practica->profesor->rol
                ] : null,
                'evaluador_asignado' => $rubrica->evaluador ? [
                    'id' => $rubrica->evaluador->id,
                    'nombre' => $rubrica->evaluador->name,
                    'apellido' => $rubrica->evaluador->surname,
                    'email' => $rubrica->evaluador->email,
                    'rol' => $rubrica->evaluador->rol
                ] : null,
                'criterios_eliminados' => $rubrica->criterios->map(function ($criterio) {
                    return [
                        'id' => $criterio->id,
                        'nombre' => $criterio->nombre,
                        'puntuacion_maxima' => $criterio->puntuacion_maxima,
                        'descripcion' => $criterio->descripcion
                    ];
                }),
                'total_criterios_eliminados' => $rubrica->criterios->count()
            ];

            $notasAsociadas = $rubrica->notas()->count();
            
            if ($notasAsociadas > 0) {
                $rubrica->notas()->delete();
                $deletedData['notas_eliminadas'] = $notasAsociadas;
            } else {
                $deletedData['notas_eliminadas'] = 0;
            }

            $rubrica->criterios()->delete();
            
            $rubrica->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rúbrica eliminada exitosamente',
                'data' => $deletedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la rúbrica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexProfesor()
    {
        try {
            $user = auth()->user();
            
            $rubricas = Rubrica::with([
                'practica.profesor',
                'evaluador',
                'notas'
            ])->whereHas('practica', function($query) use ($user) {
                $query->where('profesor_id', $user->id);
            })->get();

            $rubricasData = $rubricas->map(function ($rubrica) {
                return [
                    'id' => $rubrica->id,
                    'nombre' => $rubrica->nombre,
                    'documento' => $rubrica->documento,
                    'practica_titulo' => $rubrica->practica->titulo ?? null,
                    'practica_descripcion' => $rubrica->practica->descripcion ?? null,
                    'profesor_name' => $rubrica->practica->profesor->name ?? null,
                    'profesor_surname' => $rubrica->practica->profesor->surname ?? null,
                    'evaluador_name' => $rubrica->evaluador->name ?? null,
                    'evaluador_surname' => $rubrica->evaluador->surname ?? null,
                    'total_notas' => $rubrica->notas->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $rubricasData,
                'message' => 'Rúbricas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las rúbricas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeProfesor(Request $request)
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'practica_id' => 'required|exists:practicas,id',
                'documento' => 'nullable|string',
                'evaluador_id' => 'required|exists:users,id'
            ]);

            // Verificar que la práctica pertenece al profesor
            $practica = \App\Models\Practica::where('id', $validated['practica_id'])
                ->where('profesor_id', $user->id)
                ->firstOrFail();

            $rubrica = Rubrica::create($validated);

            $rubrica->load([
                'practica.profesor',
                'evaluador',
                'notas'
            ]);

            $responseData = [
                'id' => $rubrica->id,
                'nombre' => $rubrica->nombre,
                'documento' => $rubrica->documento,
                'practica_titulo' => $rubrica->practica->titulo,
                'profesor_name' => $rubrica->practica->profesor->name,
                'profesor_surname' => $rubrica->practica->profesor->surname,
                'evaluador_name' => $rubrica->evaluador->name,
                'evaluador_surname' => $rubrica->evaluador->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Rúbrica creada correctamente'
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Práctica no encontrada o no tienes permisos'
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
                'message' => 'Error al crear la rúbrica: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfesor(Request $request, $id)
    {
        try {
            $user = auth()->user();
            
            $rubrica = Rubrica::with('practica')
                ->whereHas('practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'practica_id' => 'required|exists:practicas,id',
                'documento' => 'nullable|string',
                'evaluador_id' => 'required|exists:users,id'
            ]);

            // Verificar que la nueva práctica también pertenece al profesor
            $practica = \App\Models\Practica::where('id', $validated['practica_id'])
                ->where('profesor_id', $user->id)
                ->firstOrFail();

            $rubrica->update($validated);

            $rubrica->load([
                'practica.profesor',
                'evaluador',
                'notas'
            ]);

            $responseData = [
                'id' => $rubrica->id,
                'nombre' => $rubrica->nombre,
                'documento' => $rubrica->documento,
                'practica_titulo' => $rubrica->practica->titulo,
                'profesor_name' => $rubrica->practica->profesor->name,
                'profesor_surname' => $rubrica->practica->profesor->surname,
                'evaluador_name' => $rubrica->evaluador->name,
                'evaluador_surname' => $rubrica->evaluador->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Rúbrica actualizada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rúbrica no encontrada o no tienes permisos para editarla'
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
                'message' => 'Error al actualizar la rúbrica: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyProfesor($id)
    {
        try {
            $user = auth()->user();
            
            $rubrica = Rubrica::with('practica')
                ->whereHas('practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($id);
            
            $rubrica->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rúbrica eliminada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rúbrica no encontrada o no tienes permisos para eliminarla'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la rúbrica: ' . $e->getMessage()
            ], 500);
        }
    }
}
