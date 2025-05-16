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
        Schema::create('practicas', function (Blueprint $table) {
            $table->id();

            $table->string('identificador')->unique(); 
            $table->string('titulo'); 
            $table->text('descripcion')->nullable(); 

            $table->unsignedBigInteger('modulo_id'); 
            $table->string('nombre_practica'); 

            $table->unsignedBigInteger('profesor_id'); 
            $table->date('fecha_entrega');

            $table->string('rubrica')->nullable(); 

            $table->unsignedBigInteger('grupo_id');
            $table->string('enlace_practica')->nullable(); 
            $table->timestamps();

            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('cascade');
            $table->foreign('profesor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practicas');
    }
};
