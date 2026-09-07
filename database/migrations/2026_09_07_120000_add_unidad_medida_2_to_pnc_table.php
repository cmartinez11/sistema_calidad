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
        Schema::table('pnc', function (Blueprint $table) {
            $table->string('unidad_medida_2', 50)->nullable()->after('unidad_medida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnc', function (Blueprint $table) {
            $table->dropColumn('unidad_medida_2');
        });
    }
};
