<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turno_servicios', function (Blueprint $table) {
            $table->timestamp('renovacion_resuelta_at')->nullable()->after('fecha_renovacion');
        });
    }

    public function down(): void
    {
        Schema::table('turno_servicios', function (Blueprint $table) {
            $table->dropColumn('renovacion_resuelta_at');
        });
    }
};