import Swal from "sweetalert2";
import { closeModal, validarFormulario, Loader, setBtnLoading } from "../app";

import "datatables.net-dt/css/dataTables.dataTables.css";
import DataTable from "datatables.net-dt";

const Form = document.getElementById("formUsuario");
const btnGuardarUsuario = document.getElementById("btnGuardarUsuario");
const btnActualizarUsuario = document.getElementById("btnActualizarUsuario");
const modalTitulo = document.getElementById("modalUsuarioTitulo");
const tablaUsuarios = document.getElementById("tablaUsuarios");
const campoPassword = document.getElementById("campoPassword");
const campoPasswordConfirmation = document.getElementById("campoPasswordConfirmation");

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

const GuardarUsuario = async (e) => {
    e.preventDefault();
    setBtnLoading(btnGuardarUsuario, true, "Guardando…");
    
    if (!validarFormulario(Form, ["telefono"])) {
        await Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos obligatorios", icon: "info" });
        setBtnLoading(btnGuardarUsuario, false);
        return;
    }

    // Validar contraseñas
    const password = document.getElementById("password").value;
    const passwordConfirmation = document.getElementById("password_confirmation").value;
    
    if (password !== passwordConfirmation) {
        await Swal.fire({ title: "Contraseñas no coinciden", text: "Las contraseñas deben ser iguales", icon: "error" });
        setBtnLoading(btnGuardarUsuario, false);
        return;
    }

    if (password.length < 8) {
        await Swal.fire({ title: "Contraseña muy corta", text: "La contraseña debe tener al menos 8 caracteres", icon: "warning" });
        setBtnLoading(btnGuardarUsuario, false);
        return;
    }

    const body = new FormData(Form);
    const url = "/usuarios/guardar";

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
    Loader.show("Guardando usuario…");

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
                text: mensaje || "Usuario creado correctamente",
                confirmButtonColor: "#059669",
            });
            closeModal("modalUsuario");
            Form.reset();
            btnGuardarUsuario.classList.remove("hidden");
            btnActualizarUsuario.classList.add("hidden");
            modalTitulo.textContent = "Agregar Usuario";
            mostrarCamposPassword(true);
            CargarDatos();
        } else {
            const txt = [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") || "Ocurrió un problema.";
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
        btnGuardarUsuario.disabled = false;
        Loader.hide();
        setBtnLoading(btnGuardarUsuario, false);
    }
};

const CargarDatos = async () => {
    const url = "/usuarios/obtener-datos";
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
            const txt = [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") || "Ocurrió un problema.";
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
    const { id, nombre, email, telefono, rol } = btn.dataset;

    document.getElementById("user_id").value = id || "";
    document.getElementById("name").value = nombre || "";
    document.getElementById("email").value = email || "";
    document.getElementById("telefono").value = telefono || "";
    document.getElementById("rol").value = rol || "";

    btnGuardarUsuario.classList.add("hidden");
    btnActualizarUsuario.classList.remove("hidden");
    modalTitulo.textContent = "Editar Usuario";
    mostrarCamposPassword(false);
};

const mostrarCamposPassword = (mostrar) => {
    if (mostrar) {
        campoPassword.style.display = "block";
        campoPasswordConfirmation.style.display = "block";
        document.getElementById("password").required = true;
        document.getElementById("password_confirmation").required = true;
    } else {
        campoPassword.style.display = "none";
        campoPasswordConfirmation.style.display = "none";
        document.getElementById("password").required = false;
        document.getElementById("password_confirmation").required = false;
    }
};

const ModificarUsuario = async () => {
    setBtnLoading(btnActualizarUsuario, true, "Actualizando…");
    if (!validarFormulario(Form, ["telefono", "password", "password_confirmation"])) {
        await Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos obligatorios", icon: "info" });
        setBtnLoading(btnActualizarUsuario, false);
        return;
    }

    const formData = new FormData(Form);
    const body = {
        user_id: formData.get('user_id'),
        name: formData.get('name'),
        email: formData.get('email'),
        telefono: formData.get('telefono'),
        rol: formData.get('rol')
    };

    // Solo agregar password si se está cambiando
    const password = formData.get('password');
    if (password && password.length > 0) {
        if (password.length < 8) {
            await Swal.fire({ title: "Contraseña muy corta", text: "La contraseña debe tener al menos 8 caracteres", icon: "warning" });
            setBtnLoading(btnActualizarUsuario, false);
            return;
        }
        body.password = password;
        body.password_confirmation = formData.get('password_confirmation');
    }

    const url = "/usuarios/actualizar";

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
    Loader.show("Actualizando usuario…");

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
                text: mensaje || "Usuario actualizado correctamente",
                confirmButtonColor: "#059669",
            });
            closeModal("modalUsuario");
            Form.reset();
            btnGuardarUsuario.classList.remove("hidden");
            btnActualizarUsuario.classList.add("hidden");
            modalTitulo.textContent = "Agregar Usuario";
            mostrarCamposPassword(true);
            CargarDatos();
        } else {
            const txt = [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") || "Ocurrió un problema.";
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
        setBtnLoading(btnActualizarUsuario, false);
    }
};

