import Swal from "sweetalert2";
import { closeModal, validarFormulario, Loader, setBtnLoading } from "../app";
import "datatables.net-dt/css/dataTables.dataTables.css";
import DataTable from "datatables.net-dt";

// Elementos del DOM
const formIngreso = document.getElementById("formIngreso");
const formEgreso = document.getElementById("formEgreso");
const btnGuardarIngreso = document.getElementById("btnGuardarIngreso");
const btnGuardarEgreso = document.getElementById("btnGuardarEgreso");
const btnFiltrar = document.getElementById("btnFiltrar");
const btnNuevoIngreso = document.getElementById("btnNuevoIngreso");
const btnNuevoEgreso = document.getElementById("btnNuevoEgreso");
const tablaMovimientos = document.getElementById("tablaMovimientos");

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

// DataTable para Movimientos
const datatable = new DataTable('#tablaMovimientos', {
    dom: `
      <"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4"
          <"dataTables_length">
          <"dataTables_filter">
      >
      <"dt-table-wrapper">
      t
      <"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4"
          <"dataTables_info">
          <"dataTables_paginate">
      >
    `,
    language: {
        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        lengthMenu: "Mostrar _MENU_ registros",
        search: "Buscar:",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        paginate: {
            first: "Primero",
            previous: "Anterior",
            next: "Siguiente",
            last: "Último"
        }
    },
    data: [],
    pageLength: 10,
    autoWidth: false,
    responsive: true,
    order: [[1, 'desc']],
    columnDefs: [
        {
            targets: "_all",
            className: "align-middle py-3 px-4"
        }
    ],
    columns: [
        {
            title: 'ID',
            data: 'cajamov_id',
            width: '80px',
            className: 'text-center text-gray-600 font-medium'
        },
        {
            title: 'Fecha/Hora',
            data: 'created_at',
            className: 'text-gray-900',
            render: function (data) {
                if (!data) return '-';
                const fecha = new Date(data);
                return fecha.toLocaleDateString('es-GT') + ' ' + fecha.toLocaleTimeString('es-GT');
            }
        },
        {
            title: 'Descripción',
            data: 'cajamov_desc',
            className: 'text-gray-900'
        },
        {
            title: 'Tipo',
            data: 'cajamov_tipo',
            className: 'text-center',
            render: function (data) {
                const tipos = {
                    'Venta': { color: 'blue', texto: 'Venta', icon: 'shopping-cart' },
                    'Ingreso': { color: 'green', texto: 'Ingreso', icon: 'plus-circle' },
                    'Egreso': { color: 'red', texto: 'Egreso', icon: 'minus-circle' },
                    'Apertura': { color: 'purple', texto: 'Apertura', icon: 'lock-open' },
                    'Cierre': { color: 'orange', texto: 'Cierre', icon: 'lock' }
                };
                const tipo = tipos[data] || { color: 'gray', texto: data, icon: 'circle' };
                return `
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-${tipo.color}-100 text-${tipo.color}-800 border border-${tipo.color}-200">
                        <i class="fas fa-${tipo.icon}"></i>
                        ${tipo.texto}
                    </span>
                `;
            }
        },
        {
            title: 'Monto',
            data: 'cajamov_monto',
            className: 'text-right font-semibold',
            render: function (data, type, row) {
                let color = 'text-green-600';
                let signo = '+';

                if (row.cajamov_tipo === 'Egreso') {
                    color = 'text-red-600';
                    signo = '-';
                } else if (row.cajamov_tipo === 'Venta') {
                    color = 'text-blue-600';
                    signo = '+';
                }

                return `<span class="${color}">${signo}Q ${parseFloat(data).toFixed(2)}</span>`;
            }
        },
        {
            title: 'Saldo Final',
            data: 'cajamov_saldo_final',
            className: 'text-right font-bold text-gray-800',
            render: function (data) {
                return `Q ${parseFloat(data).toFixed(2)}`;
            }
        },
        {
            title: 'Usuario',
            data: 'usuario',
            className: 'text-gray-600',
            render: function (data, type, row) {
                return data?.name || 'Sistema';
            }
        },
        {
            title: 'Acciones',
            data: 'cajamov_id',
            searchable: false,
            orderable: false,
            width: '140px',
            className: 'text-center',
            render: function (id, type, row) {
                if (row.cajamov_tipo === 'Venta' && row.ven_id) {
                    return `
                <div class="flex items-center justify-center gap-2">
                    <button class="ver-detalle-venta inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors duration-200"
                            title="Ver detalle de venta"
                            data-ven-id="${row.ven_id}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            `;
                } else {
                    return '<span class="text-gray-400 text-sm">—</span>';
                }
            }
        }
    ]
});


