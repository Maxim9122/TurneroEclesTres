<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->enum('rubro', ['peluqueria', 'barberia', 'estetica', 'unas'])->default('peluqueria');
            $table->enum('estado', ['pendiente', 'activa', 'suspendida', 'rechazada', 'cancelada'])->default('pendiente');
            $table->string('logo_path')->nullable();
            $table->string('color_fondo', 7)->nullable();
            $table->string('imagen_fondo_path')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email_contacto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};