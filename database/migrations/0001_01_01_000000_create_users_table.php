<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla de Usuarios modificada para PARKINGSURE
        Schema::create('users', function (Blueprint $table) {
            $table->string('cedula', 20)->primary(); // Tu clave primaria
            $table->string('nombre', 80);
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 80)->nullable();
            $table->timestamps();
        });

        // 2. Tabla de Tokens (Adaptada para usar correo)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Tabla de Sesiones (Adaptada para usar tu clave foránea cedula_users de tipo string)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 20)->nullable()->index(); // Cambiado a string para que coincida con tu cédula
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};