// DataTable para Saldos por Día
const datatableSaldos = new DataTable('#tablaSaldosDia', {
    dom: `
      <"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4"
          <"dataTables_length">
          <"dataTables_filter">
      >
      <"dt-table-wrapper">
      t
      <"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4"
          <"dataTables_info">
          <"dataTables_paginate">
      >
    `,
    language: {
        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        lengthMenu: "Mostrar _MENU_ registros",
        search: "Buscar:",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        paginate: {
            first: "Primero",
            previous: "Anterior",
            next: "Siguiente",
            last: "Último"
        }
    },
    data: [],
    pageLength: 10,
    autoWidth: false,
    responsive: true,
    order: [[0, 'desc']],
    columnDefs: [
        {
            targets: "_all",
            className: "align-middle py-3 px-4"
        }
    ],
    columns: [
        {
            data: 'fecha',
            render: function (data) {
                if (!data) return '';
                const [year, month, day] = data.split('-');
                const fechaLocal = new Date(year, month - 1, day);

                return fechaLocal.toLocaleDateString('es-GT', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        },
        {
            title: 'Total Ventas',
            data: 'total_ventas',
            className: 'text-right font-semibold',
            render: function (data) {
                return `<span class="text-blue-600">Q ${parseFloat(data).toFixed(2)}</span>`;
            }
        },
        {
            title: 'Total Ingresos',
            data: 'total_ingresos',
            className: 'text-right font-semibold',
            render: function (data) {
                return `<span class="text-green-600">Q ${parseFloat(data).toFixed(2)}</span>`;
            }
        },
        {
            title: 'Total Egresos',
            data: 'total_egresos',
            className: 'text-right font-semibold',
            render: function (data) {
                return `<span class="text-red-600">Q ${parseFloat(data).toFixed(2)}</span>`;
            }
        },
        {
            title: 'Saldo del Día',
            data: 'saldo_dia',
            className: 'text-right font-bold',
            render: function (data) {
                const color = parseFloat(data) >= 0 ? 'text-gray-800' : 'text-red-600';
                return `<span class="${color}">Q ${parseFloat(data).toFixed(2)}</span>`;
            }
        },
        {
            title: 'Cant. Movimientos',
            data: 'cantidad_movimientos',
            className: 'text-center text-gray-600',
            render: function (data) {
                return `<span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full text-sm font-medium">${data}</span>`;
            }
        }
    ]
});


const verDetalleVenta = async (venId) => {
    Loader.show("Cargando detalle de venta...");

    try {
        const url = `/ventas/historial/detalle-venta/${venId}`;
        const peticion = await fetch(url, {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const respuesta = await peticion.json();
        const { codigo, data, mensaje } = respuesta;

        if (codigo == 1) {
            mostrarModalDetalleVenta(data);
        } else {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje || "No se pudo cargar el detalle de la venta",
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
    }
};


const mostrarModalDetalleVenta = (venta) => {
    const productosHTML = venta.detalles.map(detalle => `
        <div class="flex justify-between items-center py-2 border-b border-gray-100">
            <div class="flex-1">
                <p class="font-medium text-gray-800">${detalle.producto?.prod_nombre || 'Producto no encontrado'}</p>
                <p class="text-sm text-gray-600">Código: ${detalle.producto?.prod_codigo || 'N/A'}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">${detalle.vendet_cantidad} x Q ${parseFloat(detalle.vendet_precio).toFixed(2)}</p>
                <p class="font-semibold text-gray-800">Q ${parseFloat(detalle.vendet_total).toFixed(2)}</p>
            </div>
        </div>
    `).join('');

    const modalHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Detalle de Venta #${venta.ven_id}</h3>
                    <button onclick="cerrarModalDetalleVenta()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Información de la venta -->
                <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Fecha</p>
                        <p class="font-medium">${new Date(venta.ven_fecha).toLocaleDateString('es-GT')} ${venta.ven_hora}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Cliente</p>
                        <p class="font-medium">${venta.ven_cliente || 'Cliente no especificado'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Venta</p>
                        <p class="font-bold text-green-600">Q ${parseFloat(venta.ven_total).toFixed(2)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Efectivo Recibido</p>
                        <p class="font-medium">Q ${parseFloat(venta.ven_efectivo).toFixed(2)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Cambio</p>
                        <p class="font-medium">Q ${parseFloat(venta.ven_cambio).toFixed(2)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Vendedor</p>
                        <p class="font-medium">${venta.usuario?.name || 'N/A'}</p>
                    </div>
                </div>

                <!-- Productos vendidos -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Productos Vendidos</h4>
                    <div class="space-y-2">
                        ${productosHTML}
                    </div>
                </div>

                <!-- Resumen -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>Total Venta:</span>
                        <span class="text-green-600">Q ${parseFloat(venta.ven_total).toFixed(2)}</span>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button onclick="cerrarModalDetalleVenta()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    `;

    const modalContainer = document.createElement('div');
    modalContainer.id = 'modalDetalleVenta';
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
};


window.cerrarModalDetalleVenta = function () {
    const modal = document.getElementById('modalDetalleVenta');
    if (modal) {
        modal.remove();
    }
};


const CargarDatos = async () => {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const tipo = document.getElementById('tipoMovimiento').value;

    const url = `/ventas/historial/datos?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo=${tipo}`;

    Loader.show("Cargando movimientos...");

    try {
        const peticion = await fetch(url, {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        const respuesta = await peticion.json();
        const { data, resumen, saldos_dia, codigo, mensaje } = respuesta;

        if (codigo == 1) {
            datatable.clear();
            if (data) datatable.rows.add(data);
            datatable.draw();

            datatableSaldos.clear();
            if (saldos_dia) datatableSaldos.rows.add(saldos_dia);
            datatableSaldos.draw();

            document.getElementById('saldoCaja').textContent = `Q ${parseFloat(resumen.saldo_actual).toFixed(2)}`;
            document.getElementById('ventasHoy').textContent = `Q ${parseFloat(resumen.ventas_hoy).toFixed(2)}`;
            document.getElementById('ingresosHoy').textContent = `Q ${parseFloat(resumen.ingresos_hoy).toFixed(2)}`;
            document.getElementById('egresosHoy').textContent = `Q ${parseFloat(resumen.egresos_hoy).toFixed(2)}`;
        } else {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje || "Ocurrió un problema al cargar los datos",
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
    }
};


const RegistrarIngreso = async (e) => {
    e.preventDefault();
    setBtnLoading(btnGuardarIngreso, true, "Guardando...");

    if (!validarFormulario(formIngreso, ["monto", "descripcion"])) {
        await Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos", icon: "info" });
        setBtnLoading(btnGuardarIngreso, false);
        return;
    }

    const formData = new FormData(formIngreso);
    const body = {
        monto: formData.get('monto'),
        descripcion: formData.get('descripcion'),
        tipo: 'ingreso'
    };

    const url = "/ventas/historial/ingreso";

    Loader.show("Registrando ingreso...");

    try {
        const peticion = await fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify(body),
        });

        const respuesta = await peticion.json();
        const { codigo, mensaje } = respuesta;

        if (codigo == 1) {
            await Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: mensaje || "Ingreso registrado correctamente",
            });
            closeModal("modalIngreso");
            formIngreso.reset();
            CargarDatos();
        } else {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje || "Ocurrió un problema al registrar el ingreso",
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
        setBtnLoading(btnGuardarIngreso, false);
    }
};


const RegistrarEgreso = async (e) => {
    e.preventDefault();
    setBtnLoading(btnGuardarEgreso, true, "Guardando...");

    if (!validarFormulario(formEgreso, ["monto", "descripcion"])) {
        await Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos", icon: "info" });
        setBtnLoading(btnGuardarEgreso, false);
        return;
    }

    const formData = new FormData(formEgreso);
    const body = {
        monto: formData.get('monto'),
        descripcion: formData.get('descripcion'),
        tipo: 'egreso'
    };

    const url = "/ventas/historial/egreso";

    Loader.show("Registrando egreso...");

    try {
        const peticion = await fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify(body),
        });

        const respuesta = await peticion.json();
        const { codigo, mensaje } = respuesta;

        if (codigo == 1) {
            await Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: mensaje || "Egreso registrado correctamente",
            });
            closeModal("modalEgreso");
            formEgreso.reset();
            CargarDatos();
        } else {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje || "Ocurrió un problema al registrar el egreso",
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
        setBtnLoading(btnGuardarEgreso, false);
    }
};


const EliminarMovimiento = async (id, tipo) => {
    const ok = await Swal.fire({
        icon: "warning",
        title: `¿Eliminar ${tipo}?`,
        text: "Esta acción es irreversible.",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
    });

    if (!ok.isConfirmed) return;

    const url = "/ventas/historial/movimiento";

    Loader.show("Eliminando movimiento...");

    try {
        const peticion = await fetch(url, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({ movimiento_id: id }),
        });

        const respuesta = await peticion.json();
        const { codigo, mensaje } = respuesta;

        if (codigo == 1) {
            await Swal.fire({
                icon: "success",
                title: "¡Eliminado!",
                text: mensaje || "Movimiento eliminado correctamente",
            });
            CargarDatos();
        } else {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje || "No se pudo eliminar el movimiento",
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
    }
};

// Event Listeners
btnNuevoIngreso.addEventListener("click", () => {
    document.getElementById('modalIngreso').classList.remove('hidden');
    formIngreso.reset();
    document.getElementById('movimiento_id').value = '';
});

btnNuevoEgreso.addEventListener("click", () => {
    document.getElementById('modalEgreso').classList.remove('hidden');
    formEgreso.reset();
    document.getElementById('movimiento_id_egreso').value = '';
});

btnFiltrar.addEventListener("click", CargarDatos);

formIngreso.addEventListener("submit", RegistrarIngreso);
formEgreso.addEventListener("submit", RegistrarEgreso);

// Cerrar modales
document.getElementById("btnCerrarModalIngreso").addEventListener("click", () => closeModal("modalIngreso"));
document.getElementById("btnCancelarIngreso").addEventListener("click", () => closeModal("modalIngreso"));
document.getElementById("btnCerrarModalEgreso").addEventListener("click", () => closeModal("modalEgreso"));
document.getElementById("btnCancelarEgreso").addEventListener("click", () => closeModal("modalEgreso"));

// Event delegation para acciones de la tabla
tablaMovimientos.addEventListener("click", (e) => {
    const btnEdit = e.target.closest(".editar-movimiento");
    if (btnEdit) {
        const { id, tipo, monto, descripcion } = btnEdit.dataset;

        if (tipo === 'Ingreso') {
            document.getElementById('modalIngreso').classList.remove('hidden');
            document.getElementById('movimiento_id').value = id;
            document.getElementById('monto_ingreso').value = monto;
            document.getElementById('descripcion_ingreso').value = descripcion;
        } else if (tipo === 'Egreso') {
            document.getElementById('modalEgreso').classList.remove('hidden');
            document.getElementById('movimiento_id_egreso').value = id;
            document.getElementById('monto_egreso').value = monto;
            document.getElementById('descripcion_egreso').value = descripcion;
        }
    }

    const btnDel = e.target.closest(".eliminar-movimiento");
    if (btnDel) {
        const { id, tipo } = btnDel.dataset;
        EliminarMovimiento(id, tipo);
    }

    const btnDetalle = e.target.closest(".ver-detalle-venta");
    if (btnDetalle) {
        const venId = btnDetalle.dataset.venId;
        verDetalleVenta(venId);
    }
});

// Inicializar fechas
document.addEventListener('DOMContentLoaded', function () {
    const hoy = new Date();
    const haceUnaSemana = new Date(hoy.getTime() - 7 * 24 * 60 * 60 * 1000);

    document.getElementById('fechaInicio').value = haceUnaSemana.toISOString().split('T')[0];
    document.getElementById('fechaFin').value = hoy.toISOString().split('T')[0];

    CargarDatos();
});