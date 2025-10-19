<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\caja_movimientos;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index()
    {
        $cajas = Caja::withCount(['ventas', 'movimientos'])->get();
        return response()->json($cajas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'caja_nombre' => 'required|string|max:100',
            'caja_saldo' => 'nullable|numeric|min:0'
        ]);

        $caja = Caja::create($request->all());
        return response()->json($caja, 201);
    }

    public function show($id)
    {
        $caja = Caja::with(['ventas', 'movimientos.usuario'])->findOrFail($id);
        return response()->json($caja);
    }

    public function apertura(Request $request, $id)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0'
        ]);

        $caja = Caja::findOrFail($id);

        DB::transaction(function () use ($caja, $request) {
            $caja->update([
                'caja_estado' => 'Abierta',
                'caja_saldo' => $request->monto_apertura
            ]);

            caja_movimientos::create([
                'caja_id' => $caja->caja_id,
                'user_id' => auth()->id(),
                'cajamov_tipo' => 'Apertura',
                'cajamov_monto' => $request->monto_apertura,
                'cajamov_saldo_final' => $request->monto_apertura,
                'cajamov_desc' => 'Apertura de caja'
            ]);
        });

        return response()->json(['message' => 'Caja abierta exitosamente']);
    }

    public function cierre(Request $request, $id)
    {
        $caja = Caja::with(['ventas'])->findOrFail($id);

        DB::transaction(function () use ($caja) {
            $saldo_final = $caja->caja_saldo;

            caja_movimientos::create([
                'caja_id' => $caja->caja_id,
                'user_id' => auth()->id(),
                'cajamov_tipo' => 'Cierre',
                'cajamov_monto' => $saldo_final,
                'cajamov_saldo_final' => $saldo_final,
                'cajamov_desc' => 'Cierre de caja'
            ]);

            $caja->update([
                'caja_estado' => 'Cerrada'
            ]);
        });

        return response()->json(['message' => 'Caja cerrada exitosamente']);
    }
}