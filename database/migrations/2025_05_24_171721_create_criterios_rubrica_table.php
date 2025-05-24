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
        Schema::create('criterios_rubrica', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rubrica_id')->nullable();
            $table->string('nombre');
            $table->integer('puntuacion_maxima');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('rubrica_id')->references('id')->on('rubricas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_rubrica');
    }
};
