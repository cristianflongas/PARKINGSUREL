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
        Schema::create('personal', function (Blueprint $table) {
            $table->integer('id_personal')->autoIncrement();
            $table->string('cedula_users', 20)->unique();
            $table->integer('id_rol');
            $table->string('usuario', 50)->unique();
            $table->string('password_hash', 255);
            $table->timestamps();

            // Relaciones (Llaves Foráneas)
            $table->foreign('cedula_users')->references('cedula')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};