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
        Schema::create('alertas_calidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspeccion_id')->constrained('inspecciones_calidad')->onDelete('cascade');
            $table->foreignId('maquina_id')->constrained('maquinas');
            $table->string('tipo_alerta', 100);
            $table->decimal('valor_registrado', 8, 2);
            $table->decimal('limite_permitido', 8, 2);
            $table->boolean('atendida')->default(false);
            $table->text('observaciones_correccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas_calidad');
    }
};
