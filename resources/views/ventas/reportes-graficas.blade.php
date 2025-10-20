@extends('layouts.menu')

@section('title', 'Reportes Gráficos')

@section('content')


<div id="loaderGraficas" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-2xl">
        <div class="flex justify-center mb-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Generando Gráficas</h3>
            <p class="text-gray-600 text-sm">Por favor espere un momento...</p>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Reportes Gráficos</h1>
            <p class="text-gray-600">Análisis visual de ventas, productos y movimientos</p>
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" id="fechaInicio" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" id="fechaFin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Gráfica</label>
                <select id="tipoGrafica" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="ventas">Ventas por Día</option>
                    <option value="productos">Productos Más Vendidos</option>
                    <option value="caja">Movimientos de Caja</option>
                    <option value="comparativa">Comparativa Mensual</option>
                </select>
            </div>
            <div class="flex items-end">
                <button id="btnGenerarGraficas" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Generar Gráficas
                </button>
            </div>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" id="tituloGraficaPrincipal">Ventas por Día</h3>
            <div class="h-80">
                <canvas id="graficaPrincipal"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" id="tituloGraficaSecundaria">Distribución</h3>
            <div class="h-80">
                <canvas id="graficaSecundaria"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas del Período</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="estadisticasResumen">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Datos Detallados</h3>
        </div>
        <div class="p-4">
            <div id="tablaDatos">
                <p class="text-gray-500 text-center py-8">Seleccione un tipo de gráfica y haga clic en "Generar Gráficas"</p>
            </div>
        </div>
    </div>
</div>
@endsection

@vite('resources/js/ventas/reportes-graficas.js')