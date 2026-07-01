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
    Schema::create('vehiculo', function (Blueprint $table) {
        $table->string('placa', 8)->primary();
        $table->integer('id_cliente');
        $table->string('marca', 30)->nullable();
        $table->string('modelo', 30)->nullable();
        $table->integer('anio')->nullable();
        $table->string('color', 20)->nullable();
        $table->timestamps();

        $table->foreign('id_cliente')->references('id_cliente')->on('cliente')->onDelete('restrict')->onUpdate('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('vehiculo');
}
};
