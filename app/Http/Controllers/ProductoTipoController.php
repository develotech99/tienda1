<?php

namespace App\Http\Controllers;

use App\Models\ProductoTipo;
use Exception;
use Illuminate\Http\Request;

class ProductoTipoController extends Controller
{
    public function index()
    {
        return view("productos.tipo");
    }

    // Listado API (mejor usar index API aparte, pero respetamos tu show actual)
    public function show(Request $request)
    {
        try {
            $data = ProductoTipo::where('tprod_situacion', 1)
                ->orderByDesc('tprod_id')
                ->get();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Datos obtenidos correctamente',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los datos',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $reglas = [
                "tprod_nombre" => ["required","string","max:100","unique:producto_tipo,tprod_nombre"],
                "tprod_desc"   => ["nullable","string"],
            ];

            $mensajes = [
                'tprod_nombre.required' => "El nombre es obligatorio",
                'tprod_nombre.max'      => "El nombre no debe tener más de 100 caracteres",
                'tprod_nombre.unique'   => "Ya existe una categoría con el mismo nombre",
            ];

            $data = $request->validate($reglas, $mensajes);

            $data['tprod_nombre']   = trim($data['tprod_nombre']);
            $desc                   = trim((string) $request->input('tprod_desc'));
            $data['tprod_desc']     = $desc === '' ? null : $desc;
            $data['tprod_situacion']= 1;

            ProductoTipo::create($data);

            return response()->json([
                'codigo' => 1,
                'mensaje' => "Categoría creada exitosamente"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => "Error al crear la categoría",
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $reglas = [
                "tprod_id"     => ["required","integer","exists:producto_tipo,tprod_id"],
                "tprod_nombre" => ["required","string","max:100","unique:producto_tipo,tprod_nombre,".$request->tprod_id.",tprod_id"],
                "tprod_desc"   => ["nullable","string"],
            ];

            $mensajes = [
                'tprod_id.required'     => "El ID de la categoría es obligatorio",
                'tprod_id.exists'       => "La categoría no existe",
                'tprod_nombre.required' => "El nombre es obligatorio",
                'tprod_nombre.max'      => "El nombre no debe tener más de 100 caracteres",
                'tprod_nombre.unique'   => "Ya existe una categoría con ese nombre",
            ];

            $data = $request->validate($reglas, $mensajes);

            $data['tprod_nombre'] = trim($data['tprod_nombre']);
            $desc                 = trim((string) $request->input('tprod_desc'));
            $data['tprod_desc']   = $desc === '' ? null : $desc;

            ProductoTipo::where('tprod_id', $data['tprod_id'])->update([
                'tprod_nombre' => $data['tprod_nombre'],
                'tprod_desc'   => $data['tprod_desc'],
            ]);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Categoría actualizada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al actualizar la categoría',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $reglas = [
                "tprod_id" => ["required","integer","exists:producto_tipo,tprod_id"],
            ];

            $mensajes = [
                'tprod_id.required' => "El ID de la categoría es obligatorio",
                'tprod_id.exists'   => "La categoría no existe",
            ];

            $data = $request->validate($reglas, $mensajes);

            $categoria = ProductoTipo::find($data['tprod_id']);
            $categoria->tprod_situacion = 0;
            $categoria->save();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Categoría eliminada correctamente',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al eliminar la categoría',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}
