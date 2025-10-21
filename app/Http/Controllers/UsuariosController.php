<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class UsuariosController extends Controller
{

    public function index()
    {
        return view('usuarios.index');
    }


    public function obtenerDatos()
    {
        try {
            $usuarios = User::select(['id', 'name', 'email', 'telefono', 'rol', 'created_at'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Datos obtenidos correctamente',
                'data' => $usuarios
            ]);

        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los datos',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    public function guardar(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'telefono' => 'nullable|string|max:20',
                'rol' => 'required|in:admin,vendedor',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $usuario = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'rol' => $request->rol,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Usuario creado exitosamente.',
                'data' => $usuario
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error de validación',
                'detalle' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al crear el usuario',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

 
    public function actualizar(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $request->user_id,
                'telefono' => 'nullable|string|max:20',
                'rol' => 'required|in:admin,vendedor',
                'password' => 'nullable|confirmed|min:8',
            ]);

            $usuario = User::findOrFail($request->user_id);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'rol' => $request->rol,
            ];

            // Actualizar contraseña solo si se proporciona
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $usuario->update($data);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Usuario actualizado exitosamente.',
                'data' => $usuario
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error de validación',
                'detalle' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al actualizar el usuario',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar usuario
     */
    public function eliminar(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $usuario = User::findOrFail($request->user_id);

            // Evitar que el usuario se elimine a sí mismo
            if ($usuario->id === auth()->id()) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'No puedes eliminar tu propio usuario.',
                ], 422);
            }

            $usuario->delete();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Usuario eliminado exitosamente.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error de validación',
                'detalle' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al eliminar el usuario',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}