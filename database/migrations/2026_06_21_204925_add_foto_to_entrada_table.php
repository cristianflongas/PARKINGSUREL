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
    Schema::table('entrada', function (Blueprint $table) {
        // Guarda la ruta de la foto de entrada después del estado
        $table->string('foto_entrada', 255)->nullable()->after('estado');
    });
}

public function down(): void
{
    Schema::table('entrada', function (Blueprint $table) {
        $table->dropColumn('foto_entrada');
    });
}
};
