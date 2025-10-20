import Swal from "sweetalert2";
import { Loader } from "../app";

// Elementos del DOM
const btnGenerarGraficas = document.getElementById("btnGenerarGraficas");
const fechaInicio = document.getElementById("fechaInicio");
const fechaFin = document.getElementById("fechaFin");
const tipoGrafica = document.getElementById("tipoGrafica");

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

// Variables para almacenar instancias de gráficas
let graficaPrincipal = null;
let graficaSecundaria = null;

// Colores para las gráficas
const colores = [
    '#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6',
    '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
];

// Inicializar fechas
function inicializarFechas() {
    const hoy = new Date();
    const haceUnMes = new Date(hoy.getTime() - 30 * 24 * 60 * 60 * 1000);

    fechaInicio.value = haceUnMes.toISOString().split('T')[0];
    fechaFin.value = hoy.toISOString().split('T')[0];
}

// Cargar Chart.js dinámicamente
let Chart = null;

const cargarChartJS = async () => {
    if (!Chart) {
        const chartModule = await import('chart.js/auto');
        Chart = chartModule.default;
    }
    return Chart;
};

// Cargar datos para gráficas
const cargarDatosGraficas = async () => {
    const fechaInicioVal = fechaInicio.value;
    const fechaFinVal = fechaFin.value;
    const tipoGraficaVal = tipoGrafica.value;

    if (!fechaInicioVal || !fechaFinVal) {
        await Swal.fire({
            icon: "warning",
            title: "Fechas requeridas",
            text: "Por favor seleccione ambas fechas",
        });
        return;
    }

    // Cargar Chart.js antes de generar gráficas
    await cargarChartJS();

    const url = `/ventas/reportes/datos-graficas?fecha_inicio=${fechaInicioVal}&fecha_fin=${fechaFinVal}&tipo_grafica=${tipoGraficaVal}`;

    Loader.show("Generando gráficas...");

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
        const { data, filtros, codigo, mensaje } = respuesta;

        if (codigo == 1) {
            generarGraficas(data, tipoGraficaVal);
            actualizarEstadisticas(data, tipoGraficaVal);
            actualizarTablaDatos(data, tipoGraficaVal);
        } else {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje || "Ocurrió un problema al generar las gráficas",
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

// Generar gráficas basadas en los datos
function generarGraficas(datos, tipo) {
    // Destruir gráficas existentes
    if (graficaPrincipal) graficaPrincipal.destroy();
    if (graficaSecundaria) graficaSecundaria.destroy();

    const ctxPrincipal = document.getElementById('graficaPrincipal').getContext('2d');
    const ctxSecundaria = document.getElementById('graficaSecundaria').getContext('2d');

    switch (tipo) {
        case 'ventas':
            generarGraficasVentas(datos, ctxPrincipal, ctxSecundaria);
            break;
        case 'productos':
            generarGraficasProductos(datos, ctxPrincipal, ctxSecundaria);
            break;
        case 'caja':
            generarGraficasCaja(datos, ctxPrincipal, ctxSecundaria);
            break;
        case 'comparativa':
            generarGraficasComparativa(datos, ctxPrincipal, ctxSecundaria);
            break;
    }
}

// Gráficas para ventas - VERSIÓN MEJORADA Y CLARA
function generarGraficasVentas(datos, ctxPrincipal, ctxSecundaria) {
    const ventasPorDia = datos.ventas_por_dia;
    const ventasPorHora = datos.ventas_por_hora;

    // 🆕 GRÁFICA PRINCIPAL MEJORADA: Ventas por día - BARRAS CLARAS
    graficaPrincipal = new Chart(ctxPrincipal, {
        type: 'bar',
        data: {
            labels: ventasPorDia.map(item => {
                const fecha = new Date(item.ven_fecha);
                return fecha.toLocaleDateString('es-GT', {
                    day: 'numeric',
                    month: 'short'
                });
            }),
            datasets: [{
                label: 'VENTAS DEL DÍA (Q)',
                data: ventasPorDia.map(item => parseFloat(item.total)),
                backgroundColor: '#3b82f6',
                borderColor: '#1d4ed8',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    callbacks: {
                        label: function (context) {
                            return `Ventas: Q ${context.parsed.y.toLocaleString('es-GT', { minimumFractionDigits: 2 })}`;
                        },
                        afterLabel: function (context) {
                            const item = ventasPorDia[context.dataIndex];
                            return `Transacciones: ${item.cantidad || 0}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function (value) {
                            return 'Q ' + value.toLocaleString('es-GT');
                        },
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    },
                    title: {
                        display: true,
                        text: 'MONTO EN QUETZALES',
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#374151'
                    },
                    title: {
                        display: true,
                        text: 'FECHAS',
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                }
            }
        }
    });

    // 🆕 GRÁFICA SECUNDARIA MEJORADA: Ventas por hora - MÁS CLARA
    graficaSecundaria = new Chart(ctxSecundaria, {
        type: 'bar',
        data: {
            labels: ventasPorHora.map(item => {
                const hora = parseInt(item.hora);
                const periodo = hora >= 12 ? 'PM' : 'AM';
                const hora12 = hora > 12 ? hora - 12 : hora;
                return `${hora12}:00 ${periodo}`;
            }),
            datasets: [{
                label: 'PROMEDIO POR HORA (Q)',
                data: ventasPorHora.map(item => parseFloat(item.promedio || 0)),
                backgroundColor: '#10b981',
                borderColor: '#047857',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    callbacks: {
                        label: function (context) {
                            return `Promedio: Q ${context.parsed.y.toLocaleString('es-GT', { minimumFractionDigits: 2 })}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function (value) {
                            return 'Q ' + value.toLocaleString('es-GT');
                        },
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    },
                    title: {
                        display: true,
                        text: 'PROMEDIO EN QUETZALES',
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#374151'
                    },
                    title: {
                        display: true,
                        text: 'HORAS DEL DÍA',
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                }
            }
        }
    });

    // Actualizar títulos con emojis para mejor comprensión
    document.getElementById('tituloGraficaPrincipal').textContent = '📊 VENTAS TOTALES POR DÍA';
    document.getElementById('tituloGraficaSecundaria').textContent = '🕒 PROMEDIO DE VENTAS POR HORA';
}

// Gráficas para productos
function generarGraficasProductos(datos, ctxPrincipal, ctxSecundaria) {
    const productosMasVendidos = datos.productos_mas_vendidos || [];
    const ventasPorCategoria = datos.ventas_por_categoria || [];

    // Gráfica principal: Productos más vendidos
    graficaPrincipal = new Chart(ctxPrincipal, {
        type: 'bar',
        data: {
            labels: productosMasVendidos.map(item =>
                item.prod_nombre.length > 15 ?
                    item.prod_nombre.substring(0, 15) + '...' :
                    item.prod_nombre
            ),
            datasets: [{
                label: 'Cantidad Vendida',
                data: productosMasVendidos.map(item => parseInt(item.total_vendido || 0)),
                backgroundColor: colores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function (context) {
                            const item = productosMasVendidos[context.dataIndex];
                            return `Ingresos: Q ${parseFloat(item.total_ingresos || 0).toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfica secundaria: Ventas por categoría
    graficaSecundaria = new Chart(ctxSecundaria, {
        type: 'doughnut',
        data: {
            labels: ventasPorCategoria.map(item => item.categoria),
            datasets: [{
                data: ventasPorCategoria.map(item => parseFloat(item.total || 0)),
                backgroundColor: colores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Actualizar títulos
    document.getElementById('tituloGraficaPrincipal').textContent = 'Productos Más Vendidos';
    document.getElementById('tituloGraficaSecundaria').textContent = 'Ventas por Categoría';
}

// Gráficas para caja
function generarGraficasCaja(datos, ctxPrincipal, ctxSecundaria) {
    const movimientosCaja = datos.movimientos_caja || [];
    const distribucionIngresos = datos.distribucion_ingresos || [];

    // Gráfica principal: Movimientos de caja
    graficaPrincipal = new Chart(ctxPrincipal, {
        type: 'bar',
        data: {
            labels: movimientosCaja.map(item => new Date(item.fecha).toLocaleDateString('es-GT', {
                month: 'short',
                day: 'numeric'
            })),
            datasets: [
                {
                    label: 'Ingresos',
                    data: movimientosCaja.map(item => parseFloat(item.ingresos || 0)),
                    backgroundColor: '#10b981',
                    borderWidth: 1
                },
                {
                    label: 'Egresos',
                    data: movimientosCaja.map(item => parseFloat(item.egresos || 0)),
                    backgroundColor: '#ef4444',
                    borderWidth: 1
                },
                {
                    label: 'Ventas',
                    data: movimientosCaja.map(item => parseFloat(item.ventas || 0)),
                    backgroundColor: '#3b82f6',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return 'Q ' + value.toLocaleString('es-GT');
                        }
                    }
                }
            }
        }
    });

    // Gráfica secundaria: Distribución de ingresos
    graficaSecundaria = new Chart(ctxSecundaria, {
        type: 'pie',
        data: {
            labels: distribucionIngresos.map(item =>
                item.cajamov_desc.length > 20 ?
                    item.cajamov_desc.substring(0, 20) + '...' :
                    item.cajamov_desc
            ),
            datasets: [{
                data: distribucionIngresos.map(item => parseFloat(item.total || 0)),
                backgroundColor: colores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Actualizar títulos
    document.getElementById('tituloGraficaPrincipal').textContent = 'Movimientos de Caja';
    document.getElementById('tituloGraficaSecundaria').textContent = 'Distribución de Ingresos';
}

// Gráficas para comparativa
function generarGraficasComparativa(datos, ctxPrincipal, ctxSecundaria) {
    const mesActual = datos.mes_actual || {};
    const mesAnterior = datos.mes_anterior || {};

    // Gráfica principal: Comparativa de ventas
    graficaPrincipal = new Chart(ctxPrincipal, {
        type: 'bar',
        data: {
            labels: ['Mes Actual', 'Mes Anterior'],
            datasets: [{
                label: 'Total Ventas (Q)',
                data: [
                    parseFloat(mesActual.total_ventas || 0),
                    parseFloat(mesAnterior.total_ventas || 0)
                ],
                backgroundColor: ['#3b82f6', '#93c5fd'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return 'Q ' + value.toLocaleString('es-GT');
                        }
                    }
                }
            }
        }
    });

    // Gráfica secundaria: Comparativa de métricas
    graficaSecundaria = new Chart(ctxSecundaria, {
        type: 'bar',
        data: {
            labels: ['Ventas Totales', 'Transacciones', 'Ticket Promedio'],
            datasets: [
                {
                    label: 'Mes Actual',
                    data: [
                        parseFloat(mesActual.total_ventas || 0),
                        parseFloat(mesActual.total_transacciones || 0),
                        parseFloat(mesActual.ticket_promedio || 0)
                    ],
                    backgroundColor: '#3b82f6',
                    borderWidth: 1
                },
                {
                    label: 'Mes Anterior',
                    data: [
                        parseFloat(mesAnterior.total_ventas || 0),
                        parseFloat(mesAnterior.total_transacciones || 0),
                        parseFloat(mesAnterior.ticket_promedio || 0)
                    ],
                    backgroundColor: '#ef4444',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Actualizar títulos
    document.getElementById('tituloGraficaPrincipal').textContent = 'Comparativa de Ventas Mensuales';
    document.getElementById('tituloGraficaSecundaria').textContent = 'Comparativa de Métricas';
}

// Actualizar estadísticas del período - VERSIÓN MEJORADA
function actualizarEstadisticas(datos, tipo) {
    const contenedor = document.getElementById('estadisticasResumen');
    let html = '';

    switch (tipo) {
        case 'ventas':
            const ventasPorDia = datos.ventas_por_dia || [];
            const totalVentas = ventasPorDia.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
            const totalTransacciones = ventasPorDia.reduce((sum, item) => sum + parseInt(item.cantidad || 0), 0);
            const promedioVenta = totalTransacciones > 0 ? totalVentas / totalTransacciones : 0;
            const diasConVentas = ventasPorDia.length;

            // Encontrar el mejor día
            let mejorDia = { total: 0 };
            ventasPorDia.forEach(item => {
                if (parseFloat(item.total) > parseFloat(mejorDia.total)) {
                    mejorDia = item;
                }
            });

            html = `
                <div class="text-center p-6 bg-blue-50 border-2 border-blue-200 rounded-xl shadow-sm">
                    <div class="text-3xl mb-2 text-blue-600">💰</div>
                    <p class="text-sm font-semibold text-blue-800 mb-1">VENTAS TOTALES</p>
                    <p class="text-2xl font-bold text-blue-600">Q ${totalVentas.toFixed(2)}</p>
                    <p class="text-xs text-blue-700 mt-2">${diasConVentas} días con ventas</p>
                </div>

                <div class="text-center p-6 bg-green-50 border-2 border-green-200 rounded-xl shadow-sm">
                    <div class="text-3xl mb-2 text-green-600">🛒</div>
                    <p class="text-sm font-semibold text-green-800 mb-1">TRANSACCIONES</p>
                    <p class="text-2xl font-bold text-green-600">${totalTransacciones}</p>
                    <p class="text-xs text-green-700 mt-2">Total de ventas</p>
                </div>

                <div class="text-center p-6 bg-purple-50 border-2 border-purple-200 rounded-xl shadow-sm">
                    <div class="text-3xl mb-2 text-purple-600">🎯</div>
                    <p class="text-sm font-semibold text-purple-800 mb-1">TICKET PROMEDIO</p>
                    <p class="text-2xl font-bold text-purple-600">Q ${promedioVenta.toFixed(2)}</p>
                    <p class="text-xs text-purple-700 mt-2">Por transacción</p>
                </div>

                <div class="text-center p-6 bg-orange-50 border-2 border-orange-200 rounded-xl shadow-sm">
                    <div class="text-3xl mb-2 text-orange-600">📊</div>
                    <p class="text-sm font-semibold text-orange-800 mb-1">MEJOR DÍA</p>
                    <p class="text-lg font-bold text-orange-600">Q ${parseFloat(mejorDia.total || 0).toFixed(2)}</p>
                    <p class="text-xs text-orange-700 mt-2">${mejorDia.ven_fecha ? new Date(mejorDia.ven_fecha).toLocaleDateString('es-GT', {day: 'numeric', month: 'short'}) : 'N/A'}</p>
                </div>
            `;
            break;
        default:
            html = `
                <div class="col-span-4 text-center py-8 bg-gray-50 rounded-xl">
                    <div class="text-4xl mb-4">📈</div>
                    <p class="text-gray-600 font-semibold">Selecciona un tipo de gráfica</p>
                    <p class="text-gray-500 text-sm mt-1">Haz clic en "Generar Gráficas" para ver las estadísticas</p>
                </div>
            `;
    }

    contenedor.innerHTML = html;
}

// Actualizar tabla de datos - VERSIÓN MEJORADA
function actualizarTablaDatos(datos, tipo) {
    const contenedor = document.getElementById('tablaDatos');
    let html = '';

    switch (tipo) {
        case 'ventas':
            const ventasPorDia = datos.ventas_por_dia || [];
            if (ventasPorDia.length === 0) {
                html = `
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <div class="text-6xl mb-4">📭</div>
                        <p class="text-gray-600 text-lg font-semibold mb-2">No hay ventas registradas</p>
                        <p class="text-gray-500 text-sm">No se encontraron ventas en el período seleccionado</p>
                    </div>
                `;
            } else {
                // Calcular totales
                const totalVentas = ventasPorDia.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
                const totalTransacciones = ventasPorDia.reduce((sum, item) => sum + parseInt(item.cantidad || 0), 0);

                html = `
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="text-blue-600 font-bold text-2xl">Q ${totalVentas.toFixed(2)}</div>
                            <div class="text-blue-800 text-sm">VENTAS TOTALES</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="text-green-600 font-bold text-2xl">${totalTransacciones}</div>
                            <div class="text-green-800 text-sm">TRANSACCIONES</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <div class="text-purple-600 font-bold text-2xl">${ventasPorDia.length}</div>
                            <div class="text-purple-800 text-sm">DÍAS CON VENTAS</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b">FECHA</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b">TOTAL VENTAS</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b">CANT. VENTAS</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b">PROMEDIO/VENTA</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${ventasPorDia.map(item => {
                    const promedio = parseInt(item.cantidad) > 0 ?
                        parseFloat(item.total) / parseInt(item.cantidad) : 0;
                    const fecha = new Date(item.ven_fecha);
                    const fechaFormateada = fecha.toLocaleDateString('es-GT', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short'
                    });

                    return `
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                ${fechaFormateada}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-green-600">
                                                Q ${parseFloat(item.total || 0).toFixed(2)}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    ${item.cantidad || 0} ventas
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-700">
                                                Q ${promedio.toFixed(2)}
                                            </td>
                                        </tr>
                                    `;
                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }
            break;
        default:
            html = `
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📈</div>
                    <p class="text-gray-600 text-lg font-semibold mb-2">Selecciona un tipo de gráfica</p>
                    <p class="text-gray-500 text-sm">Haz clic en "Generar Gráficas" para ver los datos</p>
                </div>
            `;
    }

    contenedor.innerHTML = html;
}


btnGenerarGraficas.addEventListener("click", cargarDatosGraficas);


document.addEventListener('DOMContentLoaded', function () {
    inicializarFechas();
});