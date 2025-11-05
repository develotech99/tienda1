<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Auth'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 selection:bg-emerald-200/60 selection:text-gray-900">

    <div class="relative min-h-screen overflow-hidden">

        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-emerald-100"></div>

        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(16,185,129,0.08)_1px,transparent_1px)] [background-size:22px_22px]"></div>

        <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full bg-emerald-300/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-emerald-400/30 blur-3xl"></div>
        <div class="pointer-events-none absolute top-1/3 -right-10 h-64 w-64 rounded-full bg-teal-300/30 blur-3xl"></div>

        <div class="relative z-10 flex min-h-screen items-center justify-center px-4">
            <div class="mx-auto w-full max-w-5xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

                    <div class="hidden lg:flex flex-col gap-6">
                        <div class="inline-flex items-center gap-3">
                            <div class="flex justify-center mb-6">
                                <img
                                    src="{{ asset('images/LOGO_TIENDA.jpg') }}"
                                    alt="Tienda Aprils"
                                    class="h-16 w-16 rounded-xl bg-white p-1 shadow-sm ring-1 ring-emerald-900/10 object-contain"
                                    loading="eager"
                                    decoding="async">
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-gray-800">Inventario demo</h1>
                            <p class="text-sm text-gray-500">Sistema de Gestión</p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white/70 backdrop-blur-md ring-1 ring-emerald-900/5 p-6 shadow-xl">
                        <h2 class="text-2xl font-semibold text-gray-800">Bienvenido de vuelta</h2>
                        <p class="mt-2 text-gray-600 leading-relaxed">
                            Administra tu inventario, ventas y reportes desde un panel moderno, seguro y rápido.
                        </p>

                        <ul class="mt-4 space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500"></span>
                                Acceso seguro con control de sesiones.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500"></span>
                                Reportes claros y exportables.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Lado derecho: tarjeta de login con glassmorphism -->
                <div>
                    <div class="mx-auto w-full max-w-md">
                        <div class="text-center mb-4 lg:hidden">
                            <img src="{{ asset('images/LOGO_TIENDA.png') }}" class="mx-auto w-14 h-14 rounded-full shadow-md ring-2 ring-emerald-300/60" alt="Aprils">
                            <div class="mt-2">
                                <span class="text-lg font-semibold text-gray-800">Tienda Aprils</span>
                                @hasSection('subtitle')
                                <p class="text-sm text-gray-500 mt-1">@yield('subtitle')</p>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/70 backdrop-blur-md ring-1 ring-emerald-900/5 shadow-xl overflow-hidden">
                            <!-- Franja/acento superior -->
                            <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600"></div>

                            <div class="p-6">
                                @yield('content')
                            </div>

                            @hasSection('footer-links')
                            <div class="px-6 pb-6 text-center text-sm text-gray-600">
                                @yield('footer-links')
                            </div>
                            @endif
                        </div>

                        <div class="mt-6 text-center text-xs text-gray-400">
                            &copy; {{ date('Y') }} Tienda Aprils · Sistema de Gestión
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

    @yield('scripts')
</body>

</html>