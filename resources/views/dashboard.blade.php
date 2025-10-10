@extends('layouts.menu')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- ENCABEZADO DE BIENVENIDA -->
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 py-8 md:px-8 md:py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                        ¡Bienvenido de vuelta, {{ Auth::user()->name ?? 'Invitado' }}! 👋
                    </h1>
                    <p class="text-emerald-50 text-lg">
                        Aquí está un resumen de tu tienda hoy
                    </p>
                </div>
                <div class="flex items-center gap-2 text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS DE ESTADÍSTICAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Ventas del Día -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">+12.5%</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1">Ventas Hoy</h3>
            <p class="text-2xl font-bold text-gray-800">Q 2,450.00</p>
            <p class="text-xs text-gray-500 mt-2">15 transacciones</p>
        </div>

        <!-- Productos en Stock -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-500 bg-gray-50 px-2 py-1 rounded-full">Stock</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1">Productos</h3>
            <p class="text-2xl font-bold text-gray-800">1,248</p>
            <p class="text-xs text-gray-500 mt-2">En 12 categorías</p>
        </div>

        <!-- Clientes Activos -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">+8 hoy</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1">Clientes</h3>
            <p class="text-2xl font-bold text-gray-800">456</p>
            <p class="text-xs text-gray-500 mt-2">Registrados totales</p>
        </div>

        <!-- Reparaciones Pendientes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">Urgente: 3</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1">Reparaciones</h3>
            <p class="text-2xl font-bold text-gray-800">23</p>
            <p class="text-xs text-gray-500 mt-2">En proceso</p>
        </div>
    </div>

    <!-- SECCIÓN DE ACCIONES RÁPIDAS Y GRÁFICO -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- ACCIONES RÁPIDAS -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Acciones Rápidas
                </h3>
                
                <div class="space-y-3">
                    <a href="{{ url('ventas.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-lg hover:bg-emerald-50 transition group">
                        <div class="p-2 bg-emerald-100 rounded-lg group-hover:bg-emerald-200 transition">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">Nueva Venta</p>
                            <p class="text-xs text-gray-500">Registrar transacción</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                    <a href="{{ url('productos.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-50 transition group">
                        <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">Agregar Producto</p>
                            <p class="text-xs text-gray-500">Al inventario</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                    <a href="{{ url('clientes.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-50 transition group">
                        <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">Nuevo Cliente</p>
                            <p class="text-xs text-gray-500">Registrar información</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                    <a href="{{ url('reparaciones.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-lg hover:bg-orange-50 transition group">
                        <div class="p-2 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">Orden Reparación</p>
                            <p class="text-xs text-gray-500">Crear nueva orden</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- GRÁFICO DE VENTAS -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Ventas de la Semana
                    </h3>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option>Últimos 7 días</option>
                        <option>Últimos 30 días</option>
                        <option>Este mes</option>
                    </select>
                </div>
                
                <!-- Gráfico de barras simple -->
                <div class="space-y-4">
                    <div class="flex items-end gap-2 h-48">
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-200 rounded-t-lg hover:bg-emerald-300 transition" style="height: 60%"></div>
                            <span class="text-xs text-gray-600 font-medium">Lun</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-200 rounded-t-lg hover:bg-emerald-300 transition" style="height: 75%"></div>
                            <span class="text-xs text-gray-600 font-medium">Mar</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-200 rounded-t-lg hover:bg-emerald-300 transition" style="height: 55%"></div>
                            <span class="text-xs text-gray-600 font-medium">Mié</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-200 rounded-t-lg hover:bg-emerald-300 transition" style="height: 85%"></div>
                            <span class="text-xs text-gray-600 font-medium">Jue</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-200 rounded-t-lg hover:bg-emerald-300 transition" style="height: 70%"></div>
                            <span class="text-xs text-gray-600 font-medium">Vie</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-400 rounded-t-lg hover:bg-emerald-500 transition" style="height: 95%"></div>
                            <span class="text-xs text-gray-600 font-medium">Sáb</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-emerald-300 rounded-t-lg hover:bg-emerald-400 transition" style="height: 80%"></div>
                            <span class="text-xs text-gray-600 font-medium">Dom</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center gap-6 pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-emerald-400 rounded"></div>
                            <span class="text-xs text-gray-600">Día actual</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-emerald-200 rounded"></div>
                            <span class="text-xs text-gray-600">Días anteriores</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIVIDAD RECIENTE Y PRODUCTOS BAJOS EN STOCK -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ACTIVIDAD RECIENTE -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Actividad Reciente
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Venta completada</p>
                        <p class="text-xs text-gray-500">Cliente: Juan Pérez - Q 350.00</p>
                        <p class="text-xs text-gray-400 mt-1">Hace 5 minutos</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Nuevo producto agregado</p>
                        <p class="text-xs text-gray-500">Mouse Gamer RGB - 15 unidades</p>
                        <p class="text-xs text-gray-400 mt-1">Hace 25 minutos</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Reparación urgente</p>
                        <p class="text-xs text-gray-500">Laptop Dell - Cliente: María López</p>
                        <p class="text-xs text-gray-400 mt-1">Hace 1 hora</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Nuevo cliente registrado</p>
                        <p class="text-xs text-gray-500">Carlos Rodríguez</p>
                        <p class="text-xs text-gray-400 mt-1">Hace 2 horas</p>
                    </div>
                </div>
            </div>

            <a href="{{ url('/inventario') }}" class="mt-4 block text-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                Ver todo el historial →
            </a>
        </div>

        <!-- PRODUCTOS BAJOS EN STOCK -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Alertas de Inventario
            </h3>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-100 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-lg">💻</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Teclado Mecánico</p>
                            <p class="text-xs text-red-600 font-medium">Stock crítico: 2 unidades</p>
                        </div>
                    </div>
                    <button class="px-3 py-1 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition">
                        Reabastecer
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-100 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <span class="text-lg">🖱️</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Mouse Inalámbrico</p>
                            <p class="text-xs text-orange-600 font-medium">Stock bajo: 5 unidades</p>
                        </div>
                    </div>
                    <button class="px-3 py-1 bg-orange-600 text-white text-xs rounded-lg hover:bg-orange-700 transition">
                        Reabastecer
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-lg">📱</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Cargador USB-C</p>
                            <p class="text-xs text-yellow-600 font-medium">Stock bajo: 8 unidades</p>
                        </div>
                    </div>
                    <button class="px-3 py-1 bg-yellow-600 text-white text-xs rounded-lg hover:bg-yellow-700 transition">
                        Reabastecer
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-lg">🎧</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Audífonos Bluetooth</p>
                            <p class="text-xs text-yellow-600 font-medium">Stock bajo: 6 unidades</p>
                        </div>
                    </div>
                    <button class="px-3 py-1 bg-yellow-600 text-white text-xs rounded-lg hover:bg-yellow-700 transition">
                        Reabastecer
                    </button>
                </div>
            </div>

            <a href="{{ url('/dashboard') }}" class="mt-4 block text-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                Ver inventario completo →
            </a>
        </div>
    </div>

    <!-- MENSAJE MOTIVACIONAL -->
    <div class="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 rounded-2xl shadow-lg p-8 text-center">
        <div class="max-w-2xl mx-auto">
            <svg class="w-12 h-12 text-white/80 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            <h3 class="text-2xl font-bold text-white mb-2">
                ¡Excelente trabajo! 🎉
            </h3>
            <p class="text-emerald-50 text-lg italic">
                "El éxito es la suma de pequeños esfuerzos repetidos día tras día."
            </p>
            <p class="text-emerald-100 text-sm mt-3">
                Tu dedicación hace la diferencia en Tienda Aprils
            </p>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
// Animación de números al cargar (opcional)
document.addEventListener('DOMContentLoaded', function() {
    // Efecto hover suave en tarjetas
    const cards = document.querySelectorAll('.hover\\:shadow-md');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'transform 0.3s ease';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endsection