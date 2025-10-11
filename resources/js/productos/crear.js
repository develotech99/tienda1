import Swal from "sweetalert2";
import { closeModal, Loader, setBtnLoading } from "../app";

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

// ==== helpers de escape seguros ====
const escapeHtmlAttr = (str) =>
    String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

const escapeJsSingleArg = (str) => {
    let s = String(str ?? "");
    s = s.replace(new RegExp("\\\\", "g"), "\\\\");
    s = s.replace(new RegExp("'", "g"), "\\'");
    s = s.replace(/\r/g, "\\r").replace(/\n/g, "\\n");
    return s;
};

// Estado
let productosData = [];
let categoriaActual = "";
let productoActual = null;

// Elementos
const scannerInput = document.getElementById("scannerInput");
const scannerStatus = document.getElementById("scannerStatus");
const productosGrid = document.getElementById("productosGrid");
const emptyState = document.getElementById("emptyState");
const searchBox = document.getElementById("searchBox");

// feedback escáner
const scannerNombreWrap = document.getElementById("scannerNombreWrap");
const scannerNombre = document.getElementById("scannerNombre");
const scannerBtnImprimir = document.getElementById("scannerBtnImprimir");
const scannerBtnEditar = document.getElementById("scannerBtnEditar");

// ========================= ESCÁNER CON BÚSQUEDA DINÁMICA =========================
const manejarEscaneo = (e) => {

    if (e.target.id === "scannerInput") {
        const codigo = scannerInput.value.trim().replace(/[\r\n]/g, '');

        console.log("Key pressed:", e.key, "Code:", codigo);

        if (e.key === "Enter") {
            e.preventDefault();
            e.stopPropagation();

            if (codigo) {
                console.log("Buscando producto con código:", codigo);
                buscarProductoPorCodigo(codigo);
            }
        } else {
            if (codigo.length >= 3) {
                busquedaDinamicaScanner(codigo);
            } else {
                ocultarSugerenciasScanner();
            }
        }
    }
};

// Búsqueda dinámica en tiempo real
const busquedaDinamicaScanner = (codigo) => {
    const coincidencias = productosData.filter(p =>
        p.prod_codigo && p.prod_codigo.toLowerCase().includes(codigo.toLowerCase())
    ).slice(0, 5); // Máximo 5 sugerencias

    if (coincidencias.length > 0) {
        mostrarSugerenciasScanner(coincidencias);
    } else {
        ocultarSugerenciasScanner();
    }
};

const mostrarSugerenciasScanner = (productos) => {
    let sugerenciasDiv = document.getElementById("scanner-sugerencias");

    if (!sugerenciasDiv) {
        sugerenciasDiv = document.createElement("div");
        sugerenciasDiv.id = "scanner-sugerencias";
        sugerenciasDiv.className = "absolute z-50 w-full mt-2 bg-white rounded-xl shadow-2xl border-2 border-blue-300 max-h-80 overflow-y-auto";
        scannerInput.parentElement.style.position = "relative";
        scannerInput.parentElement.appendChild(sugerenciasDiv);
    }

    sugerenciasDiv.innerHTML = productos.map(p => `
        <div class="sugerencia-item px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 transition-colors" data-codigo="${p.prod_codigo}">
            <div class="flex items-center gap-3">
                <img src="${p.prod_imagen ? `/storage/${p.prod_imagen}` : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40"%3E%3Crect fill="%23e5e7eb" width="100%25" height="100%25"/%3E%3C/svg%3E'}" 
                     class="w-10 h-10 object-cover rounded-lg border border-gray-200">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-gray-800 truncate">${p.prod_nombre}</p>
                    <p class="text-xs font-mono text-blue-600">${p.prod_codigo}</p>
                </div>
                <span class="text-sm font-bold text-emerald-600">Q${parseFloat(p.prod_precio_venta || 0).toFixed(2)}</span>
            </div>
        </div>
    `).join('');

    // Event listener para seleccionar sugerencia
    sugerenciasDiv.querySelectorAll('.sugerencia-item').forEach(item => {
        item.addEventListener('click', () => {
            const codigo = item.dataset.codigo;
            scannerInput.value = codigo;
            buscarProductoPorCodigo(codigo);
            ocultarSugerenciasScanner();
        });
    });
};

const ocultarSugerenciasScanner = () => {
    const sugerenciasDiv = document.getElementById("scanner-sugerencias");
    if (sugerenciasDiv) {
        sugerenciasDiv.remove();
    }
};

