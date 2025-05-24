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
                'entregas.fecha_entrega as fecha_entrega',
                'entregas.archivo as archivo',
                
                // Datos del alumno
                'alumnos.name as alumno_name',
                'alumnos.surname as alumno_surname',
                'alumnos.email as alumno_email',
                'alumnos.dni as alumno_dni',
                
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
            ->where('alumnos.rol', 'user')
            ->where('profesores.rol', 'profesor')
            ->where(function($query) {
                $query->whereNull('evaluadores.rol')
                      ->orWhere('evaluadores.rol', 'profesor');
            })
            ->get();

            return response()->json([
                'success' => true,
                'data' => $entregas,
                'message' => 'Entregas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las entregas: ' . $e->getMessage()
            ], 500);
        }
    }
}
