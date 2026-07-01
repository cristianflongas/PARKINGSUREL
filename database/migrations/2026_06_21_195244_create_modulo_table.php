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
    Schema::create('modulo', function (Blueprint $table) {
        $table->integer('id_modulo')->autoIncrement();
        $table->string('ubicacion', 50)->nullable();
        $table->string('estado', 20)->default('DISPONIBLE');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('modulo');
}
};
