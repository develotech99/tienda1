<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoTipo;
use App\Models\StockMovimiento;
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

    // Obtener estadísticas generales
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

    // Obtener productos clasificados por niveles de stock
    public function obtenerStockClasificado(Request $request)
    {
        try {
            $query = Producto::with('tipo')
                ->where('prod_situacion', 'Activo');

            if ($request->has('categoria') && $request->categoria != '') {
                $query->where('tprod_id', $request->categoria);
            }

            $productos = $query->orderBy('prod_stock_actual', 'asc')
                ->get();

            // Clasificar productos por niveles de stock
            $clasificados = [
                'criticos' => [], // Stock = 0
                'bajos' => [],    // Stock > 0 y <= mínimo
                'medios' => [],   // Stock > mínimo y <= 2*mínimo  
                'altos' => []     // Stock > 2*mínimo
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

    // Obtener movimientos recientes
    public function obtenerMovimientos(Request $request)
    {
        try {
            $query = StockMovimiento::with('producto.tipo')
                ->orderBy('created_at', 'desc')
                ->limit(50);

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

    // Obtener productos más vendidos
    public function obtenerMasVendidos(Request $request)
    {
        try {
            $query = StockMovimiento::with('producto.tipo')
                ->select('prod_id', DB::raw('SUM(mov_cantidad) as total_vendido'))
                ->where('mov_tipo', 'Salida')
                ->groupBy('prod_id')
                ->orderBy('total_vendido', 'desc')
                ->limit(20);

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

            $masVendidos = $query->get();

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

    // Obtener productos sin stock
    public function obtenerSinStock(Request $request)
    {
        try {
            $query = Producto::with('tipo')
                ->where('prod_situacion', 'Activo')
                ->where('prod_stock_actual', 0);

            if ($request->has('categoria') && $request->categoria != '') {
                $query->where('tprod_id', $request->categoria);
            }

            $productos = $query->orderBy('prod_nombre')
                ->get();

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

    // Nuevo método para historial de producto
    public function historialProducto(Request $request)
    {
        try {
            $request->validate([
                'prod_id' => 'required|exists:productos,prod_id'
            ]);

            // Ventana por defecto: últimos 30 días (evita saturar el modal)
            $desde = $request->filled('fecha_desde')
                ? Carbon::parse($request->fecha_desde)->startOfDay()
                : Carbon::now()->subDays(30)->startOfDay();

            $hasta = $request->filled('fecha_hasta')
                ? Carbon::parse($request->fecha_hasta)->endOfDay()
                : Carbon::now()->endOfDay();

            $movimientos = StockMovimiento::with('producto.tipo')
                ->where('prod_id', $request->prod_id)
                ->whereBetween('created_at', [$desde, $hasta])
                ->orderBy('created_at', 'desc')
                ->get(); // ya no hace falta limit(100) si acotas por fecha

            $producto = Producto::with('tipo')->find($request->prod_id);

            return response()->json([
                'success' => true,
                'movimientos' => $movimientos,
                'producto' => $producto,
                'total' => $movimientos->count(),
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


    // Método para buscar producto por código (para scanner)
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

    // Método para exportar stock a PDF
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
                    'filtros'   => $request->all(),
                    'fecha'     => Carbon::now()
                ]);

                return $pdf->download('reporte-stock-' . Carbon::now()->format('Y-m-d') . '.pdf');
            } else {
                return response()->json([
                    'success'   => true,
                    'productos' => $productos,
                    'formato'   => $formato
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
