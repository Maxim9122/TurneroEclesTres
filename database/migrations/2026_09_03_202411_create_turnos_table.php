<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('profesional_id')->nullable()->constrained('profesionales')->nullOnDelete();
            $table->foreignId('creado_por_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', ['pendiente', 'confirmado', 'cancelado', 'completado', 'no_show'])
                ->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'fecha']);
            $table->index(['profesional_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};