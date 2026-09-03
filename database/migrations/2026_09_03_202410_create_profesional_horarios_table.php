<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesional_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana'); // 0 = domingo ... 6 = sábado
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesional_horarios');
    }
};