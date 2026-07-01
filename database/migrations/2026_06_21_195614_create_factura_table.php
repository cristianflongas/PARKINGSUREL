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
    Schema::create('factura', function (Blueprint $table) {
        $table->integer('id_factura')->autoIncrement();
        $table->integer('id_salida')->nullable()->unique();
        $table->dateTime('fecha_emision')->useCurrent();
        $table->decimal('monto_total', 10, 2)->default(0.00);
        $table->string('metodo_pago', 20)->nullable();
        $table->string('estado_pago', 20)->default('PENDIENTE');
        $table->timestamps();

        $table->foreign('id_salida')->references('id_salida')->on('salida')->onDelete('restrict')->onUpdate('restrict');
    });
}

public function down(): void
{
    Schema::dropIfExists('factura');
}
};
