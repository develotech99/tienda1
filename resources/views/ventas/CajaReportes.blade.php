@extends('layouts.menu')

@section('title', 'Caja e Historial de Ventas')

@section('content')

<style>
    /* SOLO SCROLL HORIZONTAL PARA DATATABLES */
    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .dataTable {
        width: 100% !important;
        min-width: 800px;
        /* Fuerza scroll en móviles */
    }

    /* BOTONES SIEMPRE VISIBLES */
    .flex.gap-2 button span {
        display: inline !important;
        /* Forzar que el texto siempre se vea */
    }

    @media (max-width: 640px) {
        .dataTable {
            min-width: 700px;
        }
    }
</style>

<!-- Loader -->
<div id="loaderVenta" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-2xl">
        <div class="flex justify-center mb-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Procesando</h3>
            <p class="text-gray-600 text-sm">Por favor espere un momento...</p>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Reportes y Gestión de Caja</h1>
            <p class="text-gray-600 text-sm sm:text-base">Control de ventas, ingresos y egresos</p>
        </div>
        <div class="flex gap-2">
            <button id="btnNuevoIngreso" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition text-sm sm:text-base">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Ingreso</span>
            </button>
            <button id="btnNuevoEgreso" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition text-sm sm:text-base">
                <i class="fas fa-minus"></i>
                <span class="hidden sm:inline">Egreso</span>
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl p-4 sm:p-6 mb-6 shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" id="fechaInicio" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" id="fechaFin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select id="tipoMovimiento" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="todos">Todos</option>
                    <option value="venta">Ventas</option>
                    <option value="ingreso">Ingresos</option>
                    <option value="egreso">Egresos</option>
                </select>
            </div>
            <div class="flex items-end">
                <button id="btnFiltrar" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-8">
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Saldo en Caja</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-800" id="saldoCaja">Q 0.00</p>
                </div>
                <div class="p-2 sm:p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-wallet text-blue-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Ventas del Día</p>
                    <p class="text-lg sm:text-2xl font-bold text-green-600" id="ventasHoy">Q 0.00</p>
                </div>
                <div class="p-2 sm:p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-green-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Ingresos del Día</p>
                    <p class="text-lg sm:text-2xl font-bold text-emerald-600" id="ingresosHoy">Q 0.00</p>
                </div>
                <div class="p-2 sm:p-3 bg-emerald-100 rounded-lg">
                    <i class="fas fa-arrow-down text-emerald-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Egresos del Día</p>
                    <p class="text-lg sm:text-2xl font-bold text-red-600" id="egresosHoy">Q 0.00</p>
                </div>
                <div class="p-2 sm:p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-arrow-up text-red-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">Movimientos de Caja</h3>
        </div>
        <div class="p-2 sm:p-4">
            <div class="table-responsive">
                <table id="tablaMovimientos" class="w-full table-movimientos dt-responsive">
                    <thead>
                        <tr>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Final</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargan via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla de Total de Ingresos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800">Saldos por Día</h3>
            <p class="text-xs sm:text-sm text-gray-600">Resumen diario de ventas, ingresos y egresos</p>
        </div>
        <div class="p-2 sm:p-4">
            <div class="table-responsive">
                <table id="tablaSaldosDia" class="w-full table-saldos dt-responsive">
                    <thead>
                        <tr>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ingresos</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Egresos</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo del Día</th>
                            <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant. Movimientos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargan via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ingreso -->
<div id="modalIngreso" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-4 sm:p-6 max-w-md w-full mx-2">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Registrar Ingreso</h3>
            <button id="btnCerrarModalIngreso" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formIngreso">
            <input type="hidden" id="movimiento_id" name="movimiento_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto (Q)</label>
                    <input type="number" step="0.01" id="monto_ingreso" name="monto" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                        placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea id="descripcion_ingreso" name="descripcion" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                        placeholder="Descripción del ingreso" rows="3"></textarea>
                </div>
            </div>
            <div class="flex gap-2 sm:gap-3 mt-6">
                <button type="button" id="btnCancelarIngreso" class="flex-1 px-3 sm:px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    Cancelar
                </button>
                <button type="submit" id="btnGuardarIngreso" class="flex-1 px-3 sm:px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium text-sm">
                    <i class="fas fa-check mr-1 sm:mr-2"></i>
                    Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Egreso -->
<div id="modalEgreso" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-4 sm:p-6 max-w-md w-full mx-2">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Registrar Egreso</h3>
            <button id="btnCerrarModalEgreso" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formEgreso">
            <input type="hidden" id="movimiento_id_egreso" name="movimiento_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto (Q)</label>
                    <input type="number" step="0.01" id="monto_egreso" name="monto" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                        placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea id="descripcion_egreso" name="descripcion" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                        placeholder="Descripción del egreso" rows="3"></textarea>
                </div>
            </div>
            <div class="flex gap-2 sm:gap-3 mt-6">
                <button type="button" id="btnCancelarEgreso" class="flex-1 px-3 sm:px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    Cancelar
                </button>
                <button type="submit" id="btnGuardarEgreso" class="flex-1 px-3 sm:px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium text-sm">
                    <i class="fas fa-check mr-1 sm:mr-2"></i>
                    Registrar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@vite('resources/js/ventas/caja.js')