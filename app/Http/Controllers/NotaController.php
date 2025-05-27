<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use App\Models\User;

class NotaController extends Controller
{
    public function getNotas()
    {
        try {
            $notas = Nota::with([
                'entrega' => function($query) {
                    $query->select('id', 'archivo', 'user_id');
                },
                'entrega.alumno' => function($query) {
                    $query->select('id', 'name', 'surname')->where('rol', 'user');
                },
                'rubrica' => function($query) {
                    $query->select('id', 'documento');
                }
            ])->get();

            $notasData = $notas->map(function ($nota) {
                return [
                    'notas_id' => $nota->id,
                    'nota_final' => $nota->nota_final,
                    'notas_comentario' => $nota->comentario,
                    'entregas_archivo' => $nota->entrega->archivo ?? null,
                    'alumno_name' => $nota->entrega->alumno->name ?? null,
                    'alumno_surname' => $nota->entrega->alumno->surname ?? null,
                    'rubrica_documento' => $nota->rubrica->documento ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $notasData,
                'message' => 'Notas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'entrega_id' => 'nullable|exists:entregas,id',
                'user_id' => 'nullable|exists:users,id',
                'rubrica_id' => 'nullable|exists:rubricas,id',
                'nota_final' => 'required|numeric|min:0|max:10',
                'comentario' => 'nullable|string|max:1000'
            ]);

            if (isset($validated['user_id'])) {
                $evaluador = User::findOrFail($validated['user_id']);

                if ($evaluador->rol !== 'profesor') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un profesor'
                    ], 422);
                }
            }

            $nota = Nota::create($validated);

            $nota->load([
                'entrega' => function($query) {
                    $query->select('id', 'archivo', 'user_id');
                },
                'entrega.alumno' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'rubrica' => function($query) {
                    $query->select('id', 'documento');
                }
            ]);

