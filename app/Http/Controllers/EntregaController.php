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
                
                // Datos del alumno
                'alumnos.name as alumno_name',
                'alumnos.surname as alumno_surname',
                'alumnos.email as alumno_email',
                'alumnos.dni as alumno_dni',
                'alumnos.rol as alumno_rol',
                
                // Datos de la práctica
                'practicas.id as practica_id',
                'practicas.identificador as practica_identificador',
                'practicas.titulo as practica_titulo',
                'practicas.descripcion as practica_descripcion',
                'practicas.nombre_practica as practica_nombre',
                'practicas.fecha_entrega as practica_fecha_entrega',
                'practicas.enlace_practica as practica_enlace',
                
                // Datos del profesor de la práctica
                'profesores.name as profesor_name',
                'profesores.surname as profesor_surname',
                'profesores.email as profesor_email',
                'profesores.rol as profesor_rol',
                
                // Datos de la rúbrica
                'rubricas.id as rubrica_id',
                'rubricas.nombre as rubrica_nombre',
                'rubricas.documento as rubrica_documento',
                
                // Datos de la nota
                'notas.id as nota_id',
                'notas.nota_final as nota_final',
                'notas.comentario as nota_comentario',
                
                // Datos del evaluador (profesor que calificó)
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
    
    public function debugEntregas()
    {
        try {
            // Check all entregas with their relationships
            $entregas = Entrega::all();
            $practicas = Practica::all();
            $rubricas = Rubrica::all();
            $users = User::all();
            
            $debug_data = [
                'entregas_count' => $entregas->count(),
                'practicas_count' => $practicas->count(),
                'rubricas_count' => $rubricas->count(),
                'users_count' => $users->count(),
                'entregas' => $entregas,
                'practicas' => $practicas,
                'rubricas' => $rubricas,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'rol' => $user->rol
                    ];
                }),
                'relationship_issues' => []
            ];
            
            // Check for relationship issues
            foreach ($entregas as $entrega) {
                $practica_exists = $practicas->where('id', $entrega->practica_id)->first();
                $user_exists = $users->where('id', $entrega->user_id)->first();
                
                if (!$practica_exists) {
                    $debug_data['relationship_issues'][] = "Entrega {$entrega->id} references non-existent practica_id {$entrega->practica_id}";
                }
                
                if (!$user_exists) {
                    $debug_data['relationship_issues'][] = "Entrega {$entrega->id} references non-existent user_id {$entrega->user_id}";
                }
            }
            
            return response()->json([
                'success' => true,
                'debug_data' => $debug_data
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in debug: ' . $e->getMessage()
            ], 500);
        }
    }
}
