@extends('layouts.menu')

@section('title', 'Usuarios')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Usuarios del Sistema</h1>
        </div>

        <button type="button"
            id="btnAbrirModalUsuario"
            data-modal-open="modalUsuario"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
            Agregar Usuario
        </button>
    </div>

    <div class="bg-white/80 backdrop-blur-sm border border-emerald-100 rounded-xl shadow-sm dt-card">
        <div class="p-4">
            <table id="tablaUsuarios" class="stripe hover w-full text-sm"></table>
        </div>
    </div>

</div>

<!-- Modal para Agregar/Editar Usuario -->
<div id="modalUsuario" class="hidden fixed inset-0 z-50">

    <div class="absolute inset-0 bg-black/40" data-modal-close="modalUsuario"></div>

    <div class="relative mx-auto mt-16 w-11/12 sm:w-[34rem] bg-white rounded-xl shadow-2xl overflow-hidden">

        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold" id="modalUsuarioTitulo">Agregar Usuario</h3>
            <button type="button" class="p-2 rounded hover:bg-gray-100" data-modal-close="modalUsuario" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="formUsuario">
            @csrf
            <input type="hidden" id="user_id" name="user_id" value="">

            <div class="px-5 py-4 space-y-4">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <input type="text" id="name" name="name" maxlength="255"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Ej: Juan Pérez García"
                        required>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input type="email" id="email" name="email" maxlength="255"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Ej: juan@empresa.com"
                        required>
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" maxlength="20"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Ej: +1 234 567 8900">
                </div>

                <!-- Rol -->
                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select id="rol" name="rol" 
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        required>
                        <option value="">Seleccione un rol</option>
                        <option value="vendedor">Vendedor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <!-- Contraseña (solo para crear) -->
                <div id="campoPassword">
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="password" id="password" name="password" 
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Mínimo 8 caracteres"
                        required>
                    <small class="text-gray-500">La contraseña es obligatoria al crear un usuario</small>
                </div>

                <!-- Confirmar Contraseña (solo para crear) -->
                <div id="campoPasswordConfirmation">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Repetir contraseña"
                        required>
                </div>
            </div>

            <div class="px-6 py-5 border-t bg-gradient-to-r from-gray-50 to-gray-100 flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium bg-white hover:bg-gray-100 hover:text-gray-900 transition-all duration-200 shadow-sm hover:shadow"
                    data-modal-close="modalUsuario">
                    <i class="fas fa-times mr-2 text-gray-500"></i>Cancelar
                </button>

                <button
                    type="submit"
                    id="btnGuardarUsuario"
                    class="px-5 py-2.5 rounded-xl font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-400 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2 text-white/90"></i>Guardar
                </button>

                <button
                    type="button"
                    id="btnActualizarUsuario"
                    class="hidden px-5 py-2.5 rounded-xl font-semibold text-white bg-sky-600 hover:bg-sky-700 focus:ring-2 focus:ring-sky-400 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-sync-alt mr-2 text-white/90"></i>Actualizar
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@vite('resources/js/usuarios/usuarios.js') 