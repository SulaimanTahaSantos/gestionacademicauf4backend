<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enunciado;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

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


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'descripcion' => 'required|string',
                'practica_id' => 'nullable|exists:practicas,id',
                'modulo_id' => 'nullable|exists:modulos,id',
                'user_id' => 'nullable|exists:users,id',
                'fecha_limite' => 'nullable|date',
                'rubrica_id' => 'nullable|exists:rubricas,id',
                'grupo_id' => 'nullable|exists:grupo,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->user_id) {
                $usuario = User::find($request->user_id);
                if (!$usuario || $usuario->rol !== 'profesor') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El usuario debe tener rol de profesor'
                    ], 400);
                }
            }

            $enunciado = Enunciado::create([
                'descripcion' => $request->descripcion,
                'practica_id' => $request->practica_id,
                'modulo_id' => $request->modulo_id,
                'user_id' => $request->user_id,
                'fecha_limite' => $request->fecha_limite,
                'rubrica_id' => $request->rubrica_id,
                'grupo_id' => $request->grupo_id
            ]);

            $enunciado->load([
                'practica' => function($query) {
                    $query->select('id', 'titulo', 'nombre_practica', 'identificador');
                },
                'modulo' => function($query) {
                    $query->select('id', 'nombre');
                },
                'profesor' => function($query) {
                    $query->select('id', 'name', 'rol');
                },
                'rubrica' => function($query) {
                    $query->select('id', 'nombre');
                },
                'grupo' => function($query) {
                    $query->select('id', 'nombre');
                }
            ]);

            $responseData = [
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

            return response()->json([
                'success' => true,
                'message' => 'Enunciado creado exitosamente',
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el enunciado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
