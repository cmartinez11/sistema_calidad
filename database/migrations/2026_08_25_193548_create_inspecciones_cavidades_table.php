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
        Schema::create('inspecciones_cavidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspeccion_id')->constrained('inspecciones_calidad')->onDelete('cascade');
            $table->unsignedTinyInteger('cavidad_numero'); // Número de cavidad (1 al 40)
            $table->decimal('peso_medido', 8, 2);         // El peso real de esa cavidad
            $table->enum('estado', ['CONFORME', 'FUERA_DE_RANGO'])->default('CONFORME');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspecciones_cavidades');
    }
};
