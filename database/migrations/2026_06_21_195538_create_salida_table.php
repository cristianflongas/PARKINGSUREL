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
    Schema::create('salida', function (Blueprint $table) {
        $table->integer('id_salida')->autoIncrement();
        $table->integer('id_entrada')->unique();
        $table->dateTime('fecha_hora_salida')->useCurrent();
        $table->timestamps();

        $table->foreign('id_entrada')->references('id_entrada')->on('entrada')->onDelete('restrict')->onUpdate('restrict');
    });
}

public function down(): void
{
    Schema::dropIfExists('salida');
}
};
