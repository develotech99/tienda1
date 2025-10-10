import Swal from "sweetalert2";
import { closeModal, validarFormulario, Loader, setBtnLoading } from "../app";

import "datatables.net-dt/css/dataTables.dataTables.css";
import DataTable from "datatables.net-dt";

const Form = document.getElementById("formProducto");
const btnGuardarProducto = document.getElementById("btnGuardarProducto");
const btnActualizarProducto = document.getElementById("btnActualizarProducto");
const modalTitulo = document.getElementById("modalProductoTitulo");
const tablaProductos = document.getElementById("tablaProductos");
const inputScanner = document.getElementById("inputScanner");
const scannerStatus = document.getElementById("scannerStatus");

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

let timeoutScanner = null;
let datatable = null;

// ============================================
// FUNCIONES DEL SCANNER
// ============================================

const manejarScanner = (e) => {
    if (e.key === "Enter") {
        e.preventDefault();
        const codigo = inputScanner.value.trim();
        if (codigo) {
            buscarProductoPorCodigo(codigo);
        }
    }
};

const buscarProductoPorCodigo = async (codigo) => {
    scannerStatus.textContent = "● Buscando...";
    scannerStatus.className = "text-xs text-amber-600 font-medium";

    const url = "/productos/buscar-codigo";
    const config = {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ codigo }),
    };

    try {
        const peticion = await fetch(url, config);
        const respuesta = await peticion.json();

        if (respuesta.success && respuesta.producto) {
            // Producto encontrado - Cargar para editar
            scannerStatus.textContent = "● Producto encontrado";
            scannerStatus.className = "text-xs text-green-600 font-medium";
            
            llenarParaEditar(respuesta.producto);
            inputScanner.value = "";
        } else {
            // Producto no encontrado - Abrir modal para crear
            scannerStatus.textContent = "● Producto nuevo";
            scannerStatus.className = "text-xs text-blue-600 font-medium";
            
            abrirModalNuevo();
            document.getElementById("prod_codigo").value = codigo;
            inputScanner.value = "";
        }
    } catch (error) {
        scannerStatus.textContent = "● Error";
        scannerStatus.className = "text-xs text-red-600 font-medium";
        console.error(error);
    }

    setTimeout(() => {
        scannerStatus.textContent = "● Listo";
        scannerStatus.className = "text-xs text-emerald-600 font-medium";
        inputScanner.focus();
    }, 2000);
};

// Mantener el foco en el scanner
const enfocarScanner = () => {
    if (inputScanner && !document.querySelector("#modalProducto:not(.hidden)")) {
        inputScanner.focus();
    }
};

// ============================================
// FUNCIONES DEL MODAL
// ============================================

const abrirModalNuevo = () => {
    Form.reset();
    document.getElementById("prod_id").value = "";
    btnGuardarProducto.classList.remove("hidden");
    btnActualizarProducto.classList.add("hidden");
    modalTitulo.textContent = "Agregar producto";
    limpiarPreviewImagen();
    document.getElementById("margenGanancia").innerHTML = "";
    
    const modal = document.getElementById("modalProducto");
    modal?.classList.remove("hidden");
};

const llenarParaEditar = (producto) => {
    document.getElementById("prod_id").value = producto.prod_id || "";
    document.getElementById("prod_codigo").value = producto.prod_codigo || "";
    document.getElementById("prod_nombre").value = producto.prod_nombre || "";
    document.getElementById("prod_descripcion").value = producto.prod_descripcion || "";
    document.getElementById("tprod_id").value = producto.tprod_id || "";
    document.getElementById("prod_precio_compra").value = producto.prod_precio_compra || "";
    document.getElementById("prod_precio_venta").value = producto.prod_precio_venta || "";
    document.getElementById("prod_stock_minimo").value = producto.prod_stock_minimo || "";
    document.getElementById("prod_stock_actual").value = producto.prod_stock_actual || "";

    // Mostrar imagen si existe
    if (producto.prod_imagen) {
        mostrarPreviewImagen(`/storage/${producto.prod_imagen}`);
    } else {
        limpiarPreviewImagen();
    }

    calcularMargen();

    btnGuardarProducto.classList.add("hidden");
    btnActualizarProducto.classList.remove("hidden");
    modalTitulo.textContent = "Editar producto";

    const modal = document.getElementById("modalProducto");
    modal?.classList.remove("hidden");
};

// ============================================
// GUARDAR PRODUCTO
// ============================================

