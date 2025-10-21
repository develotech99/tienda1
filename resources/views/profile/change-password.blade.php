@extends('layouts.menu')

@section('title', 'Cambiar Contraseña')


@section('content')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Cambiar Contraseña</h1>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm border border-emerald-100 rounded-xl shadow-sm max-w-2xl mx-auto">
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Contraseña
                            Actual</label>
                        <input type="password" id="current_password" name="current_password"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm @error('current_password') border-red-300 @enderror"
                            placeholder="Ingresa tu contraseña actual" required>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm @error('new_password') border-red-300 @enderror"
                            placeholder="Crea una nueva contraseña" required>
                        @error('new_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar
                            Nueva Contraseña</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                            placeholder="Repite la nueva contraseña" required>
                    </div>

                    <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            La nueva contraseña debe tener al menos 8 caracteres.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <a href="{{ url('/home') }}"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium bg-white hover:bg-gray-100 transition">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                            <i class="fas fa-key mr-2"></i>Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar contraseñas
            const passwordInputs = document.querySelectorAll('input[type="password"]');

            passwordInputs.forEach(input => {
                const wrapper = document.createElement('div');
                wrapper.className = 'relative';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className =
                    'absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600';
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';

                toggleBtn.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' :
                        '<i class="fas fa-eye-slash"></i>';
                });

                wrapper.appendChild(toggleBtn);
            });

            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Cambiar contraseña?',
                    text: "¿Estás seguro?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endsection
