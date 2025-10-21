@extends('layouts.menu')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- HEADER -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                            ¡Bienvenido, {{ Auth::user()->name }}!
                        </h1>
                        <p class="text-gray-600">
                            Resumen general de tu negocio
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center gap-3 bg-gray-50 rounded-lg px-4 py-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium text-gray-700">
                            {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Ventas Hoy -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">HOY</span>
                </div>
                <h3 class="text-gray-500 text-sm font-medium mb-1">Ventas del Día</h3>
                <p class="text-2xl font-bold text-gray-800 mb-2" id="ventas-hoy">Q 0.00</p>
                <p class="text-xs text-gray-500" id="transacciones-hoy">0 transacciones</p>
            </div>

            <!-- Productos -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500 bg-gray-50 px-2 py-1 rounded-full">INVENTARIO</span>
                </div>
                <h3 class="text-gray-500 text-sm font-medium mb-1">Total Productos</h3>
                <p class="text-2xl font-bold text-gray-800 mb-2" id="total-productos">0</p>
                <p class="text-xs text-gray-500" id="total-categorias">0 categorías</p>
            </div>

            <!-- Estado Caja -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full" id="estado-caja">CERRADA</span>
                </div>
                <h3 class="text-gray-500 text-sm font-medium mb-1">Caja Actual</h3>
                <p class="text-2xl font-bold text-gray-800 mb-2" id="saldo-caja">Q 0.00</p>
                <p class="text-xs text-gray-500" id="nombre-caja">Sin caja abierta</p>
            </div>

            <!-- Alertas Stock -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full" id="alertas-stock">CRÍTICO: 0</span>
                </div>
                <h3 class="text-gray-500 text-sm font-medium mb-1">Alertas Stock</h3>
                <p class="text-2xl font-bold text-gray-800 mb-2" id="total-stock-bajo">0</p>
                <p class="text-xs text-gray-500" id="total-sin-stock">0 sin stock</p>
            </div>
        </div>

        <!-- GRID PRINCIPAL -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- COLUMNA IZQUIERDA -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- GRÁFICO DE VENTAS -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Ventas de la Semana</h3>
                        <select class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option>Últimos 7 días</option>
                            <option>Últimos 30 días</option>
                        </select>
                    </div>
                    <div class="h-80">
                        <canvas id="graficoVentas"></canvas>
                    </div>
                </div>

                <!-- ACCIONES RÁPIDAS -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones Rápidas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ url('/ventas/index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Nueva Venta</p>
                                <p class="text-sm text-gray-500">Registrar transacción</p>
                            </div>
                        </a>

                        <a href="{{ url('/productos') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Agregar Producto</p>
                                <p class="text-sm text-gray-500">Al inventario</p>
                            </div>
                        </a>

                        <a href="{{ url('/productos') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Ver Inventario</p>
                                <p class="text-sm text-gray-500">Gestionar productos</p>
                            </div>
                        </a>

                        <a href="{{ url('/ventas/historial') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Historial Caja</p>
                                <p class="text-sm text-gray-500">Ver movimientos</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div class="space-y-6">
                
                <!-- ALERTAS DE STOCK -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Alertas de Stock</h3>
                        <span class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded-full" id="contador-alertas">0</span>
                    </div>
                    <div class="space-y-3" id="alertas-stock-lista">
                        <div class="text-center py-8">
                            <p class="text-gray-400">Cargando alertas...</p>
                        </div>
                    </div>
                </div>

                <!-- ACTIVIDAD RECIENTE -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Actividad Reciente</h3>
                    <div class="space-y-3" id="actividad-reciente-lista">
                        <div class="text-center py-4">
                            <p class="text-gray-400">Cargando actividades...</p>
                        </div>
                    </div>
                </div>

                <!-- ESTADÍSTICAS RÁPIDAS -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm p-6 text-white">
                    <h3 class="text-lg font-semibold mb-4">Resumen del Día</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-blue-100">Movimientos Hoy</span>
                            <span class="font-bold" id="movimientos-hoy">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-blue-100">Productos Activos</span>
                            <span class="font-bold" id="productos-activos">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

 @vite('resources/js/dashboard.js')