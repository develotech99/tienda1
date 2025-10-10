@extends('layouts.auth')

@section('title', 'Restablecer contraseña')
@section('subtitle', 'Ingresa tu nueva contraseña para tu cuenta')

@section('content')
@if ($errors->any())
<div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3 text-red-700 text-sm">
    <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('password.store') }}" novalidate>
    @csrf

    {{-- token que viene en la URL /reset-password/{token} --}}
    <input type="hidden" name="token" value="{{ request()->route('token') }}">

    {{-- email (Laravel lo pide junto con el token) --}}
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ request('email') ?? old('email') }}"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>

    {{-- nueva contraseña --}}
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
        <input
            id="password"
            name="password"
            type="password"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>

    {{-- confirmación --}}
    <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>

    <button type="submit"
        class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-white font-medium shadow hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
        Guardar contraseña
    </button>
</form>

<div class="mt-6 text-center text-sm">
    <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Volver a iniciar sesión</a>
</div>
@endsection

@vite('resources/js/auth/reset-pass.js')