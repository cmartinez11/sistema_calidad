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
            $table->foreignId('operario_id')->nullable()->constrained('operarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspecciones_calidad', function (Blueprint $table) {
            $table->dropForeign(['operario_id']);
            $table->dropColumn('operario_id');
        });
    }
};
