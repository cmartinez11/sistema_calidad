<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('pnc')
            ->where('estado_pnc', 'EMITIDO')
            ->update(['estado_pnc' => 'PENDIENTE']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pnc')
            ->where('estado_pnc', 'PENDIENTE')
            ->update(['estado_pnc' => 'EMITIDO']);
    }
};
