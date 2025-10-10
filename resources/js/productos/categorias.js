import Swal from "sweetalert2";
import { closeModal, validarFormulario, Loader, setBtnLoading } from "../app";

import "datatables.net-dt/css/dataTables.dataTables.css";
import DataTable from "datatables.net-dt";

const Form = document.getElementById("formTipo");
const btnGuardarTipo = document.getElementById("btnGuardarTipo");
const btnActualizarTipo = document.getElementById("btnActualizarTipo");
const modalTitulo = document.getElementById("modalTipoTitulo");
const tablaTipo = document.getElementById("tablaTipos");

const token =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
    "";

const GuardarTipo = async (e) => {
    e.preventDefault();
    setBtnLoading(btnGuardarTipo, true, "Guardando…");
    if (!validarFormulario(Form, ["tprod_desc"])) {
        await Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos", icon: "info" });
        setBtnLoading(btnGuardarTipo, false);
        return;
    }

    const body = new FormData(Form);
    const url = "/productos/agregar/tipo";

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
    Loader.show("Guardando categoría…");

    try {
        const peticion = await fetch(url, config);
        let respuesta = null;
        try {
            respuesta = await peticion.json();
        } catch {
            respuesta = {};
        }

        const { codigo, mensaje, detalle } = respuesta || {};

        if (codigo == 1) {
            await Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: mensaje || "Operación realizada correctamente",
                confirmButtonColor: "#059669",
            });
            closeModal("modalTipo");
            Form.reset();
            btnGuardarTipo.classList.remove("hidden");
            btnActualizarTipo.classList.add("hidden");
            modalTitulo.textContent = "Agregar categoría";
            CargarDatos();
        } else {
            const txt =
                [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") ||
                "Ocurrió un problema.";
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
        btnGuardarTipo.disabled = false;
        Loader.hide();
        setBtnLoading(btnGuardarTipo, false);
    }
};


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
        const { data, codigo, mensaje, detalle } = respuesta;

        if (codigo == 1) {
            datatable.clear();
            if (data) datatable.rows.add(data);
            datatable.draw();
        } else {
            const txt =
                [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") ||
                "Ocurrió un problema.";
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: txt,
                confirmButtonColor: "#f82205",
            });
        }
    } catch (error) {
        console.warn(error);
    }
};


const llenarParaEditar = (btn) => {
    const { id, nombre, descripcion } = btn.dataset;

    document.getElementById("tprod_id").value = id || "";
    document.getElementById("tprod_nombre").value = nombre || "";
    document.getElementById("tprod_desc").value = descripcion || "";

    btnGuardarTipo.classList.add("hidden");
    btnActualizarTipo.classList.remove("hidden");
    modalTitulo.textContent = "Editar categoría";
};


const ModificarTipo = async () => {
    setBtnLoading(btnActualizarTipo, true, "Actualizando…");
    if (!validarFormulario(Form, ["tprod_desc"])) {
        await Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos", icon: "info" });
        setBtnLoading(btnActualizarTipo, false);
        return;
    }

    const formData = new FormData(Form);
    const body = {
        tprod_id: formData.get('tprod_id'),
        tprod_nombre: formData.get('tprod_nombre'),
        tprod_desc: formData.get('tprod_desc')
    };

    const url = "/productos/actualizar/tipo";

    const config = {
        method: "PUT",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            "Content-Type": "application/json",
        },
        body: JSON.stringify(body),
    };
    Loader.show("Actualizando categoría…");

    try {
        const peticion = await fetch(url, config);
        let respuesta = null;
        try {
            respuesta = await peticion.json();
        } catch {
            respuesta = {};
        }

        const { codigo, mensaje, detalle } = respuesta || {};

        if (codigo == 1) {
            await Swal.fire({
                icon: "success",
                title: "¡Actualizado!",
                text: mensaje || "La categoría fue actualizada.",
                confirmButtonColor: "#059669",
            });
            closeModal("modalTipo");
            Form.reset();
            btnGuardarTipo.classList.remove("hidden");
            btnActualizarTipo.classList.add("hidden");
            modalTitulo.textContent = "Agregar categoría";
            CargarDatos();
        } else {
            const txt =
                [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") ||
                "Ocurrió un problema.";
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
        setBtnLoading(btnActualizarTipo, false);
    }
};


const EliminarTipo = async (id) => {
    const ok = await Swal.fire({
        icon: "warning",
        title: "¿Eliminar registro?",
        text: "Esta acción es irreversible.",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
    });
    if (!ok.isConfirmed) return;

    const url = "/productos/eliminar/tipo";
    const config = {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ tprod_id: id }),
        credentials: "same-origin",
    };
    Loader.show("Eliminando categoría…");

    try {
        const peticion = await fetch(url, config);
        let respuesta = null;
        try {
            respuesta = await peticion.json();
        } catch {
            respuesta = {};
        }

        const { codigo, mensaje, detalle } = respuesta || {};

        if (codigo == 1) {
            await Swal.fire({
                icon: "success",
                title: "¡Eliminado!",
                text: mensaje || "El registro se eliminó correctamente.",
            });
            CargarDatos();
        } else {
            const txt =
                [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") ||
                "No se pudo eliminar.";
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


const datatable = new DataTable('#tablaTipos', {
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
    columnDefs: [
        {
            targets: "_all",
            className: "align-middle py-3 px-4"
        }
    ],
    columns: [
        {
            title: 'No.',
            data: null,
            width: '60px',
            className: 'text-center text-gray-600 font-medium',
            render: (_d, _t, _r, meta) => meta.row + meta.settings._iDisplayStart + 1
        },
        {
            title: 'Nombre',
            data: 'tprod_nombre',
            className: 'text-gray-900 font-semibold'
        },
        {
            title: 'Descripción',
            data: 'tprod_desc',
            className: 'text-gray-600',
            defaultContent: '<span class="text-gray-400">—</span>'
        },
        {
            title: 'Acciones',
            data: 'tprod_id',
            searchable: false,
            orderable: false,
            width: '140px',
            className: 'text-center',
            render: (id, _type, row) => `
                <div class="flex items-center justify-center gap-2">
                    <button class="modificar inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors duration-200"
                            title="Editar categoría"
                            data-id="${id}"
                            data-nombre="${(row.tprod_nombre || '').replace(/"/g, '&quot;')}"
                            data-descripcion="${(row.tprod_desc || '').replace(/"/g, '&quot;')}"
                            data-modal-open="modalTipo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button class="eliminar inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors duration-200"
                            title="Eliminar categoría"
                            data-id="${id}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `
        }
    ]
});


tablaTipo.addEventListener("click", (e) => {
    const btnEdit = e.target.closest(".modificar");
    if (btnEdit) {
        llenarParaEditar(btnEdit);
    }

    const btnDel = e.target.closest(".eliminar");
    if (btnDel) {
        const id = btnDel.dataset.id;
        if (id) EliminarTipo(id);
    }
});


Form.addEventListener("submit", GuardarTipo);
btnActualizarTipo.addEventListener("click", ModificarTipo);

document.getElementById("modalTipo")?.addEventListener("transitionend", () => {
    const modal = document.getElementById("modalTipo");
    if (modal?.classList.contains("hidden")) {
        Form.reset();
        btnGuardarTipo.classList.remove("hidden");
        btnActualizarTipo.classList.add("hidden");
        modalTitulo.textContent = "Agregar categoría";
    }
});


CargarDatos();