<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Cursar;
use App\Models\Modulo;
use App\Models\User;




class GrupoController extends Controller
{
    /**
     * Get grupos with modules and users
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrupos()
    {
        try {
            $grupos = Grupo::all();
            
            $resultado = $grupos->map(function($grupo) {
                // Obtener cursars de este grupo
                $cursarsDelGrupo = Cursar::where('grupo_id', $grupo->id)->get();
                
                // Obtener módulos que pertenecen a este grupo
                $cursarIds = $cursarsDelGrupo->pluck('id');
                $modulos = Modulo::whereIn('cursar_id', $cursarIds)->get();
                
                // Para cada módulo, obtener los usuarios (alumnos)
                $modulosConUsuarios = $modulos->map(function($modulo) {
                    // Obtener el cursar asociado al módulo
                    $cursar = Cursar::find($modulo->cursar_id);
                    $usuario = null;
                    
                    if ($cursar && $cursar->usuario && $cursar->usuario->rol === 'user') {
                        $usuario = [
                            'id' => $cursar->usuario->id,
                            'nombre' => $cursar->usuario->name,
                            'apellido' => $cursar->usuario->surname,
                            'email' => $cursar->usuario->email,
                            'dni' => $cursar->usuario->dni
                        ];
                    }
                    
                    return [
                        'id' => $modulo->id,
                        'nombre' => $modulo->nombre,
                        'codigo' => $modulo->codigo,
                        'descripcion' => $modulo->descripcion,
                        'usuario' => $usuario
                    ];
                });
                
                return [
                    'id' => $grupo->id,
                    'nombre' => $grupo->nombre,
                    'modulos' => $modulosConUsuarios
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
