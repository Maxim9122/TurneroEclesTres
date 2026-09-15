<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->text('motivo');
            $table->json('items_anterior');
            $table->decimal('total_anterior', 10, 2);
            $table->enum('metodo_entrega_anterior', ['retiro', 'envio']);
            $table->foreignId('direccion_envio_id_anterior')->nullable()->constrained('direcciones')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_historial');
    }
};