import Swal from "sweetalert2";
import { Loader } from "../app";

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

// Estado
let filtrosActuales = {
    fecha_desde: '',
    fecha_hasta: '',
    categoria: '',
    tipo_reporte: 'stock_clasificado'
};

// Elementos DOM
const fechaDesde = document.getElementById("fecha_desde");
const fechaHasta = document.getElementById("fecha_hasta");
const filtroCategoria = document.getElementById("filtro_categoria");
const filtroTipo = document.getElementById("filtro_tipo");
const btnAplicarFiltros = document.getElementById("btnAplicarFiltros");
const btnExportar = document.getElementById("btnExportar");

// Elementos de estadísticas
const statTotalProductos = document.getElementById("stat-total-productos");
const statStockBajo = document.getElementById("stat-stock-bajo");
const statSinStock = document.getElementById("stat-sin-stock");
const statMovimientosHoy = document.getElementById("stat-movimientos-hoy");

// Elementos de contadores
const contadorStockBajo = document.getElementById("contador-stock-bajo");
const contadorMovimientos = document.getElementById("contador-movimientos");
const contadorMasVendidos = document.getElementById("contador-mas-vendidos");
const contadorSinStock = document.getElementById("contador-sin-stock");

// Elementos de listas
const listaStockBajo = document.getElementById("lista-stock-bajo");
const listaMovimientos = document.getElementById("lista-movimientos");
const listaMasVendidos = document.getElementById("lista-mas-vendidos");
const listaSinStock = document.getElementById("lista-sin-stock");

// ========================= SCANNER =========================
let scanBuffer = "";
let lastKeyTime = 0;
const SCAN_GAP_MS = 50;
const SCAN_MIN_LEN = 6;

const limpiarCodigoBarras = (codigo) => {
    const codigoLimpio = codigo.trim().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    if (codigoLimpio.startsWith('PROD') && codigoLimpio.length > 4) {
        return `PROD-${codigoLimpio.substring(4)}`;
    }
    return codigoLimpio;
};

const setScanHUD = (() => {
    let node = null, hideT = null;
    return (text, type = "info") => {
        if (!node) {
            node = document.createElement("div");
            node.style.cssText = "position:fixed;top:12px;right:12px;z-index:99999;padding:8px 12px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.12);font:600 12px/1.2 system-ui";
            document.body.appendChild(node);
        }
        const styles = {
            info: { bg: "#eff6ff", fg: "#1d4ed8", bd: "#bfdbfe" },
            ok: { bg: "#ecfdf5", fg: "#047857", bd: "#a7f3d0" },
            warn: { bg: "#fffbeb", fg: "#b45309", bd: "#fde68a" },
            error: { bg: "#fef2f2", fg: "#b91c1c", bd: "#fecaca" }
        }[type] || { bg: "#eef2ff", fg: "#4338ca", bd: "#c7d2fe" };

        node.textContent = text;
        node.style.background = styles.bg;
        node.style.color = styles.fg;
        node.style.border = `1px solid ${styles.bd}`;

        clearTimeout(hideT);
        hideT = setTimeout(() => { node.remove(); node = null; }, 1500);
    };
})();

document.addEventListener("keydown", (e) => {
    const tag = (e.target?.tagName || "").toUpperCase();
    if ((tag === "INPUT" || tag === "TEXTAREA") && e.key !== "Enter") return;

    const now = Date.now();
    if (now - lastKeyTime > SCAN_GAP_MS) scanBuffer = "";
    lastKeyTime = now;

    if (e.key === "Enter") {
        const codigo = scanBuffer.trim();
        scanBuffer = "";
        if (codigo.length >= SCAN_MIN_LEN) {
            e.preventDefault();
            setScanHUD("Escaneando...", "info");
            manejarCodigoEscaneadoReportes(codigo);
        }
        return;
    }

    if (e.key.length === 1) {
        const ch = (e.key === '–' || e.key === '—' || e.key === '−') ? '-' : e.key;
        scanBuffer += ch;
    }
});

async function manejarCodigoEscaneadoReportes(codigo) {
    try {
        const codigoLimpio = limpiarCodigoBarras(codigo);
        if (codigoLimpio.length >= SCAN_MIN_LEN) {
            await buscarProductoPorCodigo(codigoLimpio);
        } else {
            setScanHUD("Código muy corto", "error");
        }
    } catch (err) {
        console.error("Error en scanner:", err);
        setScanHUD("Error al escanear", "error");
    }
}

