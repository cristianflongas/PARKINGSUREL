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
    Schema::table('salida', function (Blueprint $table) {
        // Guarda la ruta de la foto de salida después de la fecha de hora de salida
        $table->string('foto_salida', 255)->nullable()->after('fecha_hora_salida');
    });
}

public function down(): void
{
    Schema::table('salida', function (Blueprint $table) {
        $table->dropColumn('foto_salida');
    });
}
};
