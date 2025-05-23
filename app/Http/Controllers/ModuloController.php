<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modulo;
use App\Models\Cursar;
use App\Models\Grupo;
use App\Models\User;

class ModuloController extends Controller
{
  
    public function getModulos()
    {
        try {
            $modulos = Modulo::select(
                'modulos.id as modulo_id',
                'modulos.nombre as modulo_nombre',
                'modulos.codigo as modulo_codigo',
                'modulos.descripcion as modulo_descripcion',
                'grupo.nombre as grupo_nombre',
                'users.name as profesor_name',
                'users.surname as profesor_surname'
            )
            ->join('impartido_modulos', 'modulos.id', '=', 'impartido_modulos.modulo_id')
            ->join('grupo', 'impartido_modulos.grupo_id', '=', 'grupo.id')
            ->join('users', 'grupo.user_id', '=', 'users.id')
            ->where('users.rol', 'profesor')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $modulos,
                'message' => 'Módulos obtenidos correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los módulos: ' . $e->getMessage()
            ], 500);
        }
    }
}
