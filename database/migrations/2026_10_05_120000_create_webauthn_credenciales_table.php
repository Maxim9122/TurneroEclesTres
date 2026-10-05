<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Llaves de acceso (huella / rostro / patrón) del staff. Solo se guarda la clave PÚBLICA;
        // la privada nunca sale del dispositivo del usuario.
        Schema::create('webauthn_credenciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->char('credencial_hash', 64)->unique(); // sha256 del id de la credencial, para buscarla al ingresar
            $table->text('registro');                      // CredentialRecord serializado (clave pública, contador, etc.)
            $table->string('nombre', 100);                 // "Android · Chrome", para reconocerla en Mi perfil
            $table->timestamp('ultimo_uso_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webauthn_credenciales');
    }
};
