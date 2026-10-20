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
        Schema::create('produccions', function (Blueprint $table) {
            $table->id();
            $table->string('nro_produccion')->unique(); // PRC-0001
            $table->date('fecha_produccion');
            $table->enum('turno', ['DIA', 'NOCHE']);
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('operario_id')->constrained('operarios')->onDelete('cascade');
            $table->foreignId('maquina_id')->constrained('maquinas')->onDelete('cascade');
            $table->foreignId('resina_id')->constrained('resinas')->onDelete('cascade');
            $table->string('presentacion'); // Caja, Saco, Jumbo
            $table->decimal('cantidad_producida', 10, 2);
            $table->decimal('total_kg', 12, 2);
            $table->decimal('total_millares', 12, 2);
            $table->decimal('scrap', 10, 2)->default(0);
            $table->decimal('porcentaje_scrap', 5, 2)->default(0);
            $table->decimal('ciclo', 8, 2)->nullable();
            $table->integer('cavidades')->default(0);
            $table->integer('cavidades_trabajadas')->default(0);
            $table->string('motivo_scrap')->nullable(); // Crudo | Puntos negros | Arranque de maquina
            
            // Máquina Parada
            $table->boolean('maquina_parada')->default(false);
            $table->time('hora_inicio_parada')->nullable();
            $table->time('hora_fin_parada')->nullable();
            $table->decimal('tiempo_maquina_parada_min', 8, 2)->default(0); // en minutos
            $table->decimal('horas_operativas', 5, 2)->default(12.00); // 12 horas - parada
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produccions');
    }
};
