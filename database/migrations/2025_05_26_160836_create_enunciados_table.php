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
        Schema::create('enunciado', function (Blueprint $table) {
            $table->id();
            $table->text('descripcion');
            $table->unsignedBigInteger('practica_id')->nullable();
            $table->unsignedBigInteger('modulo_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->datetime('fecha_limite')->nullable();
            $table->unsignedBigInteger('rubrica_id')->nullable();
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->timestamps();

            $table->foreign('practica_id')->references('id')->on('practicas')->onDelete('cascade');
            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rubrica_id')->references('id')->on('rubricas')->onDelete('cascade');
            $table->foreign('grupo_id')->references('id')->on('grupo')->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('enunciado');
    }
};
