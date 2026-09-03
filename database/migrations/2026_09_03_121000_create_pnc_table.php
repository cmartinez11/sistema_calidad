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
        Schema::create('pnc', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_pnc', 50)->unique();
            $table->string('codigo_inspeccion', 50)->nullable();
            
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Datos Generales
            $table->date('fecha');
            $table->decimal('cantidad', 10, 2)->default(0.00);
            $table->string('unidad_medida', 50)->default('Millares');
            $table->string('cliente_proveedor', 150)->nullable();

            // Descripción de la no conformidad detectada
            $table->text('descripcion_nc');

            // Dónde se detectó la falla
            $table->string('detectado_area', 100)->nullable();
            $table->date('detectado_fecha')->nullable();
            $table->string('detectado_responsable', 150)->nullable();

            // Dónde se originó la no conformidad
            $table->string('originado_area', 100)->nullable();
            $table->date('originado_fecha')->nullable();
            $table->string('originado_responsable', 150)->nullable();

            // Evaluación / Pruebas realizadas
            $table->boolean('eval_revision_registros')->default(false);
            $table->boolean('eval_inspeccion_visual')->default(false);
            $table->boolean('eval_analisis_pruebas')->default(false);
            $table->boolean('eval_otros_check')->default(false);
            $table->string('eval_otros_texto', 255)->nullable();

            // Tratamiento de salida no conforme
            $table->boolean('tratamiento_devolucion')->default(false);
            $table->boolean('tratamiento_reproceso')->default(false);
            $table->boolean('tratamiento_reclasificado')->default(false);
            $table->boolean('tratamiento_molido')->default(false);
            $table->boolean('tratamiento_desperdicio')->default(false);
            $table->boolean('tratamiento_refilado')->default(false);
            $table->boolean('tratamiento_concesion')->default(false);
            $table->boolean('tratamiento_desviacion')->default(false);
            $table->boolean('tratamiento_otros')->default(false);
            $table->string('tratamiento_autorizado_por', 150)->nullable();
            $table->date('tratamiento_fecha')->nullable();

            // Causa Raíz (5M)
            $table->boolean('causa_mano_obra')->default(false);
            $table->boolean('causa_maquina')->default(false);
            $table->boolean('causa_material')->default(false);
            $table->boolean('causa_metodo')->default(false);
            $table->boolean('causa_medio_ambiente')->default(false);
            $table->text('causa_principal')->nullable();
            $table->text('accion_correctiva')->nullable();

            $table->string('estado_pnc', 30)->default('EMITIDO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pnc');
    }
};