// Ocultar sugerencias al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!e.target.closest('#scannerInput') && !e.target.closest('#scanner-sugerencias')) {
        ocultarSugerenciasScanner();
    }
});

const buscarProductoPorCodigo = async (codigo) => {
    // Limpiar el código nuevamente por si acaso
    codigo = codigo.trim().replace(/[\r\n]/g, '');

    console.log("=== INICIANDO BÚSQUEDA ==="); // DEBUG
    console.log("Código recibido:", codigo); // DEBUG
    console.log("Longitud:", codigo.length); // DEBUG

    if (!codigo) {
        console.log("Código vacío - abortando"); // DEBUG
        scannerStatus.textContent = "● Código vacío";
        scannerStatus.className = "text-sm font-semibold text-red-600";
        return;
    }

    scannerStatus.textContent = "● Buscando...";
    scannerStatus.className = "text-sm font-semibold text-amber-600";

    try {
        console.log("Haciendo fetch a /productos/buscar-codigo"); // DEBUG

        const r = await fetch("/productos/buscar-codigo", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({ codigo }),
        });

        console.log("Respuesta recibida, status:", r.status); // DEBUG

        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }

        const j = await r.json();
        console.log("JSON respuesta:", j); // DEBUG

        if (j.success && j.encontrado) {
            console.log("Producto ENCONTRADO - abriendo modal"); // DEBUG
            const p = j.producto;
            scannerStatus.textContent = "● Producto encontrado";
            scannerStatus.className = "text-sm font-semibold text-green-600";

            scannerNombreWrap?.classList.remove("hidden");
            scannerNombre.textContent = p.prod_nombre || "(Sin nombre)";
            scannerBtnImprimir.classList.remove("hidden");
            scannerBtnEditar.classList.remove("hidden");
            scannerBtnImprimir.onclick = () => window.imprimirCodigoBarras(p.prod_codigo, p.prod_nombre);
            scannerBtnEditar.onclick = () => abrirModalActualizarStock(p);

            // ABRIR EL MODAL AUTOMÁTICAMENTE
            abrirModalActualizarStock(p);
        } else {
            console.log("Producto NO ENCONTRADO - preguntando crear"); // DEBUG
            scannerStatus.textContent = "● No encontrado";
            scannerStatus.className = "text-sm font-semibold text-red-600";

            // PREGUNTAR SI QUIERE CREAR EL PRODUCTO
            await preguntarCrearProducto(codigo);
            scannerNombreWrap?.classList.add("hidden");
        }
    } catch (e) {
        console.error("Error en búsqueda:", e);
        scannerStatus.textContent = "● Error de conexión";
        scannerStatus.className = "text-sm font-semibold text-red-600";

        await Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor",
            timer: 2000
        });
    } finally {
        scannerInput.value = "";
        setTimeout(() => {
            scannerStatus.textContent = "● Listo";
            scannerStatus.className = "text-sm font-semibold text-emerald-600";
            scannerInput.focus();
        }, 1800);
    }
};

const preguntarCrearProducto = async (codigo) => {
    const result = await Swal.fire({
        title: "Producto no encontrado",
        html: `<p class="text-gray-600 mb-2">El código <strong class="font-mono text-blue-600">${codigo}</strong> no existe.</p><p class="text-gray-600">¿Deseas crear un nuevo producto?</p>`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, crear",
        cancelButtonText: "No",
        confirmButtonColor: "#2563eb",
        cancelButtonColor: "#6b7280",
    });

    if (result.isConfirmed) {
        // ABRIR MODAL DE CREACIÓN
        abrirModalCrearProducto(codigo);
    }
};
// ========================= CARGA LISTA =========================
const cargarProductos = async (categoriaId = "") => {
    try {
        const r = await fetch("/productos/ObtenerDatosAPI" + (categoriaId ? `?categoria=${categoriaId}` : ""), {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });
        const j = await r.json();

        if (j.codigo == 1) {
            productosData = j.data || [];
            actualizarEstadisticas(j.estadisticas);
            renderizarProductos(productosData);
        } else {
            productosData = [];
            renderizarProductos([]);
        }
    } catch (e) {
        console.error(e);
        productosData = [];
        renderizarProductos([]);
    }
};

const actualizarEstadisticas = (s) => {
    if (!s) return;
    document.getElementById("total-productos").textContent = s.total || 0;
    document.getElementById("stat-stock-bajo").textContent = s.stock_bajo || 0;
    document.getElementById("stat-sin-stock").textContent = s.sin_stock || 0;
};

