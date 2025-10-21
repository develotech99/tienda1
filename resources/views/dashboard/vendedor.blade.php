@extends('layouts.menu')

@section('title', 'Panel Vendedor')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- HEADER SIMPLE -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                    ¡Hola, {{ Auth::user()->name }}!
                </h1>
                <p class="text-gray-600">Listo para atender clientes</p>
                
                <!-- Indicador de ventas del día -->
                <div class="mt-4 inline-flex items-center gap-2 bg-green-50 px-4 py-2 rounded-full">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span class="text-green-700 font-medium" id="ventas-hoy-indicador">
                        Ventas hoy: Q {{ number_format($ventasHoy['total'], 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ACCIONES PRINCIPALES -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Nueva Venta -->
            <a href="{{ url('/ventas/index') }}" 
               class="bg-white rounded-xl shadow-sm border-2 border-blue-200 p-6 hover:shadow-lg hover:border-blue-300 transition-all duration-300 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-gray-800">Nueva Venta</h3>
                        <p class="text-gray-500 mt-1">Registrar transacción de venta</p>
                    </div>
                </div>
            </a>

            <!-- Buscar Productos -->
            <a href="{{ url('/ventas/index') }}" 
               class="bg-white rounded-xl shadow-sm border-2 border-green-200 p-6 hover:shadow-lg hover:border-green-300 transition-all duration-300 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-100 rounded-lg group-hover:bg-green-200 transition">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-gray-800">Buscar Productos</h3>
                        <p class="text-gray-500 mt-1">Consultar inventario y precios</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- RESUMEN VENTAS HOY -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Resumen del Día
            </h3>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-gray-800" id="ventas-hoy-vendedor">
                        Q {{ number_format($ventasHoy['total'], 2) }}
                    </p>
                    <p class="text-sm text-gray-500">Total Vendido</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800" id="transacciones-hoy-vendedor">
                        {{ $ventasHoy['transacciones'] }}
                    </p>
                    <p class="text-sm text-gray-500">Transacciones</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@vite('resources/js/dashboard-vendedor.js')