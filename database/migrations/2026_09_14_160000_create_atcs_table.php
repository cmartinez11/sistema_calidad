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
        Schema::create('atcs', function (Blueprint $table) {
            $table->id();
            $table->string('correlativo')->unique(); // Ej: ATC2026-001
            $table->date('fecha');
            $table->string('cliente_nombre');
            $table->string('cliente_ruc')->nullable();
            $table->enum('tipo', ['Reclamo', 'Devolucion']);
            $table->string('producto');
            $table->decimal('cantidad', 12, 2);
            $table->text('descripcion');
            $table->string('lote')->nullable();
            $table->text('accion_correctiva')->nullable();
            $table->text('plan_accion')->nullable();
            $table->foreignId('pnc_id')->nullable()->constrained('pnc')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atcs');
    }
};
