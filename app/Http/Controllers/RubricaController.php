<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RubricaController extends Controller
{
    /**
     * Get all rubricas with related data
     * Shows: rubrica name, assigned practica, document, evaluator (profesor), and criteria
     */
    public function index()
    {
        try {
            $rubricas = DB::table('rubricas')
                ->leftJoin('practicas', 'rubricas.practica_id', '=', 'practicas.id')
                ->leftJoin('users as evaluadores', 'rubricas.evaluador_id', '=', 'evaluadores.id')
                ->leftJoin('users as profesores', 'practicas.profesor_id', '=', 'profesores.id')
                ->select(
                    'rubricas.id as rubrica_id',
                    'rubricas.nombre as rubrica_nombre',
                    'rubricas.documento as rubrica_documento',
                    'rubricas.practica_id',
                    'rubricas.evaluador_id',
                    'rubricas.created_at as rubrica_creada',
                    'rubricas.updated_at as rubrica_actualizada',
                    
                    // Practica data
                    'practicas.nombre as practica_nombre',
                    'practicas.descripcion as practica_descripcion',
                    'practicas.fecha_inicio as practica_fecha_inicio',
                    'practicas.fecha_fin as practica_fecha_fin',
                    'practicas.profesor_id',
                    
                    // Profesor data (owner of practica)
                    'profesores.name as profesor_nombre',
                    'profesores.email as profesor_email',
                    'profesores.role as profesor_role',
                    
                    // Evaluador data (assigned to rubrica)
                    'evaluadores.name as evaluador_nombre',
                    'evaluadores.email as evaluador_email',
                    'evaluadores.role as evaluador_role'
                )
                ->get();

            // Get criteria for each rubrica
            $rubricasWithCriteria = $rubricas->map(function ($rubrica) {
                $criterios = DB::table('criterios_rubrica')
                    ->where('rubrica_id', $rubrica->rubrica_id)
                    ->select('id', 'nombre', 'puntuacion_maxima', 'descripcion')
                    ->get();

                return [
                    'rubrica' => [
                        'id' => $rubrica->rubrica_id,
                        'nombre' => $rubrica->rubrica_nombre,
                        'documento' => $rubrica->rubrica_documento,
                        'created_at' => $rubrica->rubrica_creada,
                        'updated_at' => $rubrica->rubrica_actualizada
                    ],
                    'practica_asignada' => $rubrica->practica_id ? [
                        'id' => $rubrica->practica_id,
                        'nombre' => $rubrica->practica_nombre,
                        'descripcion' => $rubrica->practica_descripcion,
                        'fecha_inicio' => $rubrica->practica_fecha_inicio,
                        'fecha_fin' => $rubrica->practica_fecha_fin,
                        'profesor_id' => $rubrica->profesor_id
                    ] : null,
                    'profesor_practica' => $rubrica->profesor_id ? [
                        'id' => $rubrica->profesor_id,
                        'nombre' => $rubrica->profesor_nombre,
                        'email' => $rubrica->profesor_email,
                        'role' => $rubrica->profesor_role
                    ] : null,
                    'evaluador_asignado' => $rubrica->evaluador_id ? [
                        'id' => $rubrica->evaluador_id,
                        'nombre' => $rubrica->evaluador_nombre,
                        'email' => $rubrica->evaluador_email,
                        'role' => $rubrica->evaluador_role
                    ] : null,
                    'criterios' => $criterios->toArray()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Rubricas obtenidas exitosamente',
                'data' => $rubricasWithCriteria,
                'total' => $rubricasWithCriteria->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las rubricas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
