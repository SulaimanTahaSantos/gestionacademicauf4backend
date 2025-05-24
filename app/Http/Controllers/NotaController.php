<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;

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
}
