<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoTipo;
use App\Models\StockMovimiento;
use App\Models\VentasDetalle;
use App\Models\Ventas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    public function index()
    {
        $tipos = ProductoTipo::where('tprod_situacion', '1')
            ->orderBy('tprod_nombre')
            ->get();

        return view('productos.reportes', compact('tipos'));
    }

    public function obtenerEstadisticas()
    {
        try {
            $totalProductos = Producto::where('prod_situacion', 'Activo')->count();

            $stockBajo = Producto::where('prod_situacion', 'Activo')
                ->where('prod_stock_actual', '>', 0)
                ->whereRaw('prod_stock_actual <= prod_stock_minimo')
                ->count();

            $sinStock = Producto::where('prod_situacion', 'Activo')
                ->where('prod_stock_actual', 0)
                ->count();

            $movimientosHoy = StockMovimiento::whereDate('created_at', Carbon::today())
                ->count();

            return response()->json([
                'success' => true,
                'estadisticas' => [
                    'total_productos' => $totalProductos,
                    'stock_bajo' => $stockBajo,
                    'sin_stock' => $sinStock,
                    'movimientos_hoy' => $movimientosHoy
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerStockClasificado(Request $request)
    {
        try {
            $query = Producto::with('tipo')
                ->where('prod_situacion', 'Activo');

            if ($request->has('categoria') && $request->categoria != '') {
                $query->where('tprod_id', $request->categoria);
            }

            $productos = $query->orderBy('prod_stock_actual', 'asc')->get();

            $clasificados = [
                'criticos' => [],
                'bajos' => [],
                'medios' => [],
                'altos' => []
            ];

            foreach ($productos as $producto) {
                if ($producto->prod_stock_actual == 0) {
                    $clasificados['criticos'][] = $producto;
                } elseif ($producto->prod_stock_actual > 0 && $producto->prod_stock_actual <= $producto->prod_stock_minimo) {
                    $clasificados['bajos'][] = $producto;
                } elseif ($producto->prod_stock_actual > $producto->prod_stock_minimo && $producto->prod_stock_actual <= ($producto->prod_stock_minimo * 2)) {
                    $clasificados['medios'][] = $producto;
                } else {
                    $clasificados['altos'][] = $producto;
                }
            }

            return response()->json([
                'success' => true,
                'productos' => $clasificados,
                'total_criticos' => count($clasificados['criticos']),
                'total_bajos' => count($clasificados['bajos']),
                'total_medios' => count($clasificados['medios']),
                'total_altos' => count($clasificados['altos']),
                'total_general' => $productos->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener stock clasificado: ' . $e->getMessage()
            ], 500);
        }
    }


    public function obtenerMovimientos(Request $request)
    {
        try {
            $query = StockMovimiento::with('producto.tipo')
                ->orderBy('created_at', 'desc')
                ->limit(100);

            if ($request->has('fecha_desde') && $request->fecha_desde != '') {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta') && $request->fecha_hasta != '') {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            if ($request->has('categoria') && $request->categoria != '') {
                $query->whereHas('producto', function ($q) use ($request) {
                    $q->where('tprod_id', $request->categoria);
                });
            }

            $movimientos = $query->get();

            return response()->json([
                'success' => true,
                'movimientos' => $movimientos,
                'total' => $movimientos->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener movimientos: ' . $e->getMessage()
            ], 500);
        }
    }


    public function obtenerMasVendidos(Request $request)
    {
        try {
            $query = VentasDetalle::join('ventas', 'venta_detalles.ven_id', '=', 'ventas.ven_id')
                ->join('productos', 'venta_detalles.prod_id', '=', 'productos.prod_id')
                ->where('ventas.ven_estado', 'Activa')
                ->select(
                    'productos.prod_id',
                    DB::raw('SUM(venta_detalles.vendet_cantidad) as total_vendido'),
                    DB::raw('SUM(venta_detalles.vendet_total) as total_ingresos'),
                    DB::raw('COUNT(DISTINCT ventas.ven_id) as num_ventas')
                )
                ->groupBy('productos.prod_id');

            if ($request->has('fecha_desde') && $request->fecha_desde != '') {
                $query->whereDate('ventas.ven_fecha', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta') && $request->fecha_hasta != '') {
                $query->whereDate('ventas.ven_fecha', '<=', $request->fecha_hasta);
            }

            if ($request->has('categoria') && $request->categoria != '') {
                $query->where('productos.tprod_id', $request->categoria);
            }

            $masVendidos = $query->orderByDesc('total_vendido')
                ->limit(20)
                ->get();

            $masVendidos->each(function ($item) {
                $item->producto = Producto::with('tipo')->find($item->prod_id);
            });

            return response()->json([
                'success' => true,
                'mas_vendidos' => $masVendidos,
                'total' => $masVendidos->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos más vendidos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerSinStock(Request $request)
    {
        try {
            $query = Producto::with('tipo')
                ->where('prod_situacion', 'Activo')
                ->where('prod_stock_actual', 0);

            if ($request->has('categoria') && $request->categoria != '') {
                $query->where('tprod_id', $request->categoria);
            }

            $productos = $query->orderBy('prod_nombre')->get();

            return response()->json([
                'success' => true,
                'productos' => $productos,
                'total' => $productos->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos sin stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function historialProducto(Request $request)
    {
        try {
            $request->validate([
                'prod_id' => 'required|exists:productos,prod_id'
            ]);

            $desde = $request->filled('fecha_desde')
                ? Carbon::parse($request->fecha_desde)->startOfDay()
                : Carbon::now()->subDays(30)->startOfDay();

            $hasta = $request->filled('fecha_hasta')
                ? Carbon::parse($request->fecha_hasta)->endOfDay()
                : Carbon::now()->endOfDay();

            // 1. Movimientos de inventario (Entradas/Salidas manuales)
            $movimientos = StockMovimiento::where('prod_id', $request->prod_id)
                ->whereBetween('created_at', [$desde, $hasta])
                ->orderBy('created_at', 'desc')
                ->get();

            // 2. Ventas del producto
            $ventas = VentasDetalle::with(['venta.usuario', 'venta.caja'])
                ->whereHas('venta', function($q) use ($desde, $hasta) {
                    $q->where('ven_estado', 'Activa')
                      ->whereBetween('ven_fecha', [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]);
                })
                ->where('prod_id', $request->prod_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // 3. Información del producto
            $producto = Producto::with('tipo')->find($request->prod_id);

            // 4. Calcular estadísticas
            $estadisticas = [
                'total_vendido' => $ventas->sum('vendet_cantidad'),
                'ingresos_generados' => $ventas->sum('vendet_total'),
                'num_ventas' => $ventas->count(),
                'entradas_inventario' => $movimientos->where('mov_tipo', 'Entrada')->sum('mov_cantidad'),
                'salidas_inventario' => $movimientos->where('mov_tipo', 'Salida')->sum('mov_cantidad'),
            ];

            return response()->json([
                'success' => true,
                'producto' => $producto,
                'movimientos' => $movimientos,
                'ventas' => $ventas,
                'estadisticas' => $estadisticas,
                'total_movimientos' => $movimientos->count(),
                'total_ventas' => $ventas->count(),
                'rango' => [
                    'desde' => $desde->toDateString(),
                    'hasta' => $hasta->toDateString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarProductoCodigo(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string'
            ]);

            $producto = Producto::with('tipo')
                ->where('prod_codigo', $request->codigo)
                ->where('prod_situacion', 'Activo')
                ->first();

            if ($producto) {
                return response()->json([
                    'success' => true,
                    'encontrado' => true,
                    'producto' => $producto
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'encontrado' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportarStock(Request $request)
    {
        try {
            $query = Producto::with('tipo')
                ->where('prod_situacion', 'Activo')
                ->orderByRaw("
                    CASE
                        WHEN prod_stock_actual = 0 THEN 1
                        WHEN prod_stock_actual > 0 AND prod_stock_actual <= prod_stock_minimo THEN 2
                        WHEN prod_stock_actual > prod_stock_minimo AND prod_stock_actual <= prod_stock_minimo * 2 THEN 3
                        ELSE 4
                    END
                ")
                ->orderBy('prod_stock_actual', 'asc')
                ->orderBy('prod_nombre', 'asc');

            if ($request->has('categoria') && $request->categoria != '') {
                $query->where('tprod_id', $request->categoria);
            }

            $productos = $query->get();

            $formato = $request->formato ?? 'pdf';

            if ($formato === 'pdf') {
                $pdf = Pdf::loadView('productos.reportes.pdf.stock', [
                    'productos' => $productos,
                    'filtros' => $request->all(),
                    'fecha' => Carbon::now()
                ]);

                return $pdf->download('reporte-stock-' . Carbon::now()->format('Y-m-d') . '.pdf');
            } else {
                return response()->json([
                    'success' => true,
                    'productos' => $productos,
                    'formato' => $formato
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }
}