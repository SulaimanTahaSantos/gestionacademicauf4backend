<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enunciado;

class EnunciadoController extends Controller
{
   
    public function index()
    {
        try {
            $enunciados = Enunciado::with([
                'practica' => function($query) {
                    $query->select('id', 'titulo', 'nombre_practica', 'identificador');
                },
                'modulo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'profesor' => function($query) {
                    $query->select('id', 'name', 'rol')->where('rol', 'profesor');
                },
                'rubrica' => function($query) {
                    $query->select('id', 'nombre');
                },
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                }
            ])->get();

            $enunciadosData = $enunciados->map(function ($enunciado) {
                return [
                    'enunciado' => [
                        'id' => $enunciado->id,
                        'descripcion' => $enunciado->descripcion,
                        'fecha_limite' => $enunciado->fecha_limite,
                        'created_at' => $enunciado->created_at,
                        'updated_at' => $enunciado->updated_at
                    ],
                    'practica' => $enunciado->practica ? [
                        'id' => $enunciado->practica->id,
                        'titulo' => $enunciado->practica->titulo,
                        'nombre' => $enunciado->practica->nombre_practica,
                        'identificador' => $enunciado->practica->identificador
                    ] : null,
                    'modulo' => $enunciado->modulo ? [
                        'id' => $enunciado->modulo->id,
                        'nombre' => $enunciado->modulo->nombre
                    ] : null,
                    'profesor' => $enunciado->profesor ? [
                        'id' => $enunciado->profesor->id,
                        'name' => $enunciado->profesor->name,
                        'rol' => $enunciado->profesor->rol
                    ] : null,
                    'rubrica' => $enunciado->rubrica ? [
                        'id' => $enunciado->rubrica->id,
                        'nombre' => $enunciado->rubrica->nombre
                    ] : null,
                    'grupo' => $enunciado->grupo ? [
                        'id' => $enunciado->grupo->id,
                        'nombre' => $enunciado->grupo->nombre
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Enunciados obtenidos exitosamente',
                'data' => $enunciadosData,
                'total' => $enunciadosData->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los enunciados',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
