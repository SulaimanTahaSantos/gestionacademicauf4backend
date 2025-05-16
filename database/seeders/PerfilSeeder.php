<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $perfiles = ['Alumno', 'Profesor', 'Administrador'];

        foreach ($perfiles as $nombre) {
            Perfil::create(['nombre' => $nombre]);
        }
    }
}
