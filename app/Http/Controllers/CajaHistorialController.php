<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\caja_movimientos;
use App\Models\Caja;
use App\Models\Ventas;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class CajaHistorialController extends Controller
{
    public function index()
    {
        return view("ventas.CajaReportes");
    }

    public function obtenerDatos(Request $request)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', now()->subDays(7)->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
            $tipo = $request->get('tipo', 'todos');

            // Obtener movimientos con relaciones
            $query = caja_movimientos::with(['usuario', 'venta'])
                ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin]);

            if ($tipo !== 'todos') {
                $query->where('cajamov_tipo', $tipo);
            }

            $movimientos = $query->orderBy('created_at', 'desc')->get();

            // Calcular resumen
            $cajaActual = Caja::where('caja_estado', 'Abierta')->first();
            $saldoActual = $cajaActual ? $cajaActual->caja_saldo : 0;

            // Ventas de hoy
            $ventasHoy = Ventas::where('ven_estado', 'Activa')
                ->whereDate('ven_fecha', now()->format('Y-m-d'))
                ->sum('ven_total');

            // Ingresos de hoy
            $ingresosHoy = caja_movimientos::where('cajamov_tipo', 'Ingreso')
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->sum('cajamov_monto');

            // Egresos de hoy
            $egresosHoy = caja_movimientos::where('cajamov_tipo', 'Egreso')
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->sum('cajamov_monto');

            // Obtener saldos por día
            $saldosDia = $this->obtenerSaldosPorDia($fechaInicio, $fechaFin);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Datos obtenidos correctamente',
                'data' => $movimientos,
                'saldos_dia' => $saldosDia,
                'resumen' => [
                    'saldo_actual' => $saldoActual,
                    'ventas_hoy' => $ventasHoy,
                    'ingresos_hoy' => $ingresosHoy,
                    'egresos_hoy' => $egresosHoy
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerSaldosPorDia($fechaInicio, $fechaFin)
    {
        // Obtener ventas por día
        $ventasPorDia = Ventas::where('ven_estado', 'Activa')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->select(
                'ven_fecha as fecha',
                DB::raw('SUM(ven_total) as total_ventas'),
                DB::raw('COUNT(*) as cantidad_ventas')
            )
            ->groupBy('ven_fecha')
            ->get()
            ->keyBy('fecha');

        // Obtener movimientos por día
        $movimientosPorDia = caja_movimientos::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('SUM(CASE WHEN cajamov_tipo = "Ingreso" THEN cajamov_monto ELSE 0 END) as total_ingresos'),
                DB::raw('SUM(CASE WHEN cajamov_tipo = "Egreso" THEN cajamov_monto ELSE 0 END) as total_egresos'),
                DB::raw('SUM(CASE WHEN cajamov_tipo = "Venta" THEN cajamov_monto ELSE 0 END) as total_ventas_mov'),
                DB::raw('COUNT(*) as cantidad_movimientos')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('fecha');

        // Combinar datos
        $fechas = [];
        $fechaActual = new DateTime($fechaInicio);
        $fechaFinObj = new DateTime($fechaFin);

        while ($fechaActual <= $fechaFinObj) {
            $fechaStr = $fechaActual->format('Y-m-d');
            $fechas[] = $fechaStr;
            $fechaActual->modify('+1 day');
        }

        $resultado = [];

        foreach ($fechas as $fecha) {
            $ventaDia = $ventasPorDia->get($fecha);
            $movimientoDia = $movimientosPorDia->get($fecha);

            $totalVentas = $ventaDia ? floatval($ventaDia->total_ventas) : 0;
            $totalIngresos = $movimientoDia ? floatval($movimientoDia->total_ingresos) : 0;
            $totalEgresos = $movimientoDia ? floatval($movimientoDia->total_egresos) : 0;
            $cantidadMovimientos = $movimientoDia ? $movimientoDia->cantidad_movimientos : 0;
            $saldoDia = ($totalVentas + $totalIngresos) - $totalEgresos;

            if ($totalVentas > 0 || $totalIngresos > 0 || $totalEgresos > 0) {
                $resultado[] = [
                    'fecha' => $fecha,
                    'total_ventas' => $totalVentas,
                    'total_ingresos' => $totalIngresos,
                    'total_egresos' => $totalEgresos,
                    'saldo_dia' => $saldoDia,
                    'cantidad_movimientos' => $cantidadMovimientos
                ];
            }
        }

        return $resultado;
    }

    public function registrarIngreso(Request $request)
    {
        try {
            DB::beginTransaction();

            $caja = Caja::where('caja_estado', 'Abierta')->first();
            if (!$caja) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'No hay caja abierta'
                ], 400);
            }

            $movimiento = new caja_movimientos();
            $movimiento->caja_id = $caja->caja_id;
            $movimiento->user_id = auth()->id();
            $movimiento->cajamov_tipo = 'Ingreso';
            $movimiento->cajamov_monto = $request->monto;
            $movimiento->cajamov_desc = $request->descripcion;
            $movimiento->cajamov_saldo_final = $caja->caja_saldo + $request->monto;
            $movimiento->save();


            $caja->caja_saldo += $request->monto;
            $caja->save();

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Ingreso registrado correctamente'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al registrar el ingreso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarEgreso(Request $request)
    {
        try {
            DB::beginTransaction();

            $caja = Caja::where('caja_estado', 'Abierta')->first();
            if (!$caja) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'No hay caja abierta'
                ], 400);
            }

            if ($caja->caja_saldo < $request->monto) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Saldo insuficiente en caja'
                ], 400);
            }


            $movimiento = new caja_movimientos();
            $movimiento->caja_id = $caja->caja_id;
            $movimiento->user_id = auth()->id();
            $movimiento->cajamov_tipo = 'Egreso';
            $movimiento->cajamov_monto = $request->monto;
            $movimiento->cajamov_desc = $request->descripcion;
            $movimiento->cajamov_saldo_final = $caja->caja_saldo - $request->monto;
            $movimiento->save();

            $caja->caja_saldo -= $request->monto;
            $caja->save();

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Egreso registrado correctamente'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al registrar el egreso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarMovimiento(Request $request)
    {
        try {
            DB::beginTransaction();

            $movimiento = caja_movimientos::find($request->movimiento_id);
            if (!$movimiento) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Movimiento no encontrado'
                ], 404);
            }

            // No permitir eliminar movimientos de ventas
            if ($movimiento->cajamov_tipo === 'Venta') {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'No se puede eliminar movimientos de ventas'
                ], 400);
            }

            $caja = $movimiento->caja;

            // Revertir saldo
            if ($movimiento->cajamov_tipo === 'Ingreso') {
                $caja->caja_saldo -= $movimiento->cajamov_monto;
            } else {
                $caja->caja_saldo += $movimiento->cajamov_monto;
            }

            $caja->save();
            $movimiento->delete();

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Movimiento eliminado correctamente'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al eliminar el movimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerDetalleVenta($id)
    {
        try {
            $venta = Ventas::with(['detalles.producto', 'usuario'])
                ->where('ven_id', $id)
                ->first();

            if (!$venta) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Venta no encontrada'
                ], 404);
            }

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Detalle de venta obtenido correctamente',
                'data' => $venta
            ]);
        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener el detalle de la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
