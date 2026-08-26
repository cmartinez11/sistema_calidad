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
        Schema::table('inspecciones_calidad', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->change();
            $table->foreignId('maquina_id')->nullable()->change();
            $table->foreignId('turno_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspecciones_calidad', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable(false)->change();
            $table->foreignId('maquina_id')->nullable(false)->change();
            $table->foreignId('turno_id')->nullable(false)->change();
        });
    }
};
