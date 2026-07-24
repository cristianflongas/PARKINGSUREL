<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id('id_reporte');
            $table->string('nombre_reporte'); // Ej: "Reporte de Ventas Diarias"
            $table->string('tipo_reporte');   // Ej: "INGRESOS", "OCUPACION", "CIERRE_CAJA"
            $table->dateTime('fecha_inicio')->nullable(); // Período consultado
            $table->dateTime('fecha_fin')->nullable();
            $table->decimal('total_recaudado', 12, 2)->default(0.00); // Si aplica
            $table->json('contenido')->nullable(); // Guardar el detalle técnico/JSON
            $table->text('observaciones')->nullable();
            $table->timestamps(); // Genera automáticamente created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
