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
            // Drop old preform-specific columns if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('productos', 'presentacion')) $columnsToDrop[] = 'presentacion';
            if (Schema::hasColumn('productos', 'millares_presentacion')) $columnsToDrop[] = 'millares_presentacion';
            if (Schema::hasColumn('productos', 'gramaje')) $columnsToDrop[] = 'gramaje';
            if (Schema::hasColumn('productos', 'unidad_peso')) $columnsToDrop[] = 'unidad_peso';
            if (Schema::hasColumn('productos', 'unidad_dimension')) $columnsToDrop[] = 'unidad_dimension';
            if (Schema::hasColumn('productos', 'unidad_produccion')) $columnsToDrop[] = 'unidad_produccion';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            // Add new generic fields
            if (!Schema::hasColumn('productos', 'tipo_producto')) {
                $table->string('tipo_producto', 30)->default('PREFORMA')->after('nombre');
            }
            if (!Schema::hasColumn('productos', 'unidad_medida')) {
                $table->string('unidad_medida', 30)->default('UNIDADES')->after('tipo_producto');
            }
            if (!Schema::hasColumn('productos', 'peso_unitario')) {
                $table->decimal('peso_unitario', 10, 4)->nullable()->after('unidad_medida');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['tipo_producto', 'unidad_medida', 'peso_unitario']);

            $table->string('presentacion', 50)->default('Caja');
            $table->decimal('millares_presentacion', 8, 4)->default(1.0000);
            $table->decimal('gramaje', 8, 2)->nullable();
            $table->string('unidad_peso', 20)->default('GRAMOS');
            $table->string('unidad_dimension', 20)->default('MILIMETROS');
            $table->string('unidad_produccion', 20)->default('UNIDADES');
        });
    }
};