            $responseData = [
                'notas_id' => $nota->id,
                'nota_final' => $nota->nota_final,
                'notas_comentario' => $nota->comentario,
                'entregas_archivo' => $nota->entrega->archivo,
                'alumno_name' => $nota->entrega->alumno->name,
                'alumno_surname' => $nota->entrega->alumno->surname,
                'evaluador_name' => $nota->evaluador->name,
                'evaluador_surname' => $nota->evaluador->surname,
                'rubrica_documento' => $nota->rubrica->documento ?? null,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Nota creada correctamente'
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
                'message' => 'Error al crear la nota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $nota = Nota::find($id);
            
            if (!$nota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota no encontrada'
                ], 404);
            }

            $validated = $request->validate([
                'entrega_id' => 'nullable|exists:entregas,id',
                'user_id' => 'nullable|exists:users,id',
                'rubrica_id' => 'nullable|exists:rubricas,id',
                'nota_final' => 'required|numeric|min:0|max:10',
                'comentario' => 'nullable|string|max:1000'
            ]);

            if (isset($validated['user_id'])) {
                $evaluador = User::findOrFail($validated['user_id']);

                if ($evaluador->rol !== 'profesor') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un profesor'
                    ], 422);
                }
            }

            $nota->update($validated);

            $nota->load([
                'entrega' => function($query) {
                    $query->select('id', 'archivo', 'user_id');
                },
                'entrega.alumno' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname');
                },
                'rubrica' => function($query) {
                    $query->select('id', 'documento');
                }
            ]);

            $responseData = [
                'notas_id' => $nota->id,
                'nota_final' => $nota->nota_final,
                'notas_comentario' => $nota->comentario,
                'entregas_archivo' => $nota->entrega->archivo,
                'alumno_name' => $nota->entrega->alumno->name,
                'alumno_surname' => $nota->entrega->alumno->surname,
                'evaluador_name' => $nota->evaluador->name,
                'evaluador_surname' => $nota->evaluador->surname,
                'rubrica_documento' => $nota->rubrica->documento ?? null,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Nota actualizada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada'
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
                'message' => 'Error al actualizar la nota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $nota = Nota::find($id);
            
            if (!$nota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota no encontrada'
                ], 404);
            }

            $notaData = [
                'notas_id' => $nota->id,
                'nota_final' => $nota->nota_final,
                'comentario' => $nota->comentario
            ];

            $nota->delete();

            return response()->json([
                'success' => true,
                'data' => $notaData,
                'message' => 'Nota eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la nota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNotasProfesor()
    {
        try {
            $user = auth()->user();
            
            $notas = Nota::with([
                'entrega.alumno',
                'evaluador',
                'rubrica'
            ])->whereHas('entrega.practica', function($query) use ($user) {
                $query->where('profesor_id', $user->id);
            })->get();

            $notasData = $notas->map(function ($nota) {
                return [
                    'nota_id' => $nota->id,
                    'nota_final' => $nota->nota_final,
                    'comentario' => $nota->comentario,
                    'alumno_name' => $nota->entrega->alumno->name ?? null,
                    'alumno_surname' => $nota->entrega->alumno->surname ?? null,
                    'alumno_email' => $nota->entrega->alumno->email ?? null,
                    'evaluador_name' => $nota->evaluador->name ?? null,
                    'evaluador_surname' => $nota->evaluador->surname ?? null,
                    'rubrica_nombre' => $nota->rubrica->nombre ?? null,
                    'entrega_id' => $nota->entrega->id ?? null,
                    'fecha_entrega' => $nota->entrega->fecha_entrega ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $notasData,
                'message' => 'Notas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeProfesor(Request $request)
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'entrega_id' => 'required|exists:entregas,id',
                'user_id' => 'required|exists:users,id',
                'rubrica_id' => 'required|exists:rubricas,id',
                'nota_final' => 'required|numeric|min:0|max:10',
                'comentario' => 'nullable|string'
            ]);

            // Verificar que la entrega pertenece a una práctica del profesor
            $entrega = \App\Models\Entrega::with('practica')
                ->whereHas('practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($validated['entrega_id']);

            // Verificar que la rúbrica pertenece a una práctica del profesor
            $rubrica = \App\Models\Rubrica::with('practica')
                ->whereHas('practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($validated['rubrica_id']);

            $nota = Nota::create($validated);

            $nota->load([
                'entrega.alumno',
                'evaluador',
                'rubrica'
            ]);

            $responseData = [
                'nota_id' => $nota->id,
                'nota_final' => $nota->nota_final,
                'comentario' => $nota->comentario,
                'alumno_name' => $nota->entrega->alumno->name,
                'alumno_surname' => $nota->entrega->alumno->surname,
                'evaluador_name' => $nota->evaluador->name,
                'evaluador_surname' => $nota->evaluador->surname,
                'rubrica_nombre' => $nota->rubrica->nombre,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Nota creada correctamente'
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Entrega o rúbrica no encontrada o no tienes permisos'
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
                'message' => 'Error al crear la nota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfesor(Request $request, $id)
    {
        try {
            $user = auth()->user();
            
            $nota = Nota::with(['entrega.practica'])
                ->whereHas('entrega.practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($id);

            $validated = $request->validate([
                'entrega_id' => 'required|exists:entregas,id',
                'user_id' => 'required|exists:users,id',
                'rubrica_id' => 'required|exists:rubricas,id',
                'nota_final' => 'required|numeric|min:0|max:10',
                'comentario' => 'nullable|string'
            ]);

            // Verificar permisos sobre la nueva entrega y rúbrica
            $entrega = \App\Models\Entrega::with('practica')
                ->whereHas('practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($validated['entrega_id']);

            $rubrica = \App\Models\Rubrica::with('practica')
                ->whereHas('practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($validated['rubrica_id']);

            $nota->update($validated);

            $nota->load([
                'entrega.alumno',
                'evaluador',
                'rubrica'
            ]);

            $responseData = [
                'nota_id' => $nota->id,
                'nota_final' => $nota->nota_final,
                'comentario' => $nota->comentario,
                'alumno_name' => $nota->entrega->alumno->name,
                'alumno_surname' => $nota->entrega->alumno->surname,
                'evaluador_name' => $nota->evaluador->name,
                'evaluador_surname' => $nota->evaluador->surname,
                'rubrica_nombre' => $nota->rubrica->nombre,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Nota actualizada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada o no tienes permisos para editarla'
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
                'message' => 'Error al actualizar la nota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyProfesor($id)
    {
        try {
            $user = auth()->user();
            
            $nota = Nota::with(['entrega.practica'])
                ->whereHas('entrega.practica', function($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->findOrFail($id);
            
            $nota->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nota eliminada correctamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada o no tienes permisos para eliminarla'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la nota: ' . $e->getMessage()
            ], 500);
        }
    }
}
