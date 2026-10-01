<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maquina;
use App\Models\Molde;
use App\Models\Resina;
use App\Models\Operario;

class CatalogoController extends Controller
{
    public function index()
    {
        $maquinas = Maquina::orderBy('codigo', 'asc')->get();
        $moldes = Molde::orderBy('codigo', 'asc')->get();
        $resinas = Resina::orderBy('codigo', 'asc')->get();
        $operarios = Operario::orderBy('nombre', 'asc')->get();

        return view('catalogos.index', compact('maquinas', 'moldes', 'resinas', 'operarios'));
    }
}
