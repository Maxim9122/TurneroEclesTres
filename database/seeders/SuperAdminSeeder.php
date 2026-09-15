<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Crea el primer usuario super_admin de la plataforma.
     * Después de correrlo, entrá por Tinker y cambiá el email/contraseña
     * por los reales antes de usarlo en producción.
     */
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['email' => 'admin@ejemplo.com'],
            [
                'nombre' => 'Super Admin',
                'password' => Hash::make(Str::random(20)),
                'rol' => 'super_admin',
                'empresa_id' => null,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}