// ========================= RENDER CARDS MEJORADAS =========================
const renderizarProductos = (productos) => {
    productosGrid.innerHTML = "";

    if (!productos.length) {
        emptyState.classList.remove("hidden");
        productosGrid.classList.add("hidden");
        document.getElementById("productos-mostrados").textContent = "0 productos";
        return;
    }

    emptyState.classList.add("hidden");
    productosGrid.classList.remove("hidden");
    document.getElementById("productos-mostrados").textContent = `${productos.length} productos`;

    productos.forEach(p => productosGrid.appendChild(crearCardProducto(p)));
};

const crearCardProducto = (p) => {
    const div = document.createElement("div");

    // ====== datos calculados ======
    const stock = Number(p.prod_stock_actual || 0);
    const minimo = Number(p.prod_stock_minimo || 0);
    const categoria = (p?.tipo?.tprod_nombre || "Sin categoría");
    const nombre = (p.prod_nombre || "(Sin nombre)");
    const precio = Number(p.prod_precio_venta || 0);
    const codigo = (p.prod_codigo || "");
    const tieneCodigo = !!codigo;

    const img = p.prod_imagen
        ? `/storage/${p.prod_imagen}`
        : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="180" height="140"%3E%3Crect width="100%25" height="100%25" fill="%23f8fafc"/%3E%3Ctext x="50%25" y="52%25" text-anchor="middle" font-size="12" fill="%2394a3b8"%3ESin imagen%3C/text%3E%3C/svg%3E';

    const estadoClase =
        stock === 0 ? "bg-red-100 text-red-700" :
            stock <= minimo ? "bg-amber-100 text-amber-700" :
                "bg-emerald-100 text-emerald-700";

    const estadoTexto =
        stock === 0 ? "Agotado" :
            stock <= minimo ? "Stock bajo" :
                "En stock";

    // ====== CARD estilo profesional ======
    div.className = "group bg-white rounded-lg border border-gray-200 hover:border-emerald-400 hover:shadow-md transition-all duration-300 flex flex-col h-full overflow-hidden";

    div.innerHTML = `
    <!-- IMAGEN -->
    <div class="relative w-full h-32 bg-gray-50 flex items-center justify-center overflow-hidden p-2">
      <img
        src="${img}"
        alt="${nombre}"
        class="max-w-full max-h-full object-contain"
        onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'180\\' height=\\'140\\'%3E%3Crect width=\\'100%25\\' height=\\'100%25\\' fill=\\'%23f8fafc\\'/%3E%3Ctext x=\\'50%25\\' y=\\'52%25\\' text-anchor=\\'middle\\' font-size=\\'12\\' fill=\\'%2394a3b8\\'%3ESin imagen%3C/text%3E%3C/svg%3E'">
      
      ${!tieneCodigo ? `
        <span class="absolute top-1 left-1 text-[10px] font-bold px-1 py-0.5 rounded bg-amber-500 text-white">SIN CÓDIGO</span>
      ` : ""}
    </div>

    <!-- CONTENIDO -->
    <div class="p-2 flex flex-col gap-1 flex-1">
      <!-- CATEGORÍA Y ESTADO -->
      <div class="flex justify-between items-center mb-1">
        <span class="text-xs text-gray-500 font-medium">${categoria}</span>
        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded ${estadoClase}">
          ${estadoTexto}
        </span>
      </div>

      <!-- NOMBRE -->
      <h3 class="text-sm font-semibold text-gray-800 leading-tight line-clamp-2 min-h-[2.2rem] mb-1">
        ${nombre}
      </h3>

      <!-- STOCK -->
      <div class="flex justify-between items-center text-xs mb-1">
        <span class="text-gray-600">Stock:</span>
        <span class="font-bold ${stock === 0 ? 'text-red-600' : stock <= minimo ? 'text-amber-600' : 'text-gray-800'}">
          ${stock} un.
        </span>
      </div>

      <!-- PRECIO -->
      <div class="mb-2">
        <div class="text-emerald-700 font-black text-base">Q${precio.toFixed(2)}</div>
      </div>

      <!-- CÓDIGO Y ACCIONES -->
      <div class="flex items-center justify-between bg-gray-50 rounded px-1.5 py-1 mt-auto">
        <span class="text-[10px] font-mono text-gray-700 truncate flex-1 mr-1">
          ${tieneCodigo ? codigo : "Sin código"}
        </span>
        
        <div class="flex gap-0.5">
          <button
            type="button"
            class="p-1 rounded hover:bg-white transition"
            title="Imprimir etiqueta"
            data-action="imprimir-barcode"
            data-codigo="${codigo}"
            data-nombre="${nombre}"
            ${!tieneCodigo ? "disabled" : ""}>
            <svg class="w-3.5 h-3.5 ${tieneCodigo ? "text-gray-600 hover:text-blue-600" : "text-gray-300"}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
          </button>

          <button
            type="button"
            class="p-1 rounded hover:bg-white transition"
            title="Modificar producto"
            data-action="editar-producto"
            data-id="${p.prod_id}">
            <svg class="w-3.5 h-3.5 text-gray-600 hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  `;

    // Event listeners
    div.querySelector('[data-action="imprimir-barcode"]')?.addEventListener("click", (e) => {
        const btn = e.currentTarget;
        if (!btn.disabled) {
            window.imprimirCodigoBarras(btn.dataset.codigo, btn.dataset.nombre);
        }
    });

    div.querySelector('[data-action="editar-producto"]')?.addEventListener("click", (e) => {
        window.abrirModalStock(e.currentTarget.dataset.id);
    });

    return div;
};

