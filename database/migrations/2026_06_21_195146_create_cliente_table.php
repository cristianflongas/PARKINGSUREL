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
    Schema::create('cliente', function (Blueprint $table) {
        $table->integer('id_cliente')->autoIncrement();
        $table->string('cedula_users', 20)->unique();
        $table->timestamps();

        $table->foreign('cedula_users')->references('cedula')->on('users')->onDelete('restrict')->onUpdate('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('cliente');
}

};
