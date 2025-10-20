<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ventas;
use App\Models\caja_movimientos;
use App\Models\Producto;
use App\Models\VentasDetalle;
use App\Models\ProductoTipo;
use Exception;
use Illuminate\Support\Facades\DB;

class VentasGraficasController extends Controller
{
    public function index()
    {
        return view("ventas.reportes-graficas");
    }

    public function obtenerDatosGraficas(Request $request)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
            $tipoGrafica = $request->get('tipo_grafica', 'ventas');

            $datos = [];

            switch ($tipoGrafica) {
                case 'ventas':
                    $datos = $this->obtenerDatosVentas($fechaInicio, $fechaFin);
                    break;
                case 'productos':
                    $datos = $this->obtenerDatosProductos($fechaInicio, $fechaFin);
                    break;
                case 'caja':
                    $datos = $this->obtenerDatosCaja($fechaInicio, $fechaFin);
                    break;
                case 'comparativa':
                    $datos = $this->obtenerDatosComparativa($fechaInicio, $fechaFin);
                    break;
            }

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Datos obtenidos correctamente',
                'data' => $datos,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'tipo_grafica' => $tipoGrafica
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los datos para gráficas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerDatosVentas($fechaInicio, $fechaFin)
    {
        // Ventas por día
        $ventasPorDia = Ventas::where('ven_estado', 'Activa')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->select(
                'ven_fecha',
                DB::raw('SUM(ven_total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('ven_fecha')
            ->orderBy('ven_fecha')
            ->get();

        // Ventas por hora (promedio)
        $ventasPorHora = Ventas::where('ven_estado', 'Activa')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('HOUR(ven_hora) as hora'),
                DB::raw('AVG(ven_total) as promedio')
            )
            ->groupBy(DB::raw('HOUR(ven_hora)'))
            ->orderBy('hora')
            ->get();

        return [
            'ventas_por_dia' => $ventasPorDia,
            'ventas_por_hora' => $ventasPorHora
        ];
    }

    private function obtenerDatosProductos($fechaInicio, $fechaFin)
    {
        // Productos más vendidos
        $productosMasVendidos = VentasDetalle::join('ventas', 'venta_detalles.ven_id', '=', 'ventas.ven_id')
            ->join('productos', 'venta_detalles.prod_id', '=', 'productos.prod_id')
            ->where('ventas.ven_estado', 'Activa')
            ->whereBetween('ventas.ven_fecha', [$fechaInicio, $fechaFin])
            ->select(
                'productos.prod_nombre',
                'productos.prod_codigo',
                DB::raw('SUM(venta_detalles.vendet_cantidad) as total_vendido'),
                DB::raw('SUM(venta_detalles.vendet_total) as total_ingresos')
            )
            ->groupBy('productos.prod_id', 'productos.prod_nombre', 'productos.prod_codigo')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        // Ventas por categoría
        $ventasPorCategoria = VentasDetalle::join('ventas', 'venta_detalles.ven_id', '=', 'ventas.ven_id')
            ->join('productos', 'venta_detalles.prod_id', '=', 'productos.prod_id')
            ->join('producto_tipo', 'productos.tprod_id', '=', 'producto_tipo.tprod_id')
            ->where('ventas.ven_estado', 'Activa')
            ->whereBetween('ventas.ven_fecha', [$fechaInicio, $fechaFin])
            ->select(
                'producto_tipo.tprod_nombre as categoria',
                DB::raw('SUM(venta_detalles.vendet_total) as total')
            )
            ->groupBy('producto_tipo.tprod_id', 'producto_tipo.tprod_nombre')
            ->orderByDesc('total')
            ->get();

        return [
            'productos_mas_vendidos' => $productosMasVendidos,
            'ventas_por_categoria' => $ventasPorCategoria
        ];
    }

    private function obtenerDatosCaja($fechaInicio, $fechaFin)
    {
        // Movimientos de caja por día
        $movimientosCaja = caja_movimientos::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('SUM(CASE WHEN cajamov_tipo = "Ingreso" THEN cajamov_monto ELSE 0 END) as ingresos'),
                DB::raw('SUM(CASE WHEN cajamov_tipo = "Egreso" THEN cajamov_monto ELSE 0 END) as egresos'),
                DB::raw('SUM(CASE WHEN cajamov_tipo = "Venta" THEN cajamov_monto ELSE 0 END) as ventas')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        // Distribución de ingresos
        $distribucionIngresos = caja_movimientos::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])
            ->where('cajamov_tipo', 'Ingreso')
            ->select(
                'cajamov_desc',
                DB::raw('SUM(cajamov_monto) as total')
            )
            ->groupBy('cajamov_desc')
            ->orderByDesc('total')
            ->get();

        return [
            'movimientos_caja' => $movimientosCaja,
            'distribucion_ingresos' => $distribucionIngresos
        ];
    }

    private function obtenerDatosComparativa($fechaInicio, $fechaFin)
    {
        // Mes actual
        $mesActual = Ventas::where('ven_estado', 'Activa')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('SUM(ven_total) as total_ventas'),
                DB::raw('COUNT(*) as total_transacciones'),
                DB::raw('AVG(ven_total) as ticket_promedio')
            )
            ->first();

        // Mes anterior
        $fechaInicioAnterior = date('Y-m-d', strtotime($fechaInicio . ' -1 month'));
        $fechaFinAnterior = date('Y-m-d', strtotime($fechaFin . ' -1 month'));

        $mesAnterior = Ventas::where('ven_estado', 'Activa')
            ->whereBetween('ven_fecha', [$fechaInicioAnterior, $fechaFinAnterior])
            ->select(
                DB::raw('SUM(ven_total) as total_ventas'),
                DB::raw('COUNT(*) as total_transacciones'),
                DB::raw('AVG(ven_total) as ticket_promedio')
            )
            ->first();

        return [
            'mes_actual' => $mesActual,
            'mes_anterior' => $mesAnterior,
            'fechas_comparativa' => [
                'actual' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
                'anterior' => ['inicio' => $fechaInicioAnterior, 'fin' => $fechaFinAnterior]
            ]
        ];
    }
}