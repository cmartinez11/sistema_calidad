<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Muestra el historial global de logs de auditoría del sistema.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $action = $request->get('action');
        $modelType = $request->get('model_type');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $query = AuditLog::with('user')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('user', fn($userQuery) => $userQuery->where('name', 'ILIKE', "%{$search}%")->orWhere('username', 'ILIKE', "%{$search}%"))
                        ->orWhere('model_type', 'ILIKE', "%{$search}%")
                        ->orWhere('action', 'ILIKE', "%{$search}%")
                        ->orWhere('ip_address', 'ILIKE', "%{$search}%");
                });
            })
            ->when($action, function ($q, $action) {
                $q->where('action', $action);
            })
            ->when($modelType, function ($q, $modelType) {
                $q->where('model_type', $modelType);
            })
            ->when($fechaInicio, function ($q, $fechaInicio) {
                $q->whereDate('created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($q, $fechaFin) {
                $q->whereDate('created_at', '<=', $fechaFin);
            });

        $auditLogs = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Obtener la lista de modelos registrados para el filtro desplegable
        $modelTypes = AuditLog::select('model_type')
            ->distinct()
            ->orderBy('model_type', 'asc')
            ->pluck('model_type');

        return view('audit_logs.index', compact(
            'auditLogs',
            'search',
            'action',
            'modelType',
            'fechaInicio',
            'fechaFin',
            'modelTypes'
        ));
    }
}
