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
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'molde_id')) {
                $table->foreignId('molde_id')
                    ->nullable()
                    ->after('peso_unitario')
                    ->constrained('molde')
                    ->nullOnDelete();
            }
        });

        Schema::table('parametros_preforma', function (Blueprint $table) {
            if (!Schema::hasColumn('parametros_preforma', 'molde_id')) {
                $table->foreignId('molde_id')
                    ->nullable()
                    ->after('producto_id')
                    ->constrained('molde')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parametros_preforma', function (Blueprint $table) {
            if (Schema::hasColumn('parametros_preforma', 'molde_id')) {
                $table->dropForeign(['molde_id']);
                $table->dropColumn('molde_id');
            }
        });

        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'molde_id')) {
                $table->dropForeign(['molde_id']);
                $table->dropColumn('molde_id');
            }
        });
    }
};
