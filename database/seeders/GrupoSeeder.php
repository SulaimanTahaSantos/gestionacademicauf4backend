<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grupo;
use App\Models\User;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        Grupo::create([
            'nombre' => 'DAW2',
            'user_id' => $user->id
        ]);

        Grupo::create([
            'nombre' => 'DAW1',
            'user_id' => $user->id
        ]);

        Grupo::create([
            'nombre' => 'SMX2',
            'user_id' => $user->id
        ]);

        Grupo::create([
            'nombre' => 'SMX1',
            'user_id' => $user->id
        ]);
        Grupo::create([
            'nombre' => 'ARI1',
            'user_id' => $user->id
        ]);
        Grupo::create([
            'nombre' => 'ARI2',
            'user_id' => $user->id
        ]);
        Grupo::create([
            'nombre' => 'IEA1',
            'user_id' => $user->id
        ]);
        Grupo::create([
            'nombre' => 'IEA2',
            'user_id' => $user->id
        ]);
        Grupo::create([
            'nombre' => 'PFI1',
            'user_id' => $user->id
        ]);
        Grupo::create([
            'nombre' => 'PFI2',
            'user_id' => $user->id
        ]);
    }
}
