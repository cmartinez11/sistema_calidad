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
        Schema::create('parametros_preforma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');

            //nro de cavidades
            $table->integer('numero_cavidades')->default(0);
            // Pesos (g)
            $table->decimal('peso_nominal', 8, 2);
            $table->decimal('peso_min', 8, 2);
            $table->decimal('peso_max', 8, 2);
            // Espesores de Pared (mm)
            $table->decimal('esp_pared_min', 8, 2)->nullable();
            $table->decimal('esp_pared_max', 8, 2)->nullable();
            // Espesores de Fondo (mm)
            $table->decimal('esp_fondo_min', 8, 2)->nullable();
            $table->decimal('esp_fondo_max', 8, 2)->nullable();
            // Altura (mm)
            $table->decimal('altura_min', 8, 2)->nullable();
            $table->decimal('altura_max', 8, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_preforma');
    }
};