// ========================= BÚSQUEDA MEJORADA (por nombre Y código) =========================
const buscarProductos = (t) => {
    const q = (t || "").toLowerCase().trim();

    if (!q) {
        // Si está vacío, mostrar todos los productos actuales
        renderizarProductos(productosData);
        return;
    }

    const arr = productosData.filter(p => {
        const nombre = (p.prod_nombre || "").toLowerCase();
        const codigo = (p.prod_codigo || "").toLowerCase();
        const categoria = (p?.tipo?.tprod_nombre || "").toLowerCase();

        return nombre.includes(q) ||
            codigo.includes(q) ||
            categoria.includes(q);
    });

    renderizarProductos(arr);
};

// ========================= FILTROS/BÚSQUEDA =========================
const filtrarPorCategoria = (id) => {
    categoriaActual = id;
    document.querySelectorAll(".categoria-btn").forEach(b => {
        b.className = (b.dataset.categoria === id)
            ? "categoria-btn w-full text-left px-4 py-3.5 rounded-xl transition-all duration-200 bg-emerald-50 border-2 border-emerald-500 text-emerald-800 font-semibold shadow-sm hover:shadow-md"
            : "categoria-btn w-full text-left px-4 py-3.5 rounded-xl transition-all duration-200 hover:bg-gray-50 border-2 border-transparent hover:border-emerald-300 hover:shadow-sm group";
    });

    // Limpiar búsqueda al cambiar categoría
    if (searchBox) searchBox.value = "";

    cargarProductos(id);
};

// ========================= MODAL GESTIONAR =========================
const abrirModalActualizarStock = (p) => {
    productoActual = p;

    // info
    document.getElementById("update_prod_id").value = p.prod_id;
    document.getElementById("update_prod_nombre").textContent = p.prod_nombre || "(Sin nombre)";
    document.getElementById("update_prod_codigo").textContent = p.prod_codigo || "Sin código";
    document.getElementById("update_prod_descripcion").textContent = p.prod_descripcion || "Sin descripción";
    document.getElementById("update_stock_actual").textContent = p.prod_stock_actual;
    document.getElementById("update_precio_venta").textContent = "Q" + parseFloat(p.prod_precio_venta || 0).toFixed(2);

    const imgSrc = p.prod_imagen
        ? `/storage/${p.prod_imagen}`
        : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect width="100%25" height="100%25" fill="%23e5e7eb"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-size="12" fill="%236b7280"%3ESin imagen%3C/text%3E%3C/svg%3E';

    document.getElementById("update_prod_imagen").src = imgSrc;

    // defaults
    document.getElementById("update_cantidad").value = 1;
    document.getElementById("update_motivo").value = "";
    document.getElementById("update_tipo_movimiento").value = "entrada";

    const btnDel = document.getElementById("btnEliminarProducto");
    if (btnDel) {
        btnDel.disabled = parseInt(p.prod_stock_actual || 0) !== 0;
        btnDel.classList.toggle("opacity-50", btnDel.disabled);
        btnDel.onclick = () => window.eliminarProducto(p.prod_id);
    }

    // editar
    llenarDatosEdicionQuick(p);

    // tabs
    cambiarTab("stock");
    document.querySelectorAll(".accion-btn").forEach(btn => {
        btn.className = (btn.dataset.accion === "entrada")
            ? "accion-btn px-5 py-4 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-700 font-bold transition hover:bg-emerald-100 hover:shadow-md flex items-center justify-center gap-2"
            : "accion-btn px-5 py-4 rounded-xl border-2 border-gray-300 bg-white text-gray-700 font-bold transition hover:bg-gray-50 hover:shadow-md flex items-center justify-center gap-2";
    });

    calcularPreviewStock();
    document.getElementById("modalActualizarStock").classList.remove("hidden");
};

