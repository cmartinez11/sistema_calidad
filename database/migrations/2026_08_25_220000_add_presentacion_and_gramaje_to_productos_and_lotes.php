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
            $table->string('presentacion', 50)->default('Caja')->after('nombre');
            $table->decimal('millares_presentacion', 8, 4)->default(1.0000)->after('presentacion');
            $table->decimal('gramaje', 8, 2)->nullable()->after('millares_presentacion');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->integer('cantidad_empaques')->nullable()->after('estado_lote');
            $table->decimal('total_millares', 10, 4)->nullable()->after('cantidad_empaques');
            $table->decimal('scrap_kg', 10, 2)->default(0.00)->after('peso_total_kg');
            $table->decimal('scrap_porcentaje', 5, 2)->default(0.00)->after('scrap_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'presentacion',
                'millares_presentacion',
                'gramaje',
            ]);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_empaques',
                'total_millares',
                'scrap_kg',
                'scrap_porcentaje',
            ]);
        });
    }
};
