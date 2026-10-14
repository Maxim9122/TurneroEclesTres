<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extras opcionales de un servicio (ej: "Diseño" o "Líneas" en un corte), con su precio y minutos extra.
        Schema::create('servicio_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->decimal('precio', 10, 2)->default(0);
            $table->unsignedSmallInteger('duracion_minutos')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Adicionales elegidos en cada turno. Guarda nombre, precio y duración del momento de la reserva,
        // así los turnos ya hechos no cambian si después se edita o se borra el adicional.
        Schema::create('turno_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('servicio_adicional_id')->nullable()->constrained('servicio_adicionales')->nullOnDelete();
            $table->string('nombre_al_momento', 100);
            $table->decimal('precio_al_momento', 10, 2)->default(0);
            $table->unsignedSmallInteger('duracion_al_momento')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_adicionales');
        Schema::dropIfExists('servicio_adicionales');
    }
};
