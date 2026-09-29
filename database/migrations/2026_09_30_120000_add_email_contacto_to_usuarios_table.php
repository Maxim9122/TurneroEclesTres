<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Email público de contacto (pie de página). Lo usa el super_admin; es distinto del email de ingreso.
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('email_contacto')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('email_contacto');
        });
    }
};
