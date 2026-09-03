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
            $table->foreignId('motivo_observacion_id')
                ->nullable()
                ->after('motivo_scrap')
                ->constrained('motivos_observacion')
                ->nullOnDelete();

            $table->text('motivo_observacion_texto')
                ->nullable()
                ->after('motivo_observacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspecciones_calidad', function (Blueprint $table) {
            $table->dropForeign(['motivo_observacion_id']);
            $table->dropColumn(['motivo_observacion_id', 'motivo_observacion_texto']);
        });
    }
};
