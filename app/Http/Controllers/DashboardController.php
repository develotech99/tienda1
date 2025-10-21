<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Ventas;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\ProductoTipo;
use App\Models\Caja;
use App\Models\Caja_movimientos;
use Exception;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            if (auth()->user()->esAdmin()) {
                return $this->dashboardAdmin();
            } else {
                return $this->dashboardVendedor();
            }
        } catch (Exception $e) {
            // Fallback para ambos roles
            if (auth()->user()->esAdmin()) {
                return view('dashboard.admin', [
                    'estadisticas' => $this->estadisticasVacias(),
                    'actividadReciente' => [],
                    'alertasStock' => [],
                    'ventasSemana' => []
                ]);
            } else {
                return view('dashboard.vendedor', [
                    'ventasHoy' => ['total' => 0, 'transacciones' => 0]
                ]);
            }
        }
    }

    private function dashboardAdmin()
    {
        $estadisticas = $this->obtenerEstadisticas();
        $actividadReciente = $this->obtenerActividadReciente();
        $alertasStock = $this->obtenerAlertasStock();
        $ventasSemana = $this->obtenerVentasSemana();

        return view('dashboard.admin', compact(
            'estadisticas',
            'actividadReciente',
            'alertasStock',
            'ventasSemana'
        ));
    }

    private function dashboardVendedor()
    {
        $ventasHoy = $this->obtenerVentasHoy();
        return view('dashboard.vendedor', compact('ventasHoy'));
    }

    private function obtenerVentasHoy()
    {
        $hoy = Carbon::today();
        
        $totalVentas = Ventas::whereDate('ven_fecha', $hoy)
            ->where('ven_estado', 'Activa')
            ->sum('ven_total') ?? 0;

        $transacciones = Ventas::whereDate('ven_fecha', $hoy)
            ->where('ven_estado', 'Activa')
            ->count();

        return [
            'total' => $totalVentas,
            'transacciones' => $transacciones
        ];
    }

    private function obtenerEstadisticas()
    {
        $hoy = Carbon::today();

        return [
            // Ventas de hoy (solo activas)
            'ventas_hoy' => Ventas::whereDate('ven_fecha', $hoy)
                ->where('ven_estado', 'Activa')
                ->sum('ven_total') ?? 0,

            'transacciones_hoy' => Ventas::whereDate('ven_fecha', $hoy)
                ->where('ven_estado', 'Activa')
                ->count(),

            // Total de productos activos
            'total_productos' => Producto::where('prod_situacion', 'Activo')->count(),

            // Total de categorías activas
            'total_categorias' => ProductoTipo::where('tprod_situacion', '1')->count(),

            // Caja abierta actual
            'caja_abierta' => Caja::where('caja_estado', 'Abierta')->first(),

            // Movimientos de hoy
            'movimientos_hoy' => StockMovimiento::whereDate('created_at', $hoy)->count(),

            // Productos con stock bajo
            'stock_bajo' => Producto::where('prod_situacion', 'Activo')
                ->whereRaw('prod_stock_actual <= prod_stock_minimo')
                ->count(),

            // Productos sin stock
            'sin_stock' => Producto::where('prod_situacion', 'Activo')
                ->where('prod_stock_actual', 0)
                ->count()
        ];
    }

    private function obtenerActividadReciente()
    {
        $actividades = [];

        // Últimas ventas activas (últimas 4 horas)
        $ventasRecientes = Ventas::with(['usuario', 'detalles.producto'])
            ->where('ven_estado', 'Activa')
            ->where('created_at', '>=', Carbon::now()->subHours(4))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($ventasRecientes as $venta) {
            $actividades[] = [
                'tipo' => 'venta',
                'mensaje' => 'Venta completada',
                'detalle' => 'Venta #' . $venta->ven_id . ' - Q ' . number_format($venta->ven_total, 2),
                'tiempo' => $venta->created_at->diffForHumans(),
                'icono' => 'success',
                'color' => 'emerald',
                'usuario' => $venta->usuario->name ?? 'Sistema'
            ];
        }

        // Productos agregados recientemente (último día)
        $productosRecientes = Producto::with('tipo')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($productosRecientes as $producto) {
            $actividades[] = [
                'tipo' => 'producto',
                'mensaje' => 'Nuevo producto agregado',
                'detalle' => $producto->prod_nombre . ' - ' . $producto->prod_stock_actual . ' unidades',
                'tiempo' => $producto->created_at->diffForHumans(),
                'icono' => 'add',
                'color' => 'blue',
                'usuario' => 'Sistema'
            ];
        }

        // Movimientos de stock recientes (entradas importantes)
        $movimientosRecientes = StockMovimiento::with('producto')
            ->where('mov_tipo', 'Entrada')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($movimientosRecientes as $movimiento) {
            $actividades[] = [
                'tipo' => 'stock',
                'mensaje' => 'Stock actualizado',
                'detalle' => $movimiento->producto->prod_nombre . ' +' . $movimiento->mov_cantidad . ' unidades',
                'tiempo' => $movimiento->created_at->diffForHumans(),
                'icono' => 'package',
                'color' => 'green',
                'usuario' => 'Sistema'
            ];
        }

        // Ordenar por fecha más reciente
        usort($actividades, function ($a, $b) {
            return strtotime($b['tiempo']) - strtotime($a['tiempo']);
        });

        return array_slice($actividades, 0, 4); // Limitar a 4 actividades
    }

    private function obtenerAlertasStock()
    {
        return Producto::with('tipo')
            ->where('prod_situacion', 'Activo')
            ->where(function ($query) {
                $query->where('prod_stock_actual', 0) // Sin stock
                    ->orWhereRaw('prod_stock_actual <= prod_stock_minimo'); // Stock bajo
            })
            ->orderByRaw('CASE WHEN prod_stock_actual = 0 THEN 1 ELSE 2 END')
            ->orderBy('prod_stock_actual', 'asc')
            ->limit(6)
            ->get()
            ->map(function ($producto) {
                // Determinar nivel de alerta
                if ($producto->prod_stock_actual == 0) {
                    $nivel = 'critico';
                    $color = 'red';
                    $texto = 'Stock crítico: 0 unidades';
                } elseif ($producto->prod_stock_actual <= $producto->prod_stock_minimo) {
                    $nivel = 'bajo';
                    $color = 'orange';
                    $texto = 'Stock bajo: ' . $producto->prod_stock_actual . ' unidades';
                } else {
                    $nivel = 'medio';
                    $color = 'yellow';
                    $texto = 'Stock medio: ' . $producto->prod_stock_actual . ' unidades';
                }

                return [
                    'id' => $producto->prod_id,
                    'nombre' => $producto->prod_nombre,
                    'stock_actual' => $producto->prod_stock_actual,
                    'stock_minimo' => $producto->prod_stock_minimo,
                    'nivel' => $nivel,
                    'color' => $color,
                    'texto_alerta' => $texto,
                    'emoji' => $this->obtenerEmojiProducto($producto->prod_nombre)
                ];
            })
            ->toArray();
    }

    private function obtenerVentasSemana()
    {
        $fechas = [];
        $ventas = [];

        // Generar últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $fechas[] = $this->obtenerNombreDia($fecha->dayOfWeek);

            // Obtener ventas del día (solo activas)
            $totalDia = Ventas::whereDate('ven_fecha', $fecha)
                ->where('ven_estado', 'Activa')
                ->sum('ven_total') ?? 0;

            $ventas[] = $totalDia;
        }

        return [
            'dias' => $fechas,
            'ventas' => $ventas,
            'max_venta' => max($ventas) > 0 ? max($ventas) : 1 // Evitar división por cero
        ];
    }

    private function obtenerNombreDia($diaNumero)
    {
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        return $dias[$diaNumero];
    }

    private function obtenerEmojiProducto($nombreProducto)
    {
        $nombre = strtolower($nombreProducto);

        if (str_contains($nombre, 'teclado')) return '💻';
        if (str_contains($nombre, 'mouse')) return '🖱️';
        if (str_contains($nombre, 'audifono') || str_contains($nombre, 'auricular')) return '🎧';
        if (str_contains($nombre, 'cargador')) return '📱';
        if (str_contains($nombre, 'laptop') || str_contains($nombre, 'portatil')) return '💻';
        if (str_contains($nombre, 'monitor')) return '🖥️';
        if (str_contains($nombre, 'impresora')) return '🖨️';
        if (str_contains($nombre, 'tablet')) return '📱';
        if (str_contains($nombre, 'celular')) return '📱';

        return '📦'; // Emoji por defecto
    }

    private function estadisticasVacias()
    {
        return [
            'ventas_hoy' => 0,
            'transacciones_hoy' => 0,
            'total_productos' => 0,
            'total_categorias' => 0,
            'caja_abierta' => null,
            'movimientos_hoy' => 0,
            'stock_bajo' => 0,
            'sin_stock' => 0
        ];
    }

    public function apiEstadisticas()
    {
        try {
            return response()->json([
                'success' => true,
                'estadisticas' => $this->obtenerEstadisticas(),
                'ventas_semana' => $this->obtenerVentasSemana(),
                'alertas_stock' => $this->obtenerAlertasStock(),
                'actividad_reciente' => $this->obtenerActividadReciente()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}