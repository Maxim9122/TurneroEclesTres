<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->decimal('precio_al_momento', 10, 2);
            $table->unsignedSmallInteger('duracion_al_momento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_servicios');
    }
};