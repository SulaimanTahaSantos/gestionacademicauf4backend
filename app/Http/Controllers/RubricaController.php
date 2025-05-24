<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rubrica;
use App\Models\Practica;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class RubricaController extends Controller
{
    /**
     * Get all rubricas with related data using Eloquent
     * Shows: rubrica name, assigned practica, document, evaluator (profesor), and criteria
     */
    public function index()
    {
        try {
            $rubricas = Rubrica::with([
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'profesor_id', 'fecha_entrega', 'enlace_practica');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'criterios' => function($query) {
                    $query->select('id', 'rubrica_id', 'nombre', 'puntuacion_maxima', 'descripcion');
                }
            ])->get();

            $rubricasData = $rubricas->map(function ($rubrica) {
                return [
                    'rubrica' => [
                        'id' => $rubrica->id,
                        'nombre' => $rubrica->nombre,
                        'documento' => $rubrica->documento,
                        'created_at' => $rubrica->created_at,
                        'updated_at' => $rubrica->updated_at
                    ],
                    'practica_asignada' => $rubrica->practica ? [
                        'id' => $rubrica->practica->id,
                        'identificador' => $rubrica->practica->identificador,
                        'titulo' => $rubrica->practica->titulo,
                        'descripcion' => $rubrica->practica->descripcion,
                        'nombre_practica' => $rubrica->practica->nombre_practica,
                        'fecha_entrega' => $rubrica->practica->fecha_entrega,
                        'enlace_practica' => $rubrica->practica->enlace_practica,
                        'profesor_id' => $rubrica->practica->profesor_id
                    ] : null,
                    'profesor_practica' => $rubrica->practica && $rubrica->practica->profesor ? [
                        'id' => $rubrica->practica->profesor->id,
                        'nombre' => $rubrica->practica->profesor->name,
                        'apellido' => $rubrica->practica->profesor->surname,
                        'email' => $rubrica->practica->profesor->email,
                        'rol' => $rubrica->practica->profesor->rol
                    ] : null,
                    'evaluador_asignado' => $rubrica->evaluador ? [
                        'id' => $rubrica->evaluador->id,
                        'nombre' => $rubrica->evaluador->name,
                        'apellido' => $rubrica->evaluador->surname,
                        'email' => $rubrica->evaluador->email,
                        'rol' => $rubrica->evaluador->rol
                    ] : null,
                    'criterios' => $rubrica->criterios->map(function ($criterio) {
                        return [
                            'id' => $criterio->id,
                            'nombre' => $criterio->nombre,
                            'puntuacion_maxima' => $criterio->puntuacion_maxima,
                            'descripcion' => $criterio->descripcion
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Rubricas obtenidas exitosamente',
                'data' => $rubricasData,
                'total' => $rubricasData->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las rubricas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new rubrica
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'practica_id' => 'nullable|exists:practicas,id',
                'evaluador_id' => 'nullable|exists:users,id',
                'documento' => 'nullable|string|max:500',
                'criterios' => 'nullable|array',
                'criterios.*.nombre' => 'required_with:criterios|string|max:255',
                'criterios.*.puntuacion_maxima' => 'required_with:criterios|integer|min:1|max:100',
                'criterios.*.descripcion' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->evaluador_id) {
                $evaluador = User::find($request->evaluador_id);
                if (!$evaluador || $evaluador->rol !== 'profesor') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El evaluador debe ser un usuario con rol de profesor'
                    ], 400);
                }
            }

            if ($request->practica_id) {
                $practica = Practica::find($request->practica_id);
                if (!$practica) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La práctica especificada no existe'
                    ], 400);
                }
            }

            $rubrica = Rubrica::create([
                'nombre' => $request->nombre,
                'practica_id' => $request->practica_id,
                'evaluador_id' => $request->evaluador_id,
                'documento' => $request->documento,
            ]);

            if ($request->has('criterios') && is_array($request->criterios)) {
                foreach ($request->criterios as $criterioData) {
                    $rubrica->criterios()->create([
                        'nombre' => $criterioData['nombre'],
                        'puntuacion_maxima' => $criterioData['puntuacion_maxima'],
                        'descripcion' => $criterioData['descripcion'] ?? null
                    ]);
                }
            }

            $rubrica->load([
                'practica' => function($query) {
                    $query->select('id', 'identificador', 'titulo', 'descripcion', 'nombre_practica', 'profesor_id', 'fecha_entrega', 'enlace_practica');
                },
                'practica.profesor' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'evaluador' => function($query) {
                    $query->select('id', 'name', 'surname', 'email', 'rol');
                },
                'criterios' => function($query) {
                    $query->select('id', 'rubrica_id', 'nombre', 'puntuacion_maxima', 'descripcion');
                }
            ]);

            $responseData = [
                'rubrica' => [
                    'id' => $rubrica->id,
                    'nombre' => $rubrica->nombre,
                    'documento' => $rubrica->documento,
                    'created_at' => $rubrica->created_at,
                    'updated_at' => $rubrica->updated_at
                ],
                'practica_asignada' => $rubrica->practica ? [
                    'id' => $rubrica->practica->id,
                    'identificador' => $rubrica->practica->identificador,
                    'titulo' => $rubrica->practica->titulo,
                    'descripcion' => $rubrica->practica->descripcion,
                    'nombre_practica' => $rubrica->practica->nombre_practica,
                    'fecha_entrega' => $rubrica->practica->fecha_entrega,
                    'enlace_practica' => $rubrica->practica->enlace_practica,
                    'profesor_id' => $rubrica->practica->profesor_id
                ] : null,
                'profesor_practica' => $rubrica->practica && $rubrica->practica->profesor ? [
                    'id' => $rubrica->practica->profesor->id,
                    'nombre' => $rubrica->practica->profesor->name,
                    'apellido' => $rubrica->practica->profesor->surname,
                    'email' => $rubrica->practica->profesor->email,
                    'rol' => $rubrica->practica->profesor->rol
                ] : null,
                'evaluador_asignado' => $rubrica->evaluador ? [
                    'id' => $rubrica->evaluador->id,
                    'nombre' => $rubrica->evaluador->name,
                    'apellido' => $rubrica->evaluador->surname,
                    'email' => $rubrica->evaluador->email,
                    'rol' => $rubrica->evaluador->rol
                ] : null,
                'criterios' => $rubrica->criterios->map(function ($criterio) {
                    return [
                        'id' => $criterio->id,
                        'nombre' => $criterio->nombre,
                        'puntuacion_maxima' => $criterio->puntuacion_maxima,
                        'descripcion' => $criterio->descripcion
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Rúbrica creada exitosamente',
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la rúbrica',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
