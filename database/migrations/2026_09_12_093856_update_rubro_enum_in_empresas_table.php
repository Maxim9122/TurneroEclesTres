<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero migramos los valores viejos a los nuevos equivalentes,
        // para no perder el rubro de empresas ya registradas con "odontologia", "medicina" o "masajes".
        DB::table('empresas')->where('rubro', 'odontologia')->update(['rubro' => 'salud']);
        DB::table('empresas')->where('rubro', 'medicina')->update(['rubro' => 'salud']);
        DB::table('empresas')->where('rubro', 'masajes')->update(['rubro' => 'especialista']);

        DB::statement("ALTER TABLE empresas MODIFY COLUMN rubro ENUM(
            'peluqueria', 'barberia', 'estetica', 'unas',
            'salud', 'especialista', 'profesion', 'otro'
        ) NOT NULL DEFAULT 'otro'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE empresas MODIFY COLUMN rubro ENUM(
            'peluqueria', 'barberia', 'estetica', 'unas'
        ) NOT NULL DEFAULT 'peluqueria'");
    }
};