const llenarDatosEdicionQuick = (p) => {
    document.getElementById("edit_quick_prod_id").value = p.prod_id;
    document.getElementById("edit_quick_prod_nombre").value = p.prod_nombre || "";
    document.getElementById("edit_quick_prod_descripcion").value = p.prod_descripcion || "";
    document.getElementById("edit_quick_prod_codigo").value = p.prod_codigo || "Sin código";
    document.getElementById("edit_quick_tprod_id").value = p.tprod_id;
    document.getElementById("edit_quick_prod_precio_compra").value = p.prod_precio_compra ?? "";
    document.getElementById("edit_quick_prod_precio_venta").value = p.prod_precio_venta ?? "";
    document.getElementById("edit_quick_prod_stock_minimo").value = p.prod_stock_minimo ?? 0;
    document.getElementById("edit_quick_prod_stock_actual").value = p.prod_stock_actual ?? 0;

    const prev = document.getElementById("edit_quick_preview");
    const imgSrc = p.prod_imagen
        ? `/storage/${p.prod_imagen}`
        : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect width="100%25" height="100%25" fill="%23e5e7eb"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-size="12" fill="%236b7280"%3ESin imagen%3C/text%3E%3C/svg%3E';

    prev.src = imgSrc;
};

const cambiarTab = (tab) => {
    const ts = document.getElementById("tabStock");
    const te = document.getElementById("tabEditar");
    const cs = document.getElementById("contenidoStock");
    const ce = document.getElementById("contenidoEditar");
    if (tab === "stock") {
        ts.className = "tab-btn flex-1 px-6 py-3 font-semibold text-emerald-600 border-b-2 border-emerald-600 transition";
        te.className = "tab-btn flex-1 px-6 py-3 font-semibold text-gray-500 hover:text-gray-700 transition";
        cs.classList.remove("hidden"); ce.classList.add("hidden");
    } else {
        te.className = "tab-btn flex-1 px-6 py-3 font-semibold text-blue-600 border-b-2 border-blue-600 transition";
        ts.className = "tab-btn flex-1 px-6 py-3 font-semibold text-gray-500 hover:text-gray-700 transition";
        ce.classList.remove("hidden"); cs.classList.add("hidden");
    }
};

const calcularPreviewStock = () => {
    const actual = parseInt(productoActual?.prod_stock_actual || 0);
    const cant = parseInt(document.getElementById("update_cantidad").value || 0);
    const tipo = document.getElementById("update_tipo_movimiento").value;
    const nuevo = tipo === "entrada" ? actual + cant : Math.max(0, actual - cant);
    document.getElementById("preview-nuevo-stock").textContent = nuevo;
};

const cambiarTipoMovimiento = (tipo) => {
    document.getElementById("update_tipo_movimiento").value = tipo;
    document.querySelectorAll(".accion-btn").forEach(btn => {
        if (btn.dataset.accion === tipo) {
            const color = tipo === "entrada" ? "border-emerald-500 bg-emerald-50 text-emerald-700" : "border-red-500 bg-red-50 text-red-700";
            btn.className = "accion-btn px-5 py-4 rounded-xl border-2 " + color + " font-bold transition flex items-center justify-center gap-2";
        } else {
            btn.className = "accion-btn px-5 py-4 rounded-xl border-2 border-gray-300 bg-white text-gray-700 font-bold transition hover:bg-gray-50 hover:shadow-md flex items-center justify-center gap-2";
        }
    });
    calcularPreviewStock();
};

