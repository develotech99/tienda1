@extends('layouts.menu')

@section('title', 'Reportes de Inventario')

@section('content')

<style>
    .card-reporte {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .card-reporte:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
    }
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .producto-bajo-stock { border-left: 4px solid #f59e0b; }
    .producto-sin-stock  { border-left: 4px solid #ef4444; }
    .producto-normal     { border-left: 4px solid #10b981; }
    .movimiento-entrada  { border-left: 4px solid #10b981; }
    .movimiento-salida   { border-left: 4px solid #ef4444; }

    /* Micro-tuning en móviles */
    @media (max-width: 640px) {
        .stat-card { padding: 16px; }
        .stat-number { font-size: 2rem; }
        .card-reporte { border-radius: 10px; }
    }
</style>

<!-- Contenedor principal: apila en móvil -->
<div class="flex flex-col md:flex-row md:h-[calc(100vh-120px)] min-h-[calc(100vh-120px)] gap-4">

    <!-- SIDEBAR DE FILTROS: full en móvil, fijo sutil en desktop -->
    <div class="w-full md:w-80 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden flex flex-col md:sticky md:top-20">
        <div class="px-3 md:px-4 py-4 bg-gradient-to-br from-blue-600 via-blue-600 to-blue-700 flex-shrink-0">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <h3 class="text-white font-bold text-base">Filtros</h3>
            </div>
            <p class="text-blue-100 text-xs">Filtra los reportes por fecha y categoría</p>
        </div>

        <div class="flex-1 overflow-y-auto p-3 md:p-4 space-y-4">
            <!-- Filtro por fecha -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rango de fechas</label>
                <div class="space-y-2">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Desde</label>
                        <input type="date" id="fecha_desde" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Hasta</label>
                        <input type="date" id="fecha_hasta" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    </div>
                </div>
            </div>

            <!-- Filtro por categoría -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Categoría</label>
                <select id="filtro_categoria" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    <option value="">Todas las categorías</option>
                    @foreach($tipos as $tipo)
                    <option value="{{ $tipo->tprod_id }}">{{ $tipo->tprod_nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro tipo de reporte -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Reporte</label>
                <select id="filtro_tipo" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    <option value="stock_clasificado">Stock Clasificado</option>
                    <option value="movimientos">Movimientos Recientes</option>
                    <option value="mas_vendidos">Productos Más Vendidos</option>
                    <option value="sin_stock">Productos Sin Stock</option>
                </select>
            </div>

            <!-- Botón aplicar filtros -->
            <button id="btnAplicarFiltros" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition shadow-sm">
                Aplicar Filtros
            </button>

            <!-- Botón exportar -->
            <button id="btnExportar" class="w-full px-4 py-2.5 border border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition">
                Exportar Reporte
            </button>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="flex-1 flex flex-col gap-4 overflow-visible md:overflow-hidden">

        <!-- ESTADÍSTICAS RÁPIDAS: grid fluido -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="stat-number" id="stat-total-productos">0</div>
                <div class="stat-label">Total Productos</div>
            </div>
            <div class="stat-card bg-gradient-to-r from-amber-500 to-amber-600">
                <div class="stat-number" id="stat-stock-bajo">0</div>
                <div class="stat-label">Stock Bajo</div>
            </div>
            <div class="stat-card bg-gradient-to-r from-red-500 to-red-600">
                <div class="stat-number" id="stat-sin-stock">0</div>
                <div class="stat-label">Sin Stock</div>
            </div>
            <div class="stat-card bg-gradient-to-r from-green-500 to-green-600">
                <div class="stat-number" id="stat-movimientos-hoy">0</div>
                <div class="stat-label">Movimientos Hoy</div>
            </div>
        </div>

        <!-- REPORTE: STOCK BAJO -->
        <div id="reporte-stock-bajo" class="reporte-contenido">
            <div class="card-reporte p-4 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Productos con Stock Bajo
                    </h3>
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm font-semibold" id="contador-stock-bajo">0 productos</span>
                </div>

                <div class="space-y-3 max-h-[50vh] md:max-h-96 overflow-y-auto" id="lista-stock-bajo">
                    <!-- Los productos se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>

        <!-- REPORTE: MOVIMIENTOS RECIENTES -->
        <div id="reporte-movimientos" class="reporte-contenido hidden">
            <div class="card-reporte p-4 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Movimientos Recientes
                    </h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold" id="contador-movimientos">0 movimientos</span>
                </div>

                <div class="space-y-3 max-h-[50vh] md:max-h-96 overflow-y-auto" id="lista-movimientos">
                    <!-- Los movimientos se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>

        <!-- REPORTE: PRODUCTOS MÁS VENDIDOS -->
        <div id="reporte-mas-vendidos" class="reporte-contenido hidden">
            <div class="card-reporte p-4 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Productos Más Vendidos
                    </h3>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold" id="contador-mas-vendidos">0 productos</span>
                </div>

                <div class="space-y-3 max-h-[50vh] md:max-h-96 overflow-y-auto" id="lista-mas-vendidos">
                    <!-- Los productos más vendidos se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>

        <!-- REPORTE: PRODUCTOS SIN STOCK -->
        <div id="reporte-sin-stock" class="reporte-contenido hidden">
            <div class="card-reporte p-4 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Productos Sin Stock
                    </h3>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold" id="contador-sin-stock">0 productos</span>
                </div>

                <div class="space-y-3 max-h-[50vh] md:max-h-96 overflow-y-auto" id="lista-sin-stock">
                    <!-- Los productos sin stock se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>

    </div>
</div>

<!-- TEMPLATES PARA LOS ELEMENTOS DINÁMICOS -->
<template id="template-producto-stock-bajo">
    <div class="producto-bajo-stock bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition">
        <div class="flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-800 text-sm truncate">{nombre}</h4>
                <p class="text-xs text-gray-500 truncate">{categoria}</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
                    <span class="text-xs text-gray-600">Stock actual: <strong class="text-amber-600">{stock_actual}</strong></span>
                    <span class="text-xs text-gray-600">Stock mínimo: <strong>{stock_minimo}</strong></span>
                    <span class="text-xs text-amber-600 font-semibold">¡Necesita reposición!</span>
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-lg font-bold text-gray-800">Q{precio_venta}</div>
                <div class="text-xs text-gray-500">{codigo}</div>
            </div>
        </div>
    </div>
</template>

<template id="template-movimiento">
    <div class="movimiento-{tipo} bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition">
        <div class="flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-800 text-sm truncate">{producto_nombre}</h4>
                <p class="text-xs text-gray-500 truncate">{motivo}</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
                    <span class="text-xs text-gray-600">{fecha}</span>
                    <span class="text-xs {tipo_color} font-semibold">{tipo_texto}: {cantidad} unidades</span>
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-sm font-semibold {tipo_color}">{stock_anterior} → {stock_nuevo}</div>
                <div class="text-xs text-gray-500">{observacion}</div>
            </div>
        </div>
    </div>
</template>

@endsection

@vite('resources/js/productos/reportes.js')
