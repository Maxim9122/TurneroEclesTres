<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['email' => 'maxi91luna@gmail.com'], // cambiá este email si querés
            [
                'nombre' => 'Administrador EclesTres',
                'password' => Hash::make('Darkelemecles.'), // cambiá esta contraseña
                'rol' => 'super_admin',
                'empresa_id' => null,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}