const guardarMovimientoStock = async (e) => {
    e.preventDefault();
    const prodId = document.getElementById("update_prod_id").value;
    const cantidad = parseInt(document.getElementById("update_cantidad").value);
    const motivo = document.getElementById("update_motivo").value;
    const tipo = document.getElementById("update_tipo_movimiento").value;

    if (!cantidad || cantidad <= 0) {
        await Swal.fire({ icon: "warning", title: "Cantidad inválida", text: "Debe ingresar una cantidad válida" });
        return;
    }

    setBtnLoading(document.getElementById("btnConfirmarStock"), true, "Guardando...");
    Loader.show("Actualizando stock...");

    try {
        const r = await fetch(`/productos/${prodId}/entrada-stock`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({
                cantidad: tipo === "entrada" ? cantidad : -cantidad,
                motivo: motivo || (tipo === "entrada" ? "Entrada de mercadería" : "Salida de mercadería")
            }),
        });
        const j = await r.json();

        if (j.success) {
            await Swal.fire({
                icon: "success",
                title: "¡Stock actualizado!",
                html: `<p class="text-gray-600 mb-2">${productoActual.prod_nombre}</p>
                       <div class="flex justify-center items-center gap-3 text-lg">
                           <span class="font-bold text-gray-600">${j.stock_anterior}</span>
                           <span class="text-emerald-600">→</span>
                           <span class="font-bold text-emerald-600">${j.stock_nuevo}</span>
                       </div>`,
                timer: 2000, showConfirmButton: false
            });
            closeModal("modalActualizarStock");
            cargarProductos(categoriaActual);
            scannerInput.focus();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: j.message || "No se pudo actualizar el stock" });
        }
    } catch (e) {
        console.error(e);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo conectar con el servidor" });
    } finally {
        Loader.hide();
        setBtnLoading(document.getElementById("btnConfirmarStock"), false);
    }
};

// Vista previa al escoger imagen - CORREGIDO
document.getElementById("edit_quick_prod_imagen")?.addEventListener("change", (e) => {
    const f = e.target.files?.[0];
    const prev = document.getElementById("edit_quick_preview");
    if (f && prev) {
        const reader = new FileReader();
        reader.onload = (event) => {
            prev.src = event.target.result;
        };
        reader.readAsDataURL(f);
    }
});

const guardarEdicionQuick = async (e) => {
    e.preventDefault();
    const id = document.getElementById("edit_quick_prod_id").value;

    const fd = new FormData();
    fd.append("prod_nombre", document.getElementById("edit_quick_prod_nombre").value);
    fd.append("prod_descripcion", document.getElementById("edit_quick_prod_descripcion").value);
    fd.append("tprod_id", document.getElementById("edit_quick_tprod_id").value);
    fd.append("prod_precio_compra", document.getElementById("edit_quick_prod_precio_compra").value || 0);
    fd.append("prod_precio_venta", document.getElementById("edit_quick_prod_precio_venta").value || 0);
    fd.append("prod_stock_minimo", document.getElementById("edit_quick_prod_stock_minimo").value || 0);

    const imgFile = document.getElementById("edit_quick_prod_imagen").files?.[0];
    if (imgFile) fd.append("prod_imagen", imgFile);

    fd.append("_method", "PUT");

    try {
        setBtnLoading(document.getElementById("btnGuardarEdicionQuick"), true, "Guardando...");
        Loader.show("Actualizando producto...");

        const r = await fetch("/productos/" + id, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json"
            },
            body: fd
        });
        const j = await r.json();

        if (j.success) {
            await Swal.fire({
                icon: "success",
                title: "¡Producto actualizado!",
                timer: 1800,
                showConfirmButton: false
            });
            closeModal("modalActualizarStock");
            cargarProductos(categoriaActual);
            scannerInput.focus();
        } else {
            Swal.fire("Error", j.message || "No se pudo actualizar", "error");
        }
    } catch (err) {
        console.error(err);
        Swal.fire("Error", "Fallo de conexión", "error");
    } finally {
        Loader.hide();
        setBtnLoading(document.getElementById("btnGuardarEdicionQuick"), false);
    }
};

// ========================= CREAR PRODUCTO =========================
const abrirModalCrearProducto = (codigo) => {
    const form = document.getElementById("formCrearProducto");
    form.reset();

    const hidden = document.getElementById("crear_prod_codigo");
    const fila = document.getElementById("fila_codigo_creado");
    const vis = document.getElementById("crear_prod_codigo_visible");

    hidden.value = "";
    fila?.classList.add("hidden");
    if (codigo) {
        hidden.value = codigo;
        if (vis && fila) { vis.value = codigo; fila.classList.remove("hidden"); }
    }

    document.getElementById("modalCrearProducto").classList.remove("hidden");
};

