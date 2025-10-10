@extends('layouts.auth')

@section('title', 'Recuperar contraseña')
@section('subtitle', 'Te enviaremos un enlace de restablecimiento a tu correo')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 p-3 text-emerald-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                placeholder="tucorreo@ejemplo.com">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-white font-medium shadow hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            Enviar enlace
        </button>
    </form>

    <div class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Volver a iniciar sesión</a>
    </div>
@endsection
@vite('resources/js/auth/forgot-pass.js')
