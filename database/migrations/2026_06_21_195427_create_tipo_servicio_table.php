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
    Schema::create('tipo_servicio', function (Blueprint $table) {
        $table->integer('id_tipo_servicio')->autoIncrement();
        $table->string('nombre_tipo_servicio', 100);
        $table->decimal('tarifa', 10, 2);
        $table->string('estado', 20)->default('ACTIVO');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('tipo_servicio');
}
};
