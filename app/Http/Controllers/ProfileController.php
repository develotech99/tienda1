<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'telefono' => $request->telefono,
        ]);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Eliminar foto anterior si existe
        if ($user->photo && Storage::exists('public/' . $user->photo)) {
            Storage::delete('public/' . $user->photo);
        }

        // Guardar nueva foto
        $photoPath = $request->file('photo')->store('profile-photos', 'public');

        $user->update([
            'photo' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'photo_url' => Storage::url($photoPath),
            'message' => 'Foto de perfil actualizada correctamente.'
        ]);
    }

    public function showChangePassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed', 
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
