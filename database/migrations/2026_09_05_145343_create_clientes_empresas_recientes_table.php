<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes_empresas_recientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->timestamp('ultima_interaccion');
            $table->timestamps();

            $table->unique(['cliente_id', 'empresa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_empresas_recientes');
    }
};