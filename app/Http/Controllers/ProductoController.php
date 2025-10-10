<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoTipo;
use App\Models\StockMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    // Listar productos
    public function index()
    {
        $productos = Producto::with('tipo')
            ->where('prod_situacion', 'Activo')
            ->orderBy('prod_nombre')
            ->paginate(20);

        $tipos = ProductoTipo::where('tprod_situacion', '1')->get();

        return view('productos.index', compact('productos', 'tipos'));
    }

    // Mostrar formulario de registro
    public function create()
    {
        $tipos = ProductoTipo::where('tprod_situacion', '1')->get();
        return view('productos.create', compact('tipos'));
    }

    // ============================================
    // OBTENER DATOS PARA DATATABLE (AJAX)
    // ============================================
    public function obtenerDatosAPI()
    {
        try {
            $productos = Producto::with('tipo')
                ->where('prod_situacion', 'Activo')
                ->orderBy('prod_nombre')
                ->get();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Datos obtenidos correctamente',
                'data' => $productos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener datos: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ============================================
    // MOSTRAR UN PRODUCTO ESPECÍFICO (AJAX)
    // ============================================
    public function show($id)
    {
        try {
            $producto = Producto::with('tipo')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'producto' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    // ============================================
    // GUARDAR NUEVO PRODUCTO (AJAX)
    // ============================================
    public function store(Request $request)
    {
        $request->validate([
            'prod_codigo' => 'nullable|string|max:50|unique:productos,prod_codigo',
            'prod_nombre' => 'required|string|max:200',
            'prod_descripcion' => 'nullable|string',
            'prod_precio_compra' => 'required|numeric|min:0',
            'prod_precio_venta' => 'required|numeric|min:0',
            'prod_stock_minimo' => 'required|integer|min:0',
            'prod_stock_actual' => 'required|integer|min:0',
            'tprod_id' => 'required|exists:producto_tipo,tprod_id',
            'prod_imagen' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Si no tiene código, generar uno único
            $codigo = $request->prod_codigo ?: Producto::generarCodigoUnico();

            // Procesar imagen si existe
            $imagenPath = null;
            if ($request->hasFile('prod_imagen')) {
                $imagenPath = $request->file('prod_imagen')->store('productos', 'public');
            }

            // Crear producto
            $producto = Producto::create([
                'prod_codigo' => $codigo,
                'prod_nombre' => $request->prod_nombre,
                'prod_descripcion' => $request->prod_descripcion,
                'prod_precio_compra' => $request->prod_precio_compra,
                'prod_precio_venta' => $request->prod_precio_venta,
                'prod_stock_minimo' => $request->prod_stock_minimo,
                'prod_stock_actual' => $request->prod_stock_actual,
                'prod_imagen' => $imagenPath,
                'tprod_id' => $request->tprod_id,
                'prod_situacion' => 'Activo'
            ]);

            // Registrar movimiento inicial de stock
            if ($request->prod_stock_actual > 0) {
                StockMovimiento::create([
                    'prod_id' => $producto->prod_id,
                    'mov_tipo' => 'Entrada',
                    'mov_cantidad' => $request->prod_stock_actual,
                    'mov_stock_anterior' => 0,
                    'mov_stock_nuevo' => $request->prod_stock_actual,
                    'mov_motivo' => 'Stock inicial',
                    'mov_observacion' => 'Registro inicial del producto'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto registrado exitosamente',
                'producto' => $producto->load('tipo')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // ACTUALIZAR PRODUCTO (AJAX)
    // ============================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'prod_codigo' => 'nullable|string|max:50|unique:productos,prod_codigo,' . $id . ',prod_id',
            'prod_nombre' => 'required|string|max:200',
            'prod_descripcion' => 'nullable|string',
            'prod_precio_compra' => 'required|numeric|min:0',
            'prod_precio_venta' => 'required|numeric|min:0',
            'prod_stock_minimo' => 'required|integer|min:0',
            'prod_stock_actual' => 'required|integer|min:0',
            'tprod_id' => 'required|exists:producto_tipo,tprod_id',
            'prod_imagen' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($id);
            
            // Si no tiene código, usar el existente
            $codigo = $request->prod_codigo ?: $producto->prod_codigo;

            // Procesar nueva imagen si existe
            $imagenPath = $producto->prod_imagen;
            if ($request->hasFile('prod_imagen')) {
                // Eliminar imagen anterior si existe
                if ($producto->prod_imagen) {
                    Storage::disk('public')->delete($producto->prod_imagen);
                }
                $imagenPath = $request->file('prod_imagen')->store('productos', 'public');
            }

            // Verificar si hay cambio en el stock
            $stockAnterior = $producto->prod_stock_actual;
            $stockNuevo = $request->prod_stock_actual;
            
            // Actualizar producto
            $producto->update([
                'prod_codigo' => $codigo,
                'prod_nombre' => $request->prod_nombre,
                'prod_descripcion' => $request->prod_descripcion,
                'prod_precio_compra' => $request->prod_precio_compra,
                'prod_precio_venta' => $request->prod_precio_venta,
                'prod_stock_minimo' => $request->prod_stock_minimo,
                'prod_stock_actual' => $stockNuevo,
                'prod_imagen' => $imagenPath,
                'tprod_id' => $request->tprod_id
            ]);

            // Registrar movimiento si hay cambio en stock
            if ($stockAnterior != $stockNuevo) {
                $diferencia = $stockNuevo - $stockAnterior;
                
                StockMovimiento::create([
                    'prod_id' => $producto->prod_id,
                    'mov_tipo' => 'Ajuste',
                    'mov_cantidad' => abs($diferencia),
                    'mov_stock_anterior' => $stockAnterior,
                    'mov_stock_nuevo' => $stockNuevo,
                    'mov_motivo' => 'Ajuste por edición',
                    'mov_observacion' => 'Stock ajustado desde ' . $stockAnterior . ' a ' . $stockNuevo
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'producto' => $producto->load('tipo')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // ELIMINAR PRODUCTO (AJAX)
    // ============================================
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($id);
            
            // Cambiar situación a Inactivo en lugar de eliminar
            $producto->update(['prod_situacion' => 'Inactivo']);

            // Registrar movimiento de baja si tiene stock
            if ($producto->prod_stock_actual > 0) {
                StockMovimiento::create([
                    'prod_id' => $producto->prod_id,
                    'mov_tipo' => 'Salida',
                    'mov_cantidad' => $producto->prod_stock_actual,
                    'mov_stock_anterior' => $producto->prod_stock_actual,
                    'mov_stock_nuevo' => 0,
                    'mov_motivo' => 'Producto eliminado',
                    'mov_observacion' => 'Producto dado de baja del sistema'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // BUSCAR PRODUCTO POR CÓDIGO DE BARRAS (AJAX)
    // ============================================
    public function buscarPorCodigo(Request $request)
    {
        $codigo = $request->codigo;
        $producto = Producto::where('prod_codigo', $codigo)
            ->where('prod_situacion', 'Activo')
            ->with('tipo')
            ->first();

        if ($producto) {
            return response()->json([
                'success' => true,
                'producto' => $producto
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
    }

    // ============================================
    // ACTUALIZAR STOCK (AJAX)
    // ============================================
    public function actualizarStock(Request $request, $id)
    {
        $request->validate([
            'mov_tipo' => 'required|in:Entrada,Salida,Ajuste',
            'mov_cantidad' => 'required|integer|min:1',
            'mov_motivo' => 'required|string|max:200',
            'mov_observacion' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($id);
            $stockAnterior = $producto->prod_stock_actual;

            // Calcular nuevo stock
            $nuevoStock = $stockAnterior;
            if ($request->mov_tipo == 'Entrada') {
                $nuevoStock += $request->mov_cantidad;
            } elseif ($request->mov_tipo == 'Salida') {
                $nuevoStock -= $request->mov_cantidad;
                if ($nuevoStock < 0) {
                    throw new \Exception('Stock insuficiente');
                }
            } else { // Ajuste
                $nuevoStock = $request->mov_cantidad;
            }

            // Actualizar producto
            $producto->update(['prod_stock_actual' => $nuevoStock]);

            // Registrar movimiento
            StockMovimiento::create([
                'prod_id' => $producto->prod_id,
                'mov_tipo' => $request->mov_tipo,
                'mov_cantidad' => $request->mov_cantidad,
                'mov_stock_anterior' => $stockAnterior,
                'mov_stock_nuevo' => $nuevoStock,
                'mov_motivo' => $request->mov_motivo,
                'mov_observacion' => $request->mov_observacion
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Stock actualizado correctamente',
                'stock_actual' => $nuevoStock
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}