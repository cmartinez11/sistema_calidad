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
            $table->string('unidad_peso', 20)->default('GRAMOS')->after('nombre');
            $table->string('unidad_dimension', 20)->default('MILIMETROS')->after('unidad_peso');
            $table->string('unidad_produccion', 20)->default('UNIDADES')->after('unidad_dimension');
            $table->decimal('factor_conversion_kg', 8, 4)->nullable()->after('unidad_produccion');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->integer('cantidad_producida_unidades')->nullable()->after('estado_lote');
            $table->decimal('peso_total_kg', 10, 2)->nullable()->after('cantidad_producida_unidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'unidad_peso',
                'unidad_dimension',
                'unidad_produccion',
                'factor_conversion_kg',
            ]);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_producida_unidades',
                'peso_total_kg',
            ]);
        });
    }
};
