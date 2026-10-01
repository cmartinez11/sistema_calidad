<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimpiarTransaccionesCalidad extends Command
{
    /**
     * El nombre y firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'calidad:limpiar-transacciones {--force : Forzar la limpieza sin solicitar confirmación en pantalla}';

    /**
     * Descripción del comando Artisan.
     *
     * @var string
     */
    protected $description = 'Limpia selectivamente todas las tablas transaccionales (inspecciones, PNC, ATC, lotes, logs) respetando usuarios y catálogos maestros.';

    /**
     * Tablas transaccionales que SÍ se van a vaciar.
     *
     * @var array
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
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $this->info("==========================================================");
        $this->info("  GRUPO FÉNIX - LIMPIEZA SELECTIVA DE DATOS TRANSACCIONALES");
        $this->info("==========================================================");

        if (!$this->option('force')) {
            $confirm = $this->confirm(
                '⚠️ ATENCIÓN: Este comando eliminará TODAS las inspecciones, PNC, ATC, lotes y registros de auditoría.\n' .
                'Los catálogos (productos, máquinas, moldes, resinas, operarios) y usuarios NO serán tocados.\n' .
                '¿Deseas continuar con el vaciado?',
                false
            );

            if (!$confirm) {
                $this->warn('Operación cancelada por el usuario. No se modificó ningún dato.');
                return Command::SUCCESS;
            }
        }

        $driver = DB::getDriverName();
        $this->comment("Motor de base de datos detectado: [{$driver}]");

        DB::beginTransaction();

        try {
            // 1. Desactivar restricciones de llaves foráneas según motor DB
            $this->disableForeignKeyChecks($driver);

            $cleanedCount = 0;

            foreach ($this->transactionalTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->truncateOrDeleteTable($driver, $table);
                    $this->line("  ✅ Tabla <fg=green>{$table}</fg=green> vaciada y autoincremento reiniciado a 1.");
                    $cleanedCount++;
                } else {
                    $this->line("  ℹ️ Tabla <fg=yellow>{$table}</fg=yellow> no existe en esta BD (omitida).");
                }
            }

            // 2. Reactivar restricciones de llaves foráneas según motor DB
            $this->enableForeignKeyChecks($driver);

            DB::commit();

            $this->newLine();
            $this->info("✨ ¡Limpieza completada con éxito! Se procesaron {$cleanedCount} tablas transaccionales.");
            $this->info("🔒 Se mantuvieron intactos los usuarios y catálogos (productos, máquinas, moldes, resinas, operarios).");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();

            // Asegurar la reactivación de llaves foráneas en caso de fallo
            try {
                $this->enableForeignKeyChecks($driver);
            } catch (\Throwable $ex) {
                // Ignorar excepción secundaria
            }

            $this->error("❌ Error al vaciar las tablas: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Desactiva temporalmente la comprobación de claves foráneas.
     */
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

    /**
     * Reactiva la comprobación de claves foráneas.
     */
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

    /**
     * Vacía una tabla y reinicia sus secuencias / autoincrementos.
     */
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
