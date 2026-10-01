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
        Schema::create('matriz_empaques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('gramaje', 8, 2)->nullable();
            $table->string('presentacion', 20); // 'CAJA', 'SACO'
            $table->decimal('factor_millares', 10, 4)->default(0); // ej: 1.55 millares por caja
            $table->decimal('factor_peso_kg', 10, 4)->default(0); // ej: 19.22 kg por caja
            $table->integer('cant_unidades')->default(0); // ej: 1550 unidades
            $table->timestamps();

            $table->unique(['producto_id', 'presentacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriz_empaques');
    }
};
