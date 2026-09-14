<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SifClienteController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::timeout(5)->get(config('services.sif.url') . '/clientes');
        $clientes = $response->successful() ? ($response->json()['data'] ?? []) : [];

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