const GuardarProducto = async (e) => {
    e.preventDefault();
    setBtnLoading(btnGuardarProducto, true, "Guardando…");

    if (!validarFormulario(Form, ["prod_codigo", "prod_descripcion", "prod_imagen"])) {
        await Swal.fire({ 
            title: "Campos vacíos", 
            text: "Debe llenar todos los campos obligatorios", 
            icon: "info" 
        });
        setBtnLoading(btnGuardarProducto, false);
        return;
    }

    const body = new FormData(Form);
    const url = "/productos";

    const config = {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        body,
        credentials: "same-origin",
    };

    Loader.show("Guardando producto…");

    try {
        const peticion = await fetch(url, config);
        let respuesta = null;
        try {
            respuesta = await peticion.json();
        } catch {
            respuesta = {};
        }

        if (respuesta.success) {
            await Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: respuesta.message || "Producto guardado correctamente",
                confirmButtonColor: "#059669",
            });
            closeModal("modalProducto");
            Form.reset();
            limpiarPreviewImagen();
            btnGuardarProducto.classList.remove("hidden");
            btnActualizarProducto.classList.add("hidden");
            modalTitulo.textContent = "Agregar producto";
            CargarDatos();
            enfocarScanner();
        } else {
            const txt = respuesta.message || "Ocurrió un problema al guardar.";
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: txt,
                confirmButtonColor: "#f82205",
            });
        }
    } catch (error) {
        await Swal.fire({
            icon: "error",
            title: "Error de red",
            text: "No se pudo conectar con el servidor.",
        });
    } finally {
        Loader.hide();
        setBtnLoading(btnGuardarProducto, false);
    }
};

// ============================================
// ACTUALIZAR PRODUCTO
// ============================================

const ModificarProducto = async () => {
    setBtnLoading(btnActualizarProducto, true, "Actualizando…");

    if (!validarFormulario(Form, ["prod_codigo", "prod_descripcion", "prod_imagen"])) {
        await Swal.fire({ 
            title: "Campos vacíos", 
            text: "Debe llenar todos los campos obligatorios", 
            icon: "info" 
        });
        setBtnLoading(btnActualizarProducto, false);
        return;
    }

    const formData = new FormData(Form);
    const prod_id = formData.get('prod_id');
    
    // Agregar método PUT usando _method
    formData.append('_method', 'PUT');
    
    const url = `/productos/${prod_id}`;

    const config = {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        body: formData,
        credentials: "same-origin",
    };

    Loader.show("Actualizando producto…");

    try {
        const peticion = await fetch(url, config);
        let respuesta = null;
        try {
            respuesta = await peticion.json();
        } catch {
            respuesta = {};
        }

        if (respuesta.success) {
            await Swal.fire({
                icon: "success",
                title: "¡Actualizado!",
                text: respuesta.message || "Producto actualizado correctamente.",
                confirmButtonColor: "#059669",
            });
            closeModal("modalProducto");
            Form.reset();
            limpiarPreviewImagen();
            btnGuardarProducto.classList.remove("hidden");
            btnActualizarProducto.classList.add("hidden");
            modalTitulo.textContent = "Agregar producto";
            CargarDatos();
            enfocarScanner();
        } else {
            const txt = respuesta.message || "Ocurrió un problema al actualizar.";
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: txt,
                confirmButtonColor: "#f82205",
            });
        }
    } catch (error) {
        await Swal.fire({
            icon: "error",
            title: "Error de red",
            text: "No se pudo conectar con el servidor.",
        });
    } finally {
        Loader.hide();
        setBtnLoading(btnActualizarProducto, false);
    }
};

// ============================================
// CARGAR DATOS
// ============================================

const CargarDatos = async () => {
    const url = "/productos/ObtenerDatosAPI";
    const config = {
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    };

    try {
        const peticion = await fetch(url, config);
        const respuesta = await peticion.json();
        const { data, codigo, mensaje } = respuesta;

        if (codigo == 1) {
            datatable.clear();
            if (data) datatable.rows.add(data);
            datatable.draw();
        } else {
            console.warn(mensaje || "Error al cargar datos");
        }
    } catch (error) {
        console.error("Error al cargar productos:", error);
    }
};

// ============================================
// ELIMINAR PRODUCTO
// ============================================

const EliminarProducto = async (id) => {
    const ok = await Swal.fire({
        icon: "warning",
        title: "¿Eliminar producto?",
        text: "Esta acción es irreversible.",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
    });
    if (!ok.isConfirmed) return;

    const url = `/productos/${id}`;
    const config = {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        credentials: "same-origin",
    };

    Loader.show("Eliminando producto…");

    try {
        const peticion = await fetch(url, config);
        let respuesta = null;
        try {
            respuesta = await peticion.json();
        } catch {
            respuesta = {};
        }

        if (respuesta.success) {
            await Swal.fire({
                icon: "success",
                title: "¡Eliminado!",
                text: respuesta.message || "Producto eliminado correctamente.",
            });
            CargarDatos();
        } else {
            const txt = respuesta.message || "No se pudo eliminar.";
            await Swal.fire({ icon: "error", title: "Error", text: txt });
        }
    } catch (error) {
        await Swal.fire({
            icon: "error",
            title: "Error de red",
            text: "No se pudo conectar con el servidor.",
        });
    } finally {
        Loader.hide();
    }
};

// ============================================
// UTILIDADES
// ============================================

