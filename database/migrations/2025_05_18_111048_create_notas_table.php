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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrega_id');
            $table->unsignedBigInteger('user_id'); 
            $table->unsignedBigInteger('rubrica_id')->nullable();
            $table->decimal('nota_final', 5, 2);
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rubrica_id')->references('id')->on('rubricas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