const EliminarUsuario = async (id) => {
    const ok = await Swal.fire({
        icon: "warning",
        title: "¿Eliminar usuario?",
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
    });
    if (!ok.isConfirmed) return;

    const url = "/usuarios/eliminar";
    const config = {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ user_id: id }),
        credentials: "same-origin",
    };
    Loader.show("Eliminando usuario…");

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
                text: mensaje || "Usuario eliminado correctamente",
            });
            CargarDatos();
        } else {
            const txt = [mensaje, detalle].filter((v) => v && String(v).trim() !== "").join(" || ") || "No se pudo eliminar.";
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

const datatable = new DataTable('#tablaUsuarios', {
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
            data: 'name',
            className: 'text-gray-900 font-semibold'
        },
        {
            title: 'Email',
            data: 'email',
            className: 'text-gray-600'
        },
        {
            title: 'Teléfono',
            data: 'telefono',
            className: 'text-gray-600',
            defaultContent: '<span class="text-gray-400">—</span>'
        },
        {
            title: 'Rol',
            data: 'rol',
            className: 'text-center',
            render: (data) => {
                const badgeClass = data === 'admin' 
                    ? 'bg-purple-100 text-purple-800' 
                    : 'bg-blue-100 text-blue-800';
                return `<span class="px-2 py-1 text-xs font-medium rounded-full ${badgeClass}">${data}</span>`;
            }
        },
        {
            title: 'Acciones',
            data: 'id',
            searchable: false,
            orderable: false,
            width: '140px',
            className: 'text-center',
            render: (id, _type, row) => `
                <div class="flex items-center justify-center gap-2">
                    <button class="modificar inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors duration-200"
                            title="Editar usuario"
                            data-id="${id}"
                            data-nombre="${(row.name || '').replace(/"/g, '&quot;')}"
                            data-email="${(row.email || '').replace(/"/g, '&quot;')}"
                            data-telefono="${(row.telefono || '').replace(/"/g, '&quot;')}"
                            data-rol="${row.rol}"
                            data-modal-open="modalUsuario">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button class="eliminar inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors duration-200"
                            title="Eliminar usuario"
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

// Event Listeners
tablaUsuarios.addEventListener("click", (e) => {
    const btnEdit = e.target.closest(".modificar");
    if (btnEdit) {
        llenarParaEditar(btnEdit);
    }

    const btnDel = e.target.closest(".eliminar");
    if (btnDel) {
        const id = btnDel.dataset.id;
        if (id) EliminarUsuario(id);
    }
});

Form.addEventListener("submit", GuardarUsuario);
btnActualizarUsuario.addEventListener("click", ModificarUsuario);

document.getElementById("modalUsuario")?.addEventListener("transitionend", () => {
    const modal = document.getElementById("modalUsuario");
    if (modal?.classList.contains("hidden")) {
        Form.reset();
        btnGuardarUsuario.classList.remove("hidden");
        btnActualizarUsuario.classList.add("hidden");
        modalTitulo.textContent = "Agregar Usuario";
        mostrarCamposPassword(true);
    }
});

// Inicializar
CargarDatos();