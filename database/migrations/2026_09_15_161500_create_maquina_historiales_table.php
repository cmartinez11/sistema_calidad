<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('maquina_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maquina_id')->constrained('maquinas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('estado_anterior')->nullable();
            $table->string('nuevo_estado');
            $table->text('motivo_observacion')->nullable();
            $table->timestamp('fecha_hora_inicio')->useCurrent();
            $table->timestamp('fecha_hora_fin')->nullable();
            $table->integer('duracion_minutos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquina_historiales');
    }
};