// ========================= INICIALIZACIÓN =========================
const inicializarReportes = () => {
    const hoy = new Date();
    const hace30Dias = new Date();
    hace30Dias.setDate(hoy.getDate() - 30);

    fechaDesde.value = hace30Dias.toISOString().split('T')[0];
    fechaHasta.value = hoy.toISOString().split('T')[0];

    filtrosActuales.fecha_desde = fechaDesde.value;
    filtrosActuales.fecha_hasta = fechaHasta.value;

    cargarEstadisticas();
    cargarReporteStockClasificado();
    configurarEventListeners();
};

const configurarEventListeners = () => {
    btnAplicarFiltros?.addEventListener("click", aplicarFiltros);
    btnExportar?.addEventListener("click", exportarReporte);
    filtroTipo?.addEventListener("change", (e) => cambiarTipoReporte(e.target.value));
    fechaDesde?.addEventListener("change", (e) => {
        if (fechaHasta.value && e.target.value > fechaHasta.value) {
            fechaHasta.value = e.target.value;
        }
    });
};

const buscarProductoPorCodigo = async (codigo) => {
    try {
        const response = await fetch("/productos/reportes/buscar-codigo", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ codigo }),
        });

        const data = await response.json();

        if (data.success && data.encontrado) {
            setScanHUD(`✅ ${data.producto.prod_nombre}`, "ok");
            await verHistorialProducto(data.producto.prod_id);
        } else {
            setScanHUD("❌ Producto no encontrado", "error");
            Swal.fire({
                title: "Producto no encontrado",
                text: "El código escaneado no existe en el sistema",
                icon: "info",
                confirmButtonColor: "#3b82f6",
            });
        }
    } catch (error) {
        console.error("Error en búsqueda:", error);
        setScanHUD("❌ Error de conexión", "error");
    }
};

// ========================= FILTROS =========================
const aplicarFiltros = () => {
    filtrosActuales = {
        fecha_desde: fechaDesde.value,
        fecha_hasta: fechaHasta.value,
        categoria: filtroCategoria.value,
        tipo_reporte: filtroTipo.value
    };

    recargarReporteActual();

    Swal.fire({
        icon: "success",
        title: "Filtros aplicados",
        timer: 1500,
        showConfirmButton: false
    });
};

const recargarReporteActual = () => {
    const tipo = filtrosActuales.tipo_reporte;
    switch (tipo) {
        case 'stock_clasificado': cargarReporteStockClasificado(); break;
        case 'movimientos': cargarReporteMovimientos(); break;
        case 'mas_vendidos': cargarReporteMasVendidos(); break;
        case 'sin_stock': cargarReporteSinStock(); break;
        default: cargarReporteStockClasificado();
    }
};

const cambiarTipoReporte = (tipo) => {
    document.querySelectorAll('.reporte-contenido').forEach(r => r.classList.add('hidden'));

    const idMap = {
        stock_clasificado: 'reporte-stock-bajo',
        movimientos: 'reporte-movimientos',
        mas_vendidos: 'reporte-mas-vendidos',
        sin_stock: 'reporte-sin-stock',
    };

    const reporteSeleccionado = document.getElementById(idMap[tipo] || 'reporte-stock-bajo');
    if (reporteSeleccionado) reporteSeleccionado.classList.remove('hidden');

    recargarReporteActual();
};

