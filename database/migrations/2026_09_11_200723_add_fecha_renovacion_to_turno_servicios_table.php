<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turno_servicios', function (Blueprint $table) {
            $table->date('fecha_renovacion')->nullable()->after('duracion_al_momento');
        });
    }

    public function down(): void
    {
        Schema::table('turno_servicios', function (Blueprint $table) {
            $table->dropColumn('fecha_renovacion');
        });
    }
};