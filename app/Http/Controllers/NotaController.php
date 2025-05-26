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
}
