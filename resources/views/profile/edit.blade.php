@extends('layouts.menu')

@section('title', 'Editar Perfil')

@section('content')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Editar Perfil</h1>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm border border-emerald-100 rounded-xl shadow-sm">
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Foto de perfil -->
                    <div class="lg:col-span-1">
                        <div class="text-center">
                            <div class="mb-4">
                                <img src="{{ $user->photo ? Storage::url($user->photo) . '?t=' . time() : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200&background=059669&color=fff' }}"
                                    alt="Foto de perfil" id="currentProfilePic"
                                    class="w-32 h-32 rounded-full mx-auto border-4 border-emerald-100 shadow-sm object-cover">
                            </div>

                            <div class="space-y-2">
                                <button type="button" onclick="document.getElementById('photoInput').click()"
                                    class="w-full px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm transition">
                                    <i class="fas fa-camera mr-2"></i>Cambiar Foto
                                </button>
                                <input type="file" id="photoInput" accept="image/*" style="display: none;"
                                    onchange="uploadPhoto(this)">

                                @if ($user->photo)
                                    <button type="button" onclick="removePhoto()"
                                        class="w-full px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 shadow-sm transition">
                                        <i class="fas fa-trash mr-2"></i>Eliminar Foto
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="lg:col-span-2">
                        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre
                                    Completo</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                                    placeholder="Ingresa tu nombre completo" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                <div class="mt-1">
                                    <input type="email" value="{{ $user->email }}"
                                        class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-500 text-sm"
                                        readonly>
                                    <p class="mt-1 text-sm text-gray-500">
                                        El correo electrónico no se puede modificar
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" id="telefono" name="telefono"
                                    value="{{ old('telefono', $user->telefono) }}"
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                                    placeholder="12345678">
                                @error('telefono')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3 pt-4">
                                <a href="{{ url('/home') }}"
                                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium bg-white hover:bg-gray-100 transition">
                                    Cancelar
                                </a>
                                <button type="submit"
                                    class="px-5 py-2.5 rounded-lg font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function uploadPhoto(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('photo', input.files[0]);

                const button = input.previousElementSibling;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subiendo...';
                button.disabled = true;

                fetch('{{ route('profile.update.photo') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Forzar recarga de la imagen
                            const img = document.getElementById('currentProfilePic');
                            img.src = data.photo_url + '?t=' + new Date().getTime();

                            // SweetAlert de éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: 'Foto actualizada correctamente',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });

                            // Recargar la página después de 1 segundo para actualizar el navbar
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al subir la foto'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al subir la foto'
                        });
                    })
                    .finally(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
            }
        }

        function removePhoto() {
            Swal.fire({
                title: '¿Eliminar foto?',
                text: "¿Estás seguro de que quieres eliminar tu foto de perfil?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#059669',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'En desarrollo',
                        text: 'Función de eliminar foto en desarrollo',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        }
    </script>
@endsection
