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
        Schema::table('inspecciones_cavidades', function (Blueprint $table) {
            $table->foreignId('inspeccion_id')->nullable()->change();
            $table->foreignId('producto_id')->nullable()->after('inspeccion_id')->constrained('productos')->onDelete('set null');
            $table->foreignId('maquina_id')->nullable()->after('producto_id')->constrained('maquinas')->onDelete('set null');
            $table->foreignId('operario_id')->nullable()->after('maquina_id')->constrained('operarios')->onDelete('set null');
            $table->foreignId('turno_id')->nullable()->after('operario_id')->constrained('turnos')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->after('turno_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspecciones_cavidades', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['turno_id']);
            $table->dropForeign(['operario_id']);
            $table->dropForeign(['maquina_id']);
            $table->dropForeign(['producto_id']);

            $table->dropColumn(['user_id', 'turno_id', 'operario_id', 'maquina_id', 'producto_id']);
            $table->foreignId('inspeccion_id')->nullable(false)->change();
        });
    }
};
