<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entrega;
use App\Models\User;
use App\Models\Practica;
use App\Models\Nota;
use App\Models\Rubrica;

class EntregaController extends Controller
{
    public function getEntregas()
    {
        try {
            $entregas = Entrega::with([
                'alumno' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'dni', 'rol');
                },
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'fecha_entrega', 'enlace_practica', 'profesor_id');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'nota' => function($query) {
                    $query->select('id', 'entrega_id', 'nota_final', 'comentario', 'user_id', 'rubrica_id');
                },
                'nota.evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'nota.rubrica' => function($query) {
                    $query->select('id', 'nombre', 'documento');
                }
            ])->get();

            $entregasData = $entregas->map(function ($entrega) {
                return [
                    'entrega_id' => $entrega->id,
                    'entrega_practica_id' => $entrega->practica_id,
                    'entrega_user_id' => $entrega->user_id,
                    'fecha_entrega' => $entrega->fecha_entrega,
                    'archivo' => $entrega->archivo,
                    
                    'alumno_name' => $entrega->alumno->name ?? null,
                    'alumno_surname' => $entrega->alumno->surname ?? null,
                    'alumno_email' => $entrega->alumno->email ?? null,
                    'alumno_dni' => $entrega->alumno->dni ?? null,
                    'alumno_rol' => $entrega->alumno->rol ?? null,
                    
                    'practica_id' => $entrega->practica->id ?? null,
                    'practica_identificador' => $entrega->practica->identificador ?? null,
                    'practica_titulo' => $entrega->practica->titulo ?? null,
                    'practica_descripcion' => $entrega->practica->descripcion ?? null,
                    'practica_nombre' => $entrega->practica->nombre_practica ?? null,
                    'practica_fecha_entrega' => $entrega->practica->fecha_entrega ?? null,
                    'practica_enlace' => $entrega->practica->enlace_practica ?? null,
                    
                    'profesor_name' => $entrega->practica->profesor->name ?? null,
                    'profesor_surname' => $entrega->practica->profesor->surname ?? null,
                    'profesor_email' => $entrega->practica->profesor->email ?? null,
                    'profesor_rol' => $entrega->practica->profesor->rol ?? null,
                    
                    'rubrica_id' => $entrega->nota->rubrica->id ?? null,
                    'rubrica_nombre' => $entrega->nota->rubrica->nombre ?? null,
                    'rubrica_documento' => $entrega->nota->rubrica->documento ?? null,
                    
                    'nota_id' => $entrega->nota->id ?? null,
                    'nota_final' => $entrega->nota->nota_final ?? null,
                    'nota_comentario' => $entrega->nota->comentario ?? null,
                    
                    'evaluador_name' => $entrega->nota->evaluador->name ?? null,
                    'evaluador_surname' => $entrega->nota->evaluador->surname ?? null,
                    'evaluador_email' => $entrega->nota->evaluador->email ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $entregasData,
                'message' => 'Entregas obtenidas correctamente',
                'total_count' => $entregasData->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las entregas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'practica_id' => 'required|exists:practicas,id',
                'user_id' => 'required|exists:users,id',
                'fecha_entrega' => 'required|date',
                'archivo' => 'required|string|max:255'
            ]);

            // Use findOrFail and validate role through relationship
            $alumno = User::findOrFail($validated['user_id']);

            if ($alumno->rol !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado debe ser un alumno (rol: user)'
                ], 422);
            }

            $practica = Practica::find($validated['practica_id']);
            if (!$practica) {
                return response()->json([
                    'success' => false,
                    'message' => 'La práctica especificada no existe'
                ], 422);
            }

            $entrega = Entrega::create($validated);

            $entrega->load([
                'alumno' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'dni');
                },
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'nombre_practica', 'fecha_entrega', 'profesor_id');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname');
                }
            ]);

            $responseData = [
                'entrega_id' => $entrega->id,
                'entrega_practica_id' => $entrega->practica_id,
                'entrega_user_id' => $entrega->user_id,
                'fecha_entrega' => $entrega->fecha_entrega,
                'archivo' => $entrega->archivo,
                
                'alumno_name' => $entrega->alumno->name,
                'alumno_surname' => $entrega->alumno->surname,
                'alumno_email' => $entrega->alumno->email,
                'alumno_dni' => $entrega->alumno->dni,
                
                'practica_identificador' => $entrega->practica->identificador,
                'practica_titulo' => $entrega->practica->titulo,
                'practica_nombre' => $entrega->practica->nombre_practica,
                'practica_fecha_entrega' => $entrega->practica->fecha_entrega,
                
                'profesor_name' => $entrega->practica->profesor->name,
                'profesor_surname' => $entrega->practica->profesor->surname,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Entrega creada correctamente'
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
                'message' => 'Error al crear la entrega: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            // Verificar que la entrega existe
            $entrega = Entrega::find($id);
            
            if (!$entrega) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entrega no encontrada'
                ], 404);
            }

            $validated = $request->validate([
                'practica_id' => 'nullable|exists:practicas,id',
                'user_id' => 'nullable|exists:users,id',
                'fecha_entrega' => 'nullable|date',
                'archivo' => 'nullable|string|max:255'
            ]);

            if (isset($validated['user_id'])) {
                $alumno = User::findOrFail($validated['user_id']);

                if ($alumno->rol !== 'user') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El usuario seleccionado debe ser un alumno (rol: user)'
                    ], 422);
                }
            }

            if (isset($validated['practica_id'])) {
                $practica = Practica::find($validated['practica_id']);
                if (!$practica) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La práctica especificada no existe'
                    ], 422);
                }
            }

            $entrega->update($validated);

            $entrega->load([
                'alumno' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'dni');
                },
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'nombre_practica', 'fecha_entrega', 'profesor_id');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'nota' => function($query) {
                    $query->select('id', 'entrega_id', 'nota_final', 'comentario', 'user_id', 'rubrica_id');
                },
                'nota.evaluador' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'nota.rubrica' => function($query) {
                    $query->select('id', 'nombre', 'documento');
                }
            ]);

            $responseData = [
                'entrega_id' => $entrega->id,
                'entrega_practica_id' => $entrega->practica_id,
                'entrega_user_id' => $entrega->user_id,
                'fecha_entrega' => $entrega->fecha_entrega,
                'archivo' => $entrega->archivo,
                
                'alumno_name' => $entrega->alumno->name,
                'alumno_surname' => $entrega->alumno->surname,
                'alumno_email' => $entrega->alumno->email,
                'alumno_dni' => $entrega->alumno->dni,
                
                'practica_identificador' => $entrega->practica->identificador,
                'practica_titulo' => $entrega->practica->titulo,
                'practica_nombre' => $entrega->practica->nombre_practica,
                'practica_fecha_entrega' => $entrega->practica->fecha_entrega,
                
                'profesor_name' => $entrega->practica->profesor->name,
                'profesor_surname' => $entrega->practica->profesor->surname,
                
                'rubrica_nombre' => $entrega->nota->rubrica->nombre ?? null,
                'rubrica_documento' => $entrega->nota->rubrica->documento ?? null,
                
                'nota_final' => $entrega->nota->nota_final ?? null,
                'nota_comentario' => $entrega->nota->comentario ?? null,
                
                'evaluador_name' => $entrega->nota->evaluador->name ?? null,
                'evaluador_surname' => $entrega->nota->evaluador->surname ?? null,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Entrega actualizada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Entrega no encontrada'
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
                'message' => 'Error al actualizar la entrega: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $entrega = Entrega::find($id);
            
            if (!$entrega) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entrega no encontrada'
                ], 404);
            }

            $entrega->load([
                'alumno' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'practica' => function($query) {
                    $query->select('id', 'titulo', 'identificador');
                }
            ]);

            $entregaInfo = [
                'entrega_id' => $entrega->id,
                'archivo' => $entrega->archivo,
                'alumno_name' => $entrega->alumno->name,
                'alumno_surname' => $entrega->alumno->surname,
                'practica_titulo' => $entrega->practica->titulo,
                'practica_identificador' => $entrega->practica->identificador,
            ];

            $entrega->delete();

            return response()->json([
                'success' => true,
                'data' => $entregaInfo,
                'message' => 'Entrega eliminada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Entrega no encontrada'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la entrega: ' . $e->getMessage()
            ], 500);
        }
    }
}
