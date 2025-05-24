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
            $entregas = Entrega::select(
                'entregas.id as entrega_id',
                'entregas.practica_id as entrega_practica_id',
                'entregas.user_id as entrega_user_id',
                'entregas.fecha_entrega as fecha_entrega',
                'entregas.archivo as archivo',
                
                'alumnos.name as alumno_name',
                'alumnos.surname as alumno_surname',
                'alumnos.email as alumno_email',
                'alumnos.dni as alumno_dni',
                'alumnos.rol as alumno_rol',
                
                'practicas.id as practica_id',
                'practicas.identificador as practica_identificador',
                'practicas.titulo as practica_titulo',
                'practicas.descripcion as practica_descripcion',
                'practicas.nombre_practica as practica_nombre',
                'practicas.fecha_entrega as practica_fecha_entrega',
                'practicas.enlace_practica as practica_enlace',
                
                'profesores.name as profesor_name',
                'profesores.surname as profesor_surname',
                'profesores.email as profesor_email',
                'profesores.rol as profesor_rol',
                
                'rubricas.id as rubrica_id',
                'rubricas.nombre as rubrica_nombre',
                'rubricas.documento as rubrica_documento',
                
                'notas.id as nota_id',
                'notas.nota_final as nota_final',
                'notas.comentario as nota_comentario',
                
                'evaluadores.name as evaluador_name',
                'evaluadores.surname as evaluador_surname',
                'evaluadores.email as evaluador_email'
            )
            ->leftJoin('users as alumnos', 'entregas.user_id', '=', 'alumnos.id')
            ->leftJoin('practicas', 'entregas.practica_id', '=', 'practicas.id')
            ->leftJoin('users as profesores', 'practicas.profesor_id', '=', 'profesores.id')
            ->leftJoin('rubricas', 'practicas.id', '=', 'rubricas.practica_id')
            ->leftJoin('notas', 'entregas.id', '=', 'notas.entrega_id')
            ->leftJoin('users as evaluadores', 'notas.user_id', '=', 'evaluadores.id')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $entregas,
                'message' => 'Entregas obtenidas correctamente',
                'total_count' => $entregas->count()
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

            $alumno = User::where('id', $validated['user_id'])
                          ->where('rol', 'user')
                          ->first();

            if (!$alumno) {
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

            $entregaCompleta = Entrega::select(
                'entregas.id as entrega_id',
                'entregas.practica_id as entrega_practica_id',
                'entregas.user_id as entrega_user_id',
                'entregas.fecha_entrega as fecha_entrega',
                'entregas.archivo as archivo',
                
                // Datos del alumno
                'alumnos.name as alumno_name',
                'alumnos.surname as alumno_surname',
                'alumnos.email as alumno_email',
                'alumnos.dni as alumno_dni',
                
                'practicas.identificador as practica_identificador',
                'practicas.titulo as practica_titulo',
                'practicas.nombre_practica as practica_nombre',
                'practicas.fecha_entrega as practica_fecha_entrega',
                
                'profesores.name as profesor_name',
                'profesores.surname as profesor_surname'
            )
            ->join('users as alumnos', 'entregas.user_id', '=', 'alumnos.id')
            ->join('practicas', 'entregas.practica_id', '=', 'practicas.id')
            ->leftJoin('users as profesores', 'practicas.profesor_id', '=', 'profesores.id')
            ->where('entregas.id', $entrega->id)
            ->first();

            return response()->json([
                'success' => true,
                'data' => $entregaCompleta,
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
   
}
