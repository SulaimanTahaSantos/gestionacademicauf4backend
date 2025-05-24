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
        // Hacer nullable las foreign keys en rubricas
        Schema::table('rubricas', function (Blueprint $table) {
            $table->dropForeign(['practica_id']);
            $table->unsignedBigInteger('practica_id')->nullable()->change();
            $table->foreign('practica_id')->references('id')->on('practicas')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en practicas
        Schema::table('practicas', function (Blueprint $table) {
            $table->dropForeign(['modulo_id']);
            $table->dropForeign(['profesor_id']);
            $table->dropForeign(['grupo_id']);
            
            $table->unsignedBigInteger('modulo_id')->nullable()->change();
            $table->unsignedBigInteger('profesor_id')->nullable()->change();
            $table->unsignedBigInteger('grupo_id')->nullable()->change();
            
            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('set null');
            $table->foreign('profesor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('grupo_id')->references('id')->on('grupo')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en entregas
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropForeign(['practica_id']);
            $table->dropForeign(['user_id']);
            
            $table->unsignedBigInteger('practica_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            $table->foreign('practica_id')->references('id')->on('practicas')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en notas
        Schema::table('notas', function (Blueprint $table) {
            $table->dropForeign(['entrega_id']);
            $table->dropForeign(['user_id']);
            // rubrica_id ya es nullable
            
            $table->unsignedBigInteger('entrega_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en cursars
        Schema::table('cursars', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['grupo_id']);
            
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('grupo_id')->nullable()->change();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('grupo_id')->references('id')->on('grupo')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en evaluador_rubrica
        Schema::table('evaluador_rubrica', function (Blueprint $table) {
            $table->dropForeign(['rubrica_id']);
            $table->dropForeign(['user_id']);
            
            $table->unsignedBigInteger('rubrica_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            $table->foreign('rubrica_id')->references('id')->on('rubricas')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en grupo (si tiene user_id)
        Schema::table('grupo', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Hacer nullable las foreign keys en clase (si tiene user_id)
        Schema::table('clase', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en rubricas
        Schema::table('rubricas', function (Blueprint $table) {
            $table->dropForeign(['practica_id']);
            $table->unsignedBigInteger('practica_id')->nullable(false)->change();
            $table->foreign('practica_id')->references('id')->on('practicas')->onDelete('cascade');
        });

        // Revertir cambios en practicas
        Schema::table('practicas', function (Blueprint $table) {
            $table->dropForeign(['modulo_id']);
            $table->dropForeign(['profesor_id']);
            $table->dropForeign(['grupo_id']);
            
            $table->unsignedBigInteger('modulo_id')->nullable(false)->change();
            $table->unsignedBigInteger('profesor_id')->nullable(false)->change();
            $table->unsignedBigInteger('grupo_id')->nullable(false)->change();
            
            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('cascade');
            $table->foreign('profesor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('grupo_id')->references('id')->on('grupo')->onDelete('cascade');
        });

        // Revertir cambios en entregas
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropForeign(['practica_id']);
            $table->dropForeign(['user_id']);
            
            $table->unsignedBigInteger('practica_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            $table->foreign('practica_id')->references('id')->on('practicas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Revertir cambios en notas
        Schema::table('notas', function (Blueprint $table) {
            $table->dropForeign(['entrega_id']);
            $table->dropForeign(['user_id']);
            
            $table->unsignedBigInteger('entrega_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Revertir cambios en cursars
        Schema::table('cursars', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['grupo_id']);
            
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('grupo_id')->nullable(false)->change();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('grupo_id')->references('id')->on('grupo')->onDelete('cascade');
        });

        // Revertir cambios en evaluador_rubrica
        Schema::table('evaluador_rubrica', function (Blueprint $table) {
            $table->dropForeign(['rubrica_id']);
            $table->dropForeign(['user_id']);
            
            $table->unsignedBigInteger('rubrica_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            $table->foreign('rubrica_id')->references('id')->on('rubricas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Revertir cambios en grupo
        Schema::table('grupo', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Revertir cambios en clase
        Schema::table('clase', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
