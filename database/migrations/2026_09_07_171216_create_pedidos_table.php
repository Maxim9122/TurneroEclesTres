<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->enum('metodo_entrega', ['retiro', 'envio']);
            $table->foreignId('direccion_envio_id')->nullable()->constrained('direcciones')->nullOnDelete();
            $table->enum('estado', ['pendiente', 'confirmado', 'en_preparacion', 'listo', 'enviado', 'entregado', 'cancelado'])
                ->default('pendiente');
            $table->decimal('total', 10, 2);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};