const calcularMargen = () => {
    const compra = parseFloat(document.getElementById("prod_precio_compra").value) || 0;
    const venta = parseFloat(document.getElementById("prod_precio_venta").value) || 0;
    const margenDiv = document.getElementById("margenGanancia");

    if (compra > 0 && venta > 0) {
        const margen = venta - compra;
        const porcentaje = ((margen / compra) * 100).toFixed(2);
        
        if (margen > 0) {
            margenDiv.innerHTML = `<span class="text-green-600 font-medium">Ganancia: Q${margen.toFixed(2)} (${porcentaje}%)</span>`;
        } else if (margen < 0) {
            margenDiv.innerHTML = `<span class="text-red-600 font-medium">Pérdida: Q${Math.abs(margen).toFixed(2)}</span>`;
        } else {
            margenDiv.innerHTML = `<span class="text-gray-600">Sin ganancia</span>`;
        }
    } else {
        margenDiv.innerHTML = "";
    }
};

const mostrarPreviewImagen = (src) => {
    const preview = document.getElementById("previewImagen");
    const img = document.getElementById("imagenPreview");
    
    if (preview && img) {
        img.src = src;
        preview.classList.remove("hidden");
    }
};

const limpiarPreviewImagen = () => {
    const preview = document.getElementById("previewImagen");
    const img = document.getElementById("imagenPreview");
    
    if (preview && img) {
        img.src = "";
        preview.classList.add("hidden");
    }
};

const manejarCambioImagen = (e) => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
            mostrarPreviewImagen(event.target.result);
        };
        reader.readAsDataURL(file);
    } else {
        limpiarPreviewImagen();
    }
};

// ============================================
// INICIALIZAR DATATABLE
// ============================================

const inicializarDataTable = () => {
    datatable = new DataTable(tablaProductos, {
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        responsive: true,
        pageLength: 10,
        order: [[1, "asc"]],
        columns: [
            {
                title: "Imagen",
                data: "prod_imagen",
                render: (data) => {
                    if (data) {
                       return `<img src="/storage/${data}" alt="Producto" class="w-12 h-12 object-cover rounded-lg border">`;
                    }
                    return `<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>`;
                },
            },
            { title: "Código", data: "prod_codigo" },
            { title: "Nombre", data: "prod_nombre" },
            { 
                title: "Categoría", 
                data: "tipo",
                render: (data) => data ? data.tprod_nombre : "Sin categoría"
            },
            {
                title: "Precio Compra",
                data: "prod_precio_compra",
                render: (data) => `Q${parseFloat(data).toFixed(2)}`,
            },
            {
                title: "Precio Venta",
                data: "prod_precio_venta",
                render: (data) => `Q${parseFloat(data).toFixed(2)}`,
            },
            {
                title: "Stock",
                data: "prod_stock_actual",
                render: (data, type, row) => {
                    const stock = parseInt(data);
                    const minimo = parseInt(row.prod_stock_minimo);
                    let badgeClass = "bg-green-100 text-green-800";
                    
                    if (stock === 0) {
                        badgeClass = "bg-red-100 text-red-800";
                    } else if (stock <= minimo) {
                        badgeClass = "bg-amber-100 text-amber-800";
                    }
                    
                    return `<span class="px-2 py-1 rounded-full text-xs font-medium ${badgeClass}">${stock}</span>`;
                },
            },
            {
                title: "Acciones",
                data: null,
                orderable: false,
                render: (data, type, row) => {
                    return `
                        <div class="flex gap-2">
                            <button 
                                onclick="window.editarProducto(${row.prod_id})"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button 
                                onclick="window.eliminarProducto(${row.prod_id})"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                },
            },
        ],
    });
};

// ============================================
// FUNCIÓN PARA EDITAR (GLOBAL)
// ============================================

window.editarProducto = async (id) => {
    const url = `/productos/${id}`;
    const config = {
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    };

    try {
        const peticion = await fetch(url, config);
        const respuesta = await peticion.json();
        
        if (respuesta.success && respuesta.producto) {
            llenarParaEditar(respuesta.producto);
        }
    } catch (error) {
        console.error("Error al cargar producto:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo cargar el producto",
        });
    }
};

window.eliminarProducto = EliminarProducto;

// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener("DOMContentLoaded", () => {
    // Inicializar DataTable
    if (tablaProductos) {
        inicializarDataTable();
        CargarDatos();
    }

    // Event listeners
    Form?.addEventListener("submit", GuardarProducto);
    btnActualizarProducto?.addEventListener("click", ModificarProducto);
    inputScanner?.addEventListener("keydown", manejarScanner);
    
    // Calcular margen automáticamente
    document.getElementById("prod_precio_compra")?.addEventListener("input", calcularMargen);
    document.getElementById("prod_precio_venta")?.addEventListener("input", calcularMargen);
    
    // Preview de imagen
    document.getElementById("prod_imagen")?.addEventListener("change", manejarCambioImagen);

    // Botón abrir modal nuevo
    document.getElementById("btnAbrirModalProducto")?.addEventListener("click", abrirModalNuevo);

    // Mantener foco en scanner
    setInterval(enfocarScanner, 1000);
    enfocarScanner();
});