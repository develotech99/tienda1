@extends('layouts.menu')

@section('title', 'Gestión de Productos')

@section('content')
<div class="space-y-6">

    <!-- Header con Scanner -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Título -->
        <div class="lg:col-span-2">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Productos</h1>
            <p class="text-sm text-gray-600 mt-1">Administra tu inventario completo</p>
        </div>

        <!-- Botón Nuevo -->
        <div class="flex items-center justify-end">
            <button 
                type="button"
                id="btnAbrirModalProducto"
                data-modal-open="modalProducto"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                </svg>
                Nuevo Producto
            </button>
        </div>

    </div>

    <!-- Scanner Card -->
    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 border-2 border-emerald-200 rounded-xl shadow-md p-5">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-emerald-600 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
            </div>
            
            <div class="flex-1">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Escáner de Código de Barras</h3>
                <p class="text-sm text-gray-600 mb-3">Escanea o ingresa el código para buscar/agregar productos</p>
                
                <div class="relative">
                    <input 
                        type="text" 
                        id="inputScanner" 
                        autocomplete="off"
                        placeholder="🔍 Escanea o escribe el código aquí..." 
                        class="w-full px-4 py-2.5 pr-28 rounded-lg border-2 border-emerald-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200 transition-all text-sm font-mono"
                    >
                    <div class="absolute right-2 top-1/2 -translate-y-1/2">
                        <span id="scannerStatus" class="text-xs text-emerald-600 font-medium">● Listo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="bg-white/80 backdrop-blur-sm border border-emerald-100 rounded-xl shadow-sm dt-card">
        <div class="p-4">
            <table id="tablaProductos" class="stripe hover w-full text-sm"></table>
        </div>
    </div>

</div>

<!-- MODAL DE PRODUCTO -->
<div id="modalProducto" class="hidden fixed inset-0 z-50">

    <div class="absolute inset-0 bg-black/40" data-modal-close="modalProducto"></div>

    <div class="relative mx-auto mt-8 w-11/12 sm:w-[56rem] bg-white rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

        <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-500 to-emerald-600 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white" id="modalProductoTitulo">Agregar producto</h3>
            </div>
            <button type="button" class="p-2 rounded hover:bg-white/20 transition" data-modal-close="modalProducto">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="formProducto" class="flex-1 overflow-y-auto">
            @csrf
            <input type="hidden" id="prod_id" name="prod_id" value="">

            <div class="px-6 py-5 space-y-5">

                <!-- Fila 1: Código y Categoría -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="prod_codigo" class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
                        <input 
                            type="text" 
                            id="prod_codigo" 
                            name="prod_codigo" 
                            maxlength="50"
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm font-mono"
                            placeholder="Generado automáticamente"
                        >
                        <p class="text-xs text-gray-500 mt-1">Se genera automáticamente si está vacío</p>
                    </div>

                    <div>
                        <label for="tprod_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select 
                            id="tprod_id" 
                            name="tprod_id"
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        >
                            <option value="">-- Selecciona una categoría --</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->tprod_id }}">{{ $tipo->tprod_nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fila 2: Nombre -->
                <div>
                    <label for="prod_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto</label>
                    <input 
                        type="text" 
                        id="prod_nombre" 
                        name="prod_nombre" 
                        maxlength="200"
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Ej. Coca Cola 500ml, Galletas Oreo..."
                    >
                </div>

                <!-- Fila 3: Descripción -->
                <div>
                    <label for="prod_descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea 
                        id="prod_descripcion" 
                        name="prod_descripcion" 
                        rows="2"
                        maxlength="200"
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        placeholder="Descripción opcional del producto"
                    ></textarea>
                </div>

                <!-- Fila 4: Precios -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="prod_precio_compra" class="block text-sm font-medium text-gray-700 mb-1">Precio de Compra</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Q</span>
                            <input 
                                type="number" 
                                id="prod_precio_compra" 
                                name="prod_precio_compra" 
                                step="0.01"
                                min="0"
                                class="w-full pl-8 rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                                placeholder="0.00"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="prod_precio_venta" class="block text-sm font-medium text-gray-700 mb-1">Precio de Venta</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Q</span>
                            <input 
                                type="number" 
                                id="prod_precio_venta" 
                                name="prod_precio_venta" 
                                step="0.01"
                                min="0"
                                class="w-full pl-8 rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                                placeholder="0.00"
                            >
                        </div>
                        <div id="margenGanancia" class="text-xs mt-1"></div>
                    </div>
                </div>

                <!-- Fila 5: Stock -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="prod_stock_minimo" class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                        <input 
                            type="number" 
                            id="prod_stock_minimo" 
                            name="prod_stock_minimo" 
                            min="0"
                            value="5"
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        >
                        <p class="text-xs text-gray-500 mt-1">Alerta cuando llegue a este nivel</p>
                    </div>

                    <div>
                        <label for="prod_stock_actual" class="block text-sm font-medium text-gray-700 mb-1">Stock Inicial</label>
                        <input 
                            type="number" 
                            id="prod_stock_actual" 
                            name="prod_stock_actual" 
                            min="0"
                            value="0"
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-400 focus:ring-emerald-400 text-sm"
                        >
                    </div>
                </div>

                <!-- Fila 6: Imagen -->
                <div>
                    <label for="prod_imagen" class="block text-sm font-medium text-gray-700 mb-1">Imagen del Producto</label>
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <input 
                                type="file" 
                                id="prod_imagen" 
                                name="prod_imagen" 
                                accept="image/*"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer"
                            >
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG. Max: 2MB</p>
                        </div>
                        
                        <div id="previewImagen" class="hidden w-20 h-20 rounded-lg overflow-hidden border-2 border-gray-200">
                            <img id="imagenPreview" src="" alt="Preview" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-5 border-t bg-gradient-to-r from-gray-50 to-gray-100 flex items-center justify-end gap-3 flex-shrink-0">
                <button
                    type="button"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium bg-white hover:bg-gray-100 hover:text-gray-900 transition-all duration-200 shadow-sm hover:shadow"
                    data-modal-close="modalProducto">
                    <i class="fas fa-times mr-2 text-gray-500"></i>Cancelar
                </button>

                <button
                    type="submit"
                    id="btnGuardarProducto"
                    class="px-5 py-2.5 rounded-xl font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-400 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2 text-white/90"></i>Guardar
                </button>

                <button
                    type="button"
                    id="btnActualizarProducto"
                    class="hidden px-5 py-2.5 rounded-xl font-semibold text-white bg-sky-600 hover:bg-sky-700 focus:ring-2 focus:ring-sky-400 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-sync-alt mr-2 text-white/90"></i>Actualizar
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@vite('resources/js/productos/crear.js')