@extends('layouts.auth')

@section('title', 'Iniciar sesión')
@section('subtitle', 'Accede a tu cuenta para gestionar la Tienda Aprils')

@section('content')


<div id="loginContainer"
    data-endpoint-login=""
    data-redirect-success=""
    data-route-register="{{ Route::has('register') ? route('register') : '' }}"
    data-route-forgot="{{ Route::has('password.request') ? route('password.request') : '' }}">

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
        <input
            id="email"
            name="email"
            type="email"
            autocomplete="username"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            placeholder="tucorreo@ejemplo.com">
        <p id="emailError" class="mt-1 text-sm text-red-600 hidden"></p>
    </div>


    <div class="mb-4">
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>


            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}"
                class="text-xs text-emerald-700 hover:underline">
                ¿Olvidaste tu contraseña?
            </a>
            @endif
        </div>

        <div class="relative mt-1">
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                class="block w-full rounded-md border-gray-300 shadow-sm pr-10 focus:border-emerald-500 focus:ring-emerald-500"
                placeholder="********">
            <button type="button" id="togglePass"
                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                aria-label="Mostrar/ocultar contraseña">👁️</button>
        </div>

        <p id="passwordError" class="mt-1 text-sm text-red-600 hidden"></p>
    </div>

    <div class="mb-6">
        <label class="inline-flex items-center">
            <input id="remember" type="checkbox"
                class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
            <span class="ml-2 text-sm text-gray-600">Recordarme</span>
        </label>
    </div>

    <div id="loginAlert" class="mb-4 hidden rounded-md p-3 text-sm"></div>

    <button
        type="button"
        id="loginBtn"
        class="w-full inline-flex justify-center items-center gap-2 rounded-md bg-emerald-600 px-4 py-2.5 text-white font-medium shadow hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
        Iniciar sesión
    </button>

    <div class="mt-6 text-center text-sm text-gray-600">
        <span>¿No tienes cuenta?</span>

        @if (Route::has('register'))
        <a href="{{ route('register') }}" class="text-emerald-700 hover:underline">
            Crear una nueva cuenta
        </a>
        @else

        <button type="button" id="goRegister" class="text-emerald-700 hover:underline">
            Crear una nueva cuenta
        </button>
        @endif
    </div>
</div>
@endsection


@vite('resources/js/auth/login.js')