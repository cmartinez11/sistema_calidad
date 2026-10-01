<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanTransactionsSeeder extends Seeder
{
    /**
     * Tablas transaccionales que SÍ se van a vaciar.
     */
    protected array $transactionalTables = [
        'atcs',
        'pnc',
        'inspecciones_cavidades',
        'inspecciones_calidad',
        'alertas_calidad',
        'lotes',
        'activity_logs',
        'audit_logs',
    ];

    /**
     * Ejecuta el seeder de limpieza transaccional.
     */
    public function run(): void
    {
        $driver = DB::getDriverName();
        $this->command->info("Motor de base de datos detectado: [{$driver}]");

        DB::beginTransaction();

        try {
            // Desactivar llaves foráneas
            $this->disableForeignKeyChecks($driver);

            foreach ($this->transactionalTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->truncateOrDeleteTable($driver, $table);
                    $this->command->info("  ✅ Tabla '{$table}' vaciada.");
                }
            }

            // Reactivar llaves foráneas
            $this->enableForeignKeyChecks($driver);

            DB::commit();

            $this->command->info("✨ Limpieza completada con éxito. Catálogos y usuarios conservados.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->enableForeignKeyChecks($driver);
            $this->command->error("❌ Error durante la limpieza: " . $e->getMessage());
        }
    }

    protected function disableForeignKeyChecks(string $driver): void
    {
        switch ($driver) {
            case 'pgsql':
                DB::statement("SET session_replication_role = 'replica';");
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = OFF;');
                break;
            case 'mysql':
            case 'mariadb':
            default:
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
                break;
        }
    }

    protected function enableForeignKeyChecks(string $driver): void
    {
        switch ($driver) {
            case 'pgsql':
                DB::statement("SET session_replication_role = 'origin';");
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = ON;');
                break;
            case 'mysql':
            case 'mariadb':
            default:
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
                break;
        }
    }

    protected function truncateOrDeleteTable(string $driver, string $table): void
    {
        switch ($driver) {
            case 'pgsql':
                DB::statement("TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE;");
                break;
            case 'sqlite':
                DB::table($table)->delete();
                DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}';");
                break;
            case 'mysql':
            case 'mariadb':
            default:
                DB::statement("TRUNCATE TABLE `{$table}`;");
                break;
        }
    }
}
