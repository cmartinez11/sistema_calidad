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
        // Elimina de forma segura la restricción CHECK física de PostgreSQL que bloquea los nuevos estados
        DB::statement('ALTER TABLE maquinas DROP CONSTRAINT IF EXISTS maquinas_estado_check;');
        
        // Asegura que la columna estado sea un VARCHAR flexible
        DB::statement('ALTER TABLE maquinas ALTER COLUMN estado TYPE VARCHAR(50);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
