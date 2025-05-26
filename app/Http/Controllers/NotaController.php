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
            $notas = Nota::select(
                'notas.id as notas_id',
                'notas.nota_final as nota_final',
                'notas.comentario as notas_comentario',
                'entregas.archivo as entregas_archivo',
                'users.name as alumno_name',
                'users.surname as alumno_surname',
                'rubricas.documento as rubrica_documento'
            )
            ->join('entregas', 'notas.entrega_id', '=', 'entregas.id')
            ->join('users', 'entregas.user_id', '=', 'users.id')  
            ->leftJoin('rubricas', 'notas.rubrica_id', '=', 'rubricas.id')  
            ->where('users.rol', 'user')  
            ->get();

            return response()->json([
                'success' => true,
                'data' => $notas,
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
                $evaluador = User::where('id', $validated['user_id'])
                                ->where('rol', 'profesor')
                                ->first();

                if (!$evaluador) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un profesor'
                    ], 422);
                }
            }

            $nota = Nota::create($validated);

            $notaCompleta = Nota::select(
                'notas.id as notas_id',
                'notas.nota_final as nota_final',
                'notas.comentario as notas_comentario',
                'entregas.archivo as entregas_archivo',
                'users.name as alumno_name',
                'users.surname as alumno_surname',
                'evaluadores.name as evaluador_name',
                'evaluadores.surname as evaluador_surname',
                'rubricas.documento as rubrica_documento'
            )
            ->leftJoin('entregas', 'notas.entrega_id', '=', 'entregas.id')
            ->leftJoin('users', 'entregas.user_id', '=', 'users.id')
            ->leftJoin('users as evaluadores', 'notas.user_id', '=', 'evaluadores.id')
            ->leftJoin('rubricas', 'notas.rubrica_id', '=', 'rubricas.id')
            ->where('notas.id', $nota->id)
            ->first();

            return response()->json([
                'success' => true,
                'data' => $notaCompleta,
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
                $evaluador = User::where('id', $validated['user_id'])
                                ->where('rol', 'profesor')
                                ->first();

                if (!$evaluador) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un profesor'
                    ], 422);
                }
            }

            $nota->update($validated);

            $notaCompleta = Nota::select(
                'notas.id as notas_id',
                'notas.nota_final as nota_final',
                'notas.comentario as notas_comentario',
                'entregas.archivo as entregas_archivo',
                'users.name as alumno_name',
                'users.surname as alumno_surname',
                'evaluadores.name as evaluador_name',
                'evaluadores.surname as evaluador_surname',
                'rubricas.documento as rubrica_documento'
            )
            ->leftJoin('entregas', 'notas.entrega_id', '=', 'entregas.id')
            ->leftJoin('users', 'entregas.user_id', '=', 'users.id')
            ->leftJoin('users as evaluadores', 'notas.user_id', '=', 'evaluadores.id')
            ->leftJoin('rubricas', 'notas.rubrica_id', '=', 'rubricas.id')
            ->where('notas.id', $nota->id)
            ->first();

            return response()->json([
                'success' => true,
                'data' => $notaCompleta,
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
