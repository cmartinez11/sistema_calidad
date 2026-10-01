<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SifClienteController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Obtenemos los clientes y los convertimos a un array plano para la vista
            $clientes = DB::connection('sif_db')
                ->table('clientes')
                ->select('id', 'nombre', 'ruc', 'direccion')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();

        } catch (\Exception $e) {
            $clientes = [];
        }

        $search = trim($request->input('search'));

        if (!empty($search)) {
            $clientes = array_filter($clientes, function ($cliente) use ($search) {
                $ruc = $cliente['ruc'] ?? '';
                $nombre = $cliente['nombre'] ?? '';
                
                return stripos($ruc, $search) !== false || stripos($nombre, $search) !== false;
            });
        }

        return view('clientes.sif-index', compact('clientes', 'search'));
    }
}