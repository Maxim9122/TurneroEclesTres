<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_profesional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->unique(['servicio_id', 'profesional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_profesional');
    }
};