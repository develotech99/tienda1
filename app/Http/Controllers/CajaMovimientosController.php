<?php

namespace App\Http\Controllers;

use App\Models\caja_movimientos;
use Illuminate\Http\Request;

class CajaMovimientosController extends Controller
{
    public function index(Request $request)
    {
        $query = caja_movimientos::with(['caja', 'usuario', 'venta']);
        
        if ($request->has('caja_id')) {
            $query->where('caja_id', $request->caja_id);
        }
        
        if ($request->has('tipo')) {
            $query->where('cajamov_tipo', $request->tipo);
        }
        
        $movimientos = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($movimientos);
    }

    public function porCaja($cajaId)
    {
        $movimientos = caja_movimientos::with(['usuario', 'venta'])
            ->where('caja_id', $cajaId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($movimientos);
    }
}