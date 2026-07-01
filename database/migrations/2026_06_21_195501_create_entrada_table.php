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
    Schema::create('entrada', function (Blueprint $table) {
        $table->integer('id_entrada')->autoIncrement();
        $table->string('placa', 8);
        $table->integer('id_modulo');
        $table->integer('id_personal');
        $table->integer('id_tipo_servicio');
        $table->dateTime('fecha_hora_entrada')->useCurrent();
        $table->string('estado', 20)->default('ACTIVO');
        $table->timestamps();

        // Relaciones
        $table->foreign('placa')->references('placa')->on('vehiculo')->onDelete('restrict')->onUpdate('cascade');
        $table->foreign('id_modulo')->references('id_modulo')->on('modulo')->onDelete('restrict')->onUpdate('restrict');
        $table->foreign('id_personal')->references('id_personal')->on('personal')->onDelete('restrict')->onUpdate('restrict');
        $table->foreign('id_tipo_servicio')->references('id_tipo_servicio')->on('tipo_servicio')->onDelete('restrict')->onUpdate('restrict');
    });
}

public function down(): void
{
    Schema::dropIfExists('entrada');
}
};
