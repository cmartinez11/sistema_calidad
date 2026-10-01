<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the trait to attach Eloquent event listeners.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::recordAuditLog($model, 'CREAR', null, $model->filterAuditAttributes($model->getAttributes()));
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            // Ignorar si solo cambió updated_at o no hubo cambios sustanciales
            unset($changes['updated_at']);
            if (empty($changes)) {
                return;
            }

            $original = array_intersect_key($model->getOriginal(), $changes);

            static::recordAuditLog(
                $model,
                'EDITAR',
                $model->filterAuditAttributes($original),
                $model->filterAuditAttributes($changes)
            );
        });

        static::deleted(function (Model $model) {
            static::recordAuditLog($model, 'ELIMINAR', $model->filterAuditAttributes($model->getAttributes()), null);
        });
    }

    /**
     * Registra la entrada en la tabla audit_logs.
     */
    protected static function recordAuditLog(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => class_basename($model),
                'model_id' => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Prevenir que un fallo puntual en el log de auditoría interrumpa la operación principal
            logger()->error('Error al registrar AuditLog: ' . $e->getMessage());
        }
    }

    /**
     * Filtra atributos sensibles u ocultos para la auditoría.
     */
    public function filterAuditAttributes(array $attributes): array
    {
        $hidden = array_merge($this->getHidden(), ['password', 'remember_token']);
        return array_diff_key($attributes, array_flip($hidden));
    }
}