const crearProductoNuevo = async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append("prod_stock_minimo", "5");

    setBtnLoading(document.getElementById("btnCrearProducto"), true, "Creando...");
    Loader.show("Creando producto...");

    try {
        const r = await fetch("/productos", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: fd,
        });
        const j = await r.json();

        if (j.success) {
            await Swal.fire({
                icon: "success",
                title: "¡Producto creado!",
                html: `<div class="text-left">
                        <p><strong>${j.producto.prod_nombre}</strong></p>
                        <p class="font-mono text-sm text-gray-700 mt-1">Código: ${j.producto.prod_codigo}</p>
                       </div>`,
                showCancelButton: true,
                confirmButtonText: "Imprimir código",
                cancelButtonText: "Cerrar",
                confirmButtonColor: "#2563eb"
            }).then((res) => {
                if (res.isConfirmed) window.imprimirCodigoBarras(j.producto.prod_codigo, j.producto.prod_nombre);
            });

            closeModal("modalCrearProducto");
            cargarProductos(categoriaActual);
            scannerInput.focus();
            location.reload();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: j.message || "No se pudo crear el producto" });
        }
    } catch (e) {
        console.error(e);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo conectar con el servidor" });
    } finally {
        Loader.hide();
        setBtnLoading(document.getElementById("btnCrearProducto"), false);
    }
};

// ========================= FUNCIONES GLOBALES =========================
window.abrirModalStock = async (id) => {
    try {
        const r = await fetch("/productos/" + id, {
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });
        const j = await r.json();
        if (j.success && j.producto) abrirModalActualizarStock(j.producto);
    } catch (e) { console.error(e); }
};

window.imprimirCodigoBarras = (codigo, nombre) => {
    if (!codigo) {
        Swal.fire({ icon: "warning", title: "Sin código", text: "Este producto no tiene código para imprimir" });
        return;
    }
    const w = window.open("", "_blank", "width=400,height=600");
    const html = `
    <!DOCTYPE html><html><head><meta charset="UTF-8"><title>Código de Barras - ${nombre}</title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f3f4f6;padding:20px}
    .etiqueta{background:#fff;border:2px dashed #d1d5db;border-radius:8px;padding:20px;text-align:center;max-width:350px}
    .nombre{font-size:16px;font-weight:bold;color:#1f2937;margin-bottom:15px;line-height:1.4}
    .codigo-container{margin:20px 0}.codigo-barras{width:100%;max-width:300px;height:auto;margin:0 auto;display:block}
    .codigo-texto{font-family:'Courier New',monospace;font-size:18px;font-weight:bold;color:#374151;margin-top:10px;letter-spacing:2px}
    .instrucciones{margin-top:20px;padding:10px;background:#fef3c7;border-radius:6px;font-size:12px;color:#92400e}
    @media print{body{background:#fff;padding:0}.etiqueta{border:none;max-width:100%}.instrucciones{display:none}}</style>
    </head><body>
      <div class="etiqueta">
        <div class="nombre">${nombre || ""}</div>
        <div class="codigo-container">
          <img src="/productos/barcode/${encodeURIComponent(codigo)}" alt="Código de barras" class="codigo-barras"
               onerror="this.style.display='none'; document.querySelector('.error-msg').style.display='block';">
          <div class="error-msg" style="display:none; color:red; margin-top:10px;">Error al generar código de barras</div>
          <div class="codigo-texto">${codigo}</div>
        </div>
        <div class="instrucciones">💡 Presiona Ctrl+P o Cmd+P para imprimir</div>
      </div>
      <script>window.onload=()=>setTimeout(()=>window.print(),500)</script>
    </body></html>`;
    w.document.write(html);
    w.document.close();
};

window.generarCodigoProducto = async (id) => {
    try {
        Loader.show("Generando código...");
        const r = await fetch(`/productos/${id}/asignar-codigo`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            }
        });
        const j = await r.json();
        if (j.success) {
            await Swal.fire({
                icon: "success",
                title: "Código creado",
                html: `<p class="font-mono text-sm">Nuevo: ${j.producto.prod_codigo}</p>`,
                showCancelButton: true,
                confirmButtonText: "Imprimir",
                cancelButtonText: "Cerrar",
                confirmButtonColor: "#2563eb"
            }).then(res => {
                if (res.isConfirmed) window.imprimirCodigoBarras(j.producto.prod_codigo, j.producto.prod_nombre);
            });
            cargarProductos(categoriaActual);
        } else {
            Swal.fire("Atención", j.message || "No se pudo generar el código", "warning");
        }
    } catch (e) {
        Swal.fire("Error", "Fallo de conexión", "error");
    } finally {
        Loader.hide();
    }
};

