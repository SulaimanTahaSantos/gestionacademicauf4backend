<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clase;
use App\Models\User;

class ClaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegúrate de que tienes algunos usuarios en la base de datos
        $user = User::first();

        // Lista de nombres de clases
        $clases = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8'];

        // Crear las clases
        foreach ($clases as $nombreClase) {
            Clase::create([
                'nombre' => $nombreClase,
                'user_id' => $user->id
            ]);
        }
    }
}
