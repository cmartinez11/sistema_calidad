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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote', 20)->unique(); 
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('maquina_id')->constrained('maquinas');
            $table->string('resina', 100)->nullable();
            $table->date('fecha_produccion');
            $table->enum('estado_lote', ['en_proceso', 'liberado', 'observado_pnc'])->default('en_proceso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
