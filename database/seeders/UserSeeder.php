<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Admin',
            'surname' => 'System',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'dni' => '12345678A',
            'rol' => 'admin'
        ]);

        // Crear algunos profesores
        User::create([
            'name' => 'Juan',
            'surname' => 'García',
            'email' => 'juan.garcia@example.com',
            'password' => Hash::make('password123'),
            'dni' => '23456789B',
            'rol' => 'profesor'
        ]);

        User::create([
            'name' => 'María',
            'surname' => 'López',
            'email' => 'maria.lopez@example.com',
            'password' => Hash::make('password123'),
            'dni' => '34567890C',
            'rol' => 'profesor'
        ]);

        // Crear algunos alumnos
        User::create([
            'name' => 'Carlos',
            'surname' => 'Martínez',
            'email' => 'carlos.martinez@example.com',
            'password' => Hash::make('password123'),
            'dni' => '45678901D',
            'rol' => 'alumno'
        ]);

        User::create([
            'name' => 'Ana',
            'surname' => 'Sánchez',
            'email' => 'ana.sanchez@example.com',
            'password' => Hash::make('password123'),
            'dni' => '56789012E',
            'rol' => 'alumno'
        ]);
    }
}