// ========================= CARGAR DATOS =========================
const cargarEstadisticas = async () => {
    try {
        const r = await fetch(`/productos/reportes/estadisticas`, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const j = await r.json();

        if (j.success) {
            if (statTotalProductos) statTotalProductos.textContent = j.estadisticas.total_productos;
            if (statStockBajo) statStockBajo.textContent = j.estadisticas.stock_bajo;
            if (statSinStock) statSinStock.textContent = j.estadisticas.sin_stock;
            if (statMovimientosHoy) statMovimientosHoy.textContent = j.estadisticas.movimientos_hoy;
        }
    } catch (error) {
        console.error("Error cargando estadísticas:", error);
    }
};

const cargarReporteStockClasificado = async () => {
    try {
        Loader.show("Analizando niveles de stock...");

        const params = new URLSearchParams(filtrosActuales);
        const r = await fetch(`/productos/reportes/stock-clasificado?${params}`, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const j = await r.json();

        if (j.success) {
            renderizarStockClasificado(j);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire("Error", "No se pudo cargar el análisis de stock", "error");
    } finally {
        Loader.hide();
    }
};

const renderizarStockClasificado = (data) => {
    if (!listaStockBajo) return;

    listaStockBajo.innerHTML = '';

    if (contadorStockBajo) {
        contadorStockBajo.textContent = `${data.total_general} productos analizados`;
    }

    const { productos } = data;
    let html = '';

    const secciones = [
        { key: 'criticos', titulo: 'CRÍTICO - Sin Stock', color: 'red', icon: '🔴' },
        { key: 'bajos', titulo: 'STOCK BAJO', color: 'amber', icon: '🟡' },
        { key: 'medios', titulo: 'STOCK MEDIO', color: 'blue', icon: '🔵' },
        { key: 'altos', titulo: 'STOCK ALTO', color: 'green', icon: '🟢' }
    ];

    secciones.forEach(seccion => {
        if (productos[seccion.key] && productos[seccion.key].length > 0) {
            html += `
                <div class="mb-6">
                    <h4 class="font-bold text-${seccion.color}-600 text-sm mb-3 flex items-center gap-2">
                        ${seccion.icon} ${seccion.titulo} (${productos[seccion.key].length} productos)
                    </h4>
                    ${productos[seccion.key].map(p => crearTarjetaProducto(p, seccion.key)).join('')}
                </div>
            `;
        }
    });

    listaStockBajo.innerHTML = html || '<div class="text-center py-8 text-gray-500">No hay productos para analizar</div>';
};

const crearTarjetaProducto = (producto, nivel) => {
    const config = {
        criticos: { color: 'red', texto: 'CRÍTICO' },
        bajos: { color: 'amber', texto: 'BAJO' },
        medios: { color: 'blue', texto: 'MEDIO' },
        altos: { color: 'green', texto: 'ALTO' }
    };

    const { color, texto } = config[nivel];
    const tieneImagen = producto.prod_imagen && producto.prod_imagen.trim() !== '';

    return `
        <div class="border-${color}-300 bg-${color}-50 hover:border-${color}-400 p-4 rounded-lg border-2 transition cursor-pointer mb-2" 
             onclick="verHistorialProducto(${producto.prod_id})">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 flex-1">
                    <div class="flex-shrink-0">
                        ${tieneImagen ? `<img src="/storage/${producto.prod_imagen}" alt="${producto.prod_nombre}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">` : '<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">📦</div>'}
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800 text-sm">${producto.prod_nombre || 'Sin nombre'}</h4>
                        <p class="text-xs text-gray-500">${producto.tipo?.tprod_nombre || 'Sin categoría'} • ${producto.prod_codigo || 'Sin código'}</p>
                        <div class="flex items-center gap-4 text-xs mt-1">
                            <span class="text-gray-600">Stock: <strong class="text-${color}-600">${producto.prod_stock_actual}</strong></span>
                            <span class="text-gray-600">Mínimo: <strong>${producto.prod_stock_minimo}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-gray-800">Q${parseFloat(producto.prod_precio_venta || 0).toFixed(2)}</div>
                    <div class="text-xs text-blue-600 hover:text-blue-800 mt-1">Ver historial</div>
                </div>
            </div>
        </div>
    `;
};

// ========================= REPORTES ESPECÍFICOS =========================
const cargarReporteMasVendidos = async () => {
    try {
        Loader.show("Cargando productos más vendidos...");

        const params = new URLSearchParams(filtrosActuales);
        const r = await fetch(`/productos/reportes/mas-vendidos?${params}`, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const j = await r.json();

        if (j.success) {
            renderizarMasVendidos(j.mas_vendidos, j.total);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire("Error", "No se pudo cargar los productos más vendidos", "error");
    } finally {
        Loader.hide();
    }
};

const renderizarMasVendidos = (masVendidos, total) => {
    if (!listaMasVendidos) return;

    listaMasVendidos.innerHTML = '';

    if (contadorMasVendidos) {
        contadorMasVendidos.textContent = `${total || 0} productos`;
    }

    if (!masVendidos || masVendidos.length === 0) {
        listaMasVendidos.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <p class="text-lg font-medium mb-2">No hay datos de ventas</p>
                <p class="text-sm">No se encontraron productos vendidos en el período seleccionado</p>
            </div>
        `;
        return;
    }

    const masVendidosHTML = masVendidos.map((item, index) => {
        const producto = item.producto;
        const puesto = index + 1;
        const emoji = puesto <= 3 ? ['🥇', '🥈', '🥉'][puesto - 1] : `#${puesto}`;

        if (!producto) {
            return `
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="text-2xl font-bold text-gray-300 w-8 text-center">${emoji}</div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800 text-sm">Producto no disponible</h4>
                                <p class="text-xs text-gray-500">ID: ${item.prod_id}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">${item.total_vendido || 0} unidades</div>
                            <div class="text-xs text-gray-500">vendidas</div>
                        </div>
                    </div>
                </div>
            `;
        }

        return `
            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition mb-2 cursor-pointer" onclick="verHistorialProducto(${producto.prod_id})">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="text-2xl font-bold text-gray-300 w-8 text-center">${emoji}</div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 text-sm">${producto.prod_nombre || 'Producto sin nombre'}</h4>
                            <p class="text-xs text-gray-500">${producto.tipo?.tprod_nombre || 'Sin categoría'} • ${producto.prod_codigo || 'Sin código'}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600">${item.total_vendido || 0} unidades</div>
                        <div class="text-xs text-gray-500">${item.num_ventas || 0} ventas</div>
                        <div class="text-xs text-blue-600 font-semibold">Q${parseFloat(item.total_ingresos || 0).toFixed(2)}</div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-2 text-xs text-gray-600">
                    <span>Stock actual: ${producto.prod_stock_actual || 0}</span>
                    <span>Precio: Q${parseFloat(producto.prod_precio_venta || 0).toFixed(2)}</span>
                </div>
            </div>
        `;
    }).join('');

    listaMasVendidos.innerHTML = masVendidosHTML;
};

const cargarReporteMovimientos = async () => {
    try {
        Loader.show("Cargando movimientos...");

        const params = new URLSearchParams(filtrosActuales);
        const r = await fetch(`/productos/reportes/movimientos?${params}`, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const j = await r.json();

        if (j.success) {
            renderizarMovimientos(j.movimientos, j.total);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire("Error", "No se pudo cargar los movimientos", "error");
    } finally {
        Loader.hide();
    }
};

const renderizarMovimientos = (movimientos, total) => {
    if (!listaMovimientos) return;

    listaMovimientos.innerHTML = '';

    if (contadorMovimientos) {
        contadorMovimientos.textContent = `${total || 0} movimientos`;
    }

    if (!movimientos || movimientos.length === 0) {
        listaMovimientos.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <p class="text-lg font-medium mb-2">No hay movimientos registrados</p>
                <p class="text-sm">No se encontraron movimientos en el período seleccionado</p>
            </div>
        `;
        return;
    }

    const movimientosHTML = movimientos.map(mov => {
        const esEntrada = mov.mov_tipo === 'Entrada';
        const bordeColor = esEntrada ? 'border-green-300' : 'border-red-300';
        const bgColor = esEntrada ? 'bg-green-50' : 'bg-red-50';
        const nombreProducto = mov.producto?.prod_nombre || 'Producto no encontrado';

        return `
            <div class="${bordeColor} ${bgColor} p-4 rounded-lg border-l-4 hover:shadow-md transition mb-2">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-gray-800 text-sm">${nombreProducto}</h4>
                            <span class="text-xs px-2 py-1 rounded-full ${esEntrada ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${mov.mov_tipo}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mb-2">${mov.mov_motivo || 'Sin motivo'}</p>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="text-gray-500">${new Date(mov.created_at).toLocaleString()}</span>
                            <span class="${esEntrada ? 'text-green-600' : 'text-red-600'} font-semibold">${mov.mov_cantidad} unidades</span>
                            <span class="text-gray-600">Stock: ${mov.mov_stock_anterior} → ${mov.mov_stock_nuevo}</span>
                        </div>
                    </div>
                </div>
                ${mov.mov_observacion ? `<div class="text-xs text-gray-500 mt-2">📝 ${mov.mov_observacion}</div>` : ''}
            </div>
        `;
    }).join('');

    listaMovimientos.innerHTML = movimientosHTML;
};

const cargarReporteSinStock = async () => {
    try {
        Loader.show("Cargando productos sin stock...");

        const params = new URLSearchParams(filtrosActuales);
        const r = await fetch(`/productos/reportes/sin-stock?${params}`, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const j = await r.json();

        if (j.success) {
            renderizarSinStock(j.productos, j.total);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire("Error", "No se pudo cargar los productos sin stock", "error");
    } finally {
        Loader.hide();
    }
};

const renderizarSinStock = (productos, total) => {
    if (!listaSinStock) return;

    listaSinStock.innerHTML = '';

    if (contadorSinStock) {
        contadorSinStock.textContent = `${total || 0} productos`;
    }

    if (!productos || productos.length === 0) {
        listaSinStock.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-lg font-medium mb-2">¡Excelente gestión de stock!</p>
                <p class="text-sm">No hay productos sin stock</p>
            </div>
        `;
        return;
    }

    const sinStockHTML = productos.map(producto => {
        const tieneImagen = producto.prod_imagen && producto.prod_imagen.trim() !== '';

        return `
            <div class="bg-red-50 p-4 rounded-lg border border-red-200 hover:shadow-md transition mb-2 cursor-pointer" onclick="verHistorialProducto(${producto.prod_id})">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="flex-shrink-0">
                            ${tieneImagen ? `<img src="/storage/${producto.prod_imagen}" alt="${producto.prod_nombre}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">` : '<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">📦</div>'}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-red-500">🔴</span>
                                <h4 class="font-semibold text-gray-800 text-sm">${producto.prod_nombre || 'Sin nombre'}</h4>
                            </div>
                            <p class="text-xs text-gray-500">${producto.tipo?.tprod_nombre || 'Sin categoría'} • ${producto.prod_codigo || 'Sin código'}</p>
                            <div class="text-xs text-red-600 font-semibold mt-2">🚨 STOCK AGOTADO - Reponer urgentemente</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-800">Q${parseFloat(producto.prod_precio_venta || 0).toFixed(2)}</div>
                        <div class="text-xs text-blue-600 hover:text-blue-800 mt-1">Ver historial</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    listaSinStock.innerHTML = sinStockHTML;
};

// ========================= HISTORIAL DEL PRODUCTO =========================
window.verHistorialProducto = async (prodId) => {
    try {
        Loader.show("Cargando historial completo...");

        const params = new URLSearchParams({
            prod_id: prodId,
            fecha_desde: filtrosActuales.fecha_desde,
            fecha_hasta: filtrosActuales.fecha_hasta
        });

        const r = await fetch(`/productos/reportes/historial-producto?${params}`, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const j = await r.json();

        if (j.success) {
            mostrarModalHistorialCompleto(j);
        } else {
            Swal.fire("Error", "No se pudo cargar el historial del producto", "error");
        }
    } catch (error) {
        console.error("Error cargando historial:", error);
        Swal.fire("Error", "No se pudo cargar el historial", "error");
    } finally {
        Loader.hide();
    }
};

const mostrarModalHistorialCompleto = (data) => {
    const { producto, movimientos, ventas, estadisticas } = data;

    // Crear tabs para separar movimientos y ventas
    const tabsHTML = `
        <div class="border-b border-gray-200 mb-4">
            <nav class="flex -mb-px">
                <button onclick="switchTab('ventas')" id="tab-ventas" class="tab-button active px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                    📊 Ventas (${ventas?.length || 0})
                </button>
                <button onclick="switchTab('movimientos')" id="tab-movimientos" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    📦 Movimientos (${movimientos?.length || 0})
                </button>
                <button onclick="switchTab('estadisticas')" id="tab-estadisticas" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    📈 Estadísticas
                </button>
            </nav>
        </div>
    `;

    // Contenido de ventas
    const ventasHTML = ventas && ventas.length > 0 ? ventas.map(v => `
        <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-100 text-blue-800">
                        Venta #${v.ven_id}
                    </span>
                    <span class="text-sm font-semibold text-blue-600">
                        ${v.vendet_cantidad} unidades
                    </span>
                </div>
                <div class="text-xs text-gray-600 mb-1">
                    ${v.venta?.usuario?.name || 'Usuario desconocido'} • 
                    ${new Date(v.created_at).toLocaleString()}
                </div>
                <div class="text-xs text-gray-500">
                    Precio: Q${parseFloat(v.vendet_precio).toFixed(2)} • 
                    Total: Q${parseFloat(v.vendet_total).toFixed(2)}
                </div>
            </div>
        </div>
    `).join('') : '<p class="text-center text-gray-500 py-8">No hay ventas registradas en este período</p>';

    const movimientosHTML = movimientos && movimientos.length > 0 ? movimientos.map(mov => `
        <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-semibold px-2 py-1 rounded ${mov.mov_tipo === 'Entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${mov.mov_tipo}
                    </span>
                    <span class="text-sm font-semibold ${mov.mov_tipo === 'Entrada' ? 'text-green-600' : 'text-red-600'}">
                        ${mov.mov_cantidad} unidades
                    </span>
                </div>
                <div class="text-xs text-gray-600 mb-1">${mov.mov_motivo}</div>
                <div class="text-xs text-gray-400">${new Date(mov.created_at).toLocaleString()}</div>
            </div>
            <div class="text-right text-sm">
                <div class="font-mono text-gray-700">${mov.mov_stock_anterior} → ${mov.mov_stock_nuevo}</div>
                ${mov.mov_observacion ? `<div class="text-xs text-gray-500 mt-1">${mov.mov_observacion}</div>` : ''}
            </div>
        </div>
    `).join('') : '<p class="text-center text-gray-500 py-8">No hay movimientos registrados en este período</p>';

    // Estadísticas
    const estadisticasHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${estadisticas.total_vendido || 0}</div>
                <div class="text-xs text-gray-600">Unidades vendidas</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">Q${parseFloat(estadisticas.ingresos_generados || 0).toFixed(2)}</div>
                <div class="text-xs text-gray-600">Ingresos generados</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">${estadisticas.num_ventas || 0}</div>
                <div class="text-xs text-gray-600">Número de ventas</div>
            </div>
            <div class="bg-amber-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-amber-600">${estadisticas.entradas_inventario || 0}</div>
                <div class="text-xs text-gray-600">Entradas de inventario</div>
            </div>
        </div>
    `;

    const tieneImagen = producto.prod_imagen && producto.prod_imagen.trim() !== '';

    Swal.fire({
        title: `📊 Historial Completo: ${producto.prod_nombre}`,
        html: `
            <div class="text-left">
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <div class="flex items-center gap-3">
                        ${tieneImagen ? `<img src="/storage/${producto.prod_imagen}" alt="${producto.prod_nombre}" class="w-16 h-16 rounded-lg object-cover border border-gray-200">` : '<div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-2xl">📦</div>'}
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-semibold">Código: ${producto.prod_codigo}</span>
                                <span class="text-sm text-gray-600">${producto.tipo?.tprod_nombre || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-semibold ${producto.prod_stock_actual === 0 ? 'text-red-600' : producto.prod_stock_actual <= producto.prod_stock_minimo ? 'text-amber-600' : 'text-green-600'}">
                                    Stock actual: ${producto.prod_stock_actual}
                                </span>
                                <span class="text-sm text-gray-600">Mínimo: ${producto.prod_stock_minimo}</span>
                            </div>
                        </div>
                    </div>
                </div>

                ${tabsHTML}

                <div id="content-ventas" class="tab-content max-h-96 overflow-y-auto">
                    ${ventasHTML}
                </div>

                <div id="content-movimientos" class="tab-content hidden max-h-96 overflow-y-auto">
                    ${movimientosHTML}
                </div>

                <div id="content-estadisticas" class="tab-content hidden">
                    ${estadisticasHTML}
                </div>
            </div>
        `,
        width: 800,
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            popup: 'historial-modal'
        }
    });
};

// Función global para cambiar tabs
window.switchTab = (tab) => {
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    document.getElementById(`tab-${tab}`).classList.add('active', 'border-blue-500', 'text-blue-600');
    document.getElementById(`tab-${tab}`).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(`content-${tab}`).classList.remove('hidden');
};

// ========================= EXPORTAR =========================
const exportarReporte = async () => {
    try {
        const params = new URLSearchParams({
            ...filtrosActuales,
            formato: 'pdf'
        });

        const url = `/productos/reportes/exportar-stock?${params}`;
        window.open(url, '_blank');

    } catch (error) {
        console.error("Error exportando:", error);
        Swal.fire("Error", "No se pudo exportar el reporte", "error");
    }
};

// ========================= INICIALIZAR =========================
document.addEventListener("DOMContentLoaded", () => {
    inicializarReportes();
});

window.recargarReportes = recargarReporteActual;
window.aplicarFiltrosReportes = aplicarFiltros;
window.exportarReporte = exportarReporte;