window.eliminarProducto = async (id) => {
    const ok = await Swal.fire({
        icon: "warning",
        title: "Eliminar producto",
        text: "Esta acción dará de baja el producto. ¿Continuar?",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc2626"
    });
    if (!ok.isConfirmed) return;

    try {
        Loader.show("Eliminando...");
        const r = await fetch(`/productos/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });
        const j = await r.json();
        if (j.success) {
            await Swal.fire({ icon: "success", title: "Producto eliminado", timer: 1500, showConfirmButton: false });
            cargarProductos(categoriaActual);
        } else {
            Swal.fire("Error", j.message || "No se pudo eliminar", "error");
        }
    } catch (e) {
        Swal.fire("Error", "Fallo de conexión", "error");
    } finally {
        Loader.hide();
    }
};

// ========================= FOCO & INIT =========================
// ========================= FOCO & INIT =========================
const enfocarScanner = () => {
    const activo = document.activeElement;
    const modalStockAbierto = document.querySelector("#modalActualizarStock:not(.hidden)");
    const modalCrearAbierto = document.querySelector("#modalCrearProducto:not(.hidden)");

    if (scannerInput && !modalStockAbierto && !modalCrearAbierto) {
        const elementosQueNoDebenTenerFoco = ['input', 'textarea', 'select', 'button'];
        const tagName = activo?.tagName.toLowerCase();

        if (!activo || activo === document.body || !elementosQueNoDebenTenerFoco.includes(tagName)) {
            console.log("Enfocando scanner..."); // DEBUG
            scannerInput.focus();
        }
    }
};

document.addEventListener("DOMContentLoaded", () => {
    cargarProductos();

    // === CONFIGURACIÓN MEJORADA DEL ESCÁNER ===
    if (scannerInput) {
        console.log("Configurando escáner..."); // DEBUG

        // Listener separado y específico para el Enter
        scannerInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                e.stopImmediatePropagation(); // IMPORTANTE

                const codigo = scannerInput.value.trim().replace(/[\r\n]/g, '');
                console.log("Escáner - Enter detectado, código:", codigo); // DEBUG

                if (codigo) {
                    buscarProductoPorCodigo(codigo);
                }

                // Limpiar inmediatamente después de capturar
                setTimeout(() => {
                    scannerInput.value = "";
                }, 100);
            }
        });

        // Búsqueda dinámica separada
        scannerInput.addEventListener("input", (e) => {
            const codigo = e.target.value.trim().replace(/[\r\n]/g, '');
            if (codigo.length >= 3) {
                busquedaDinamicaScanner(codigo);
            } else {
                ocultarSugerenciasScanner();
            }
        });

        // Enfocar automáticamente al cargar
        setTimeout(() => {
            scannerInput.focus();
        }, 1000);
    }

    // El resto de tus event listeners...
    searchBox?.addEventListener("input", (e) => buscarProductos(e.target.value));

    document.querySelectorAll(".categoria-btn").forEach(btn => {
        btn.addEventListener("click", () => filtrarPorCategoria(btn.dataset.categoria));
    });

    document.getElementById("formActualizarStock")?.addEventListener("submit", guardarMovimientoStock);
    document.getElementById("update_cantidad")?.addEventListener("input", calcularPreviewStock);

    document.querySelectorAll(".accion-btn").forEach(btn => {
        btn.addEventListener("click", () => cambiarTipoMovimiento(btn.dataset.accion));
    });

    document.getElementById("btnNuevoProducto")?.addEventListener("click", () => abrirModalCrearProducto());

    document.getElementById("btnFiltrarSinCodigo")?.addEventListener("click", () => {
        const sinCodigo = productosData.filter(p => !p.prod_codigo);
        renderizarProductos(sinCodigo);
        Swal.fire({
            icon: "info",
            title: "Productos sin código",
            text: `Mostrando ${sinCodigo.length} productos`,
            timer: 1800,
            showConfirmButton: false
        });
    });

    document.getElementById("formCrearProducto")?.addEventListener("submit", crearProductoNuevo);
    document.getElementById("formEditarProductoQuick")?.addEventListener("submit", guardarEdicionQuick);

    document.getElementById("tabStock")?.addEventListener("click", () => cambiarTab("stock"));
    document.getElementById("tabEditar")?.addEventListener("click", () => cambiarTab("editar"));

    // Enfocar después de un breve delay
    setTimeout(enfocarScanner, 500);
});