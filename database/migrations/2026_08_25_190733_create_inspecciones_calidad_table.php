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
        Schema::create('inspecciones_calidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('maquina_id')->constrained('maquinas');
            $table->foreignId('user_id')->constrained('users'); // Usuario que usa el sistema
            $table->foreignId('turno_id')->constrained('turnos');

            // Mediciones físicas reales
            $table->decimal('peso_min', 8, 2)->nullable();
            $table->decimal('peso_max', 8, 2)->nullable();
            $table->decimal('esp_pared_medido', 8, 2)->nullable();
            $table->decimal('esp_fondo_medido', 8, 2)->nullable();
            $table->decimal('altura_medida', 8, 2)->nullable();

            $table->enum('estado_evaluacion', ['CONFORME', 'OBSERVADO_PNC'])->default('CONFORME');
            $table->string('desviacion', 150)->nullable();
            $table->text('causa')->nullable();
            $table->text('comentarios')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspecciones_calidad');
    }
};
