<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('motivos_observacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar catálogo inicial de motivos de observación
        DB::table('motivos_observacion')->insert([
            [
                'nombre' => 'Aprobación condicional por jefatura de calidad',
                'descripcion' => 'Desviación menor aprobada condicionalmente tras evaluación metrológica',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Desviación temporal autorizada de parámetros',
                'descripcion' => 'Ajuste temporal en parámetros de inyección autorizado en producción',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ajuste de proceso en línea en seguimiento',
                'descripcion' => 'Se corrigió en línea y se mantiene seguimiento en las siguientes cajas',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Muestreo de validación técnica',
                'descripcion' => 'Validación técnica por cambio de molde, resina o arranque de máquina',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Otro motivo con observación menor',
                'descripcion' => 'Observación particular documentada en la auditoría',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivos_observacion');
    }
};
