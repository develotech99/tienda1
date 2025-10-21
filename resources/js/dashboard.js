// dashboard.js - Script separado para el dashboard

class Dashboard {
    constructor() {
        this.chart = null;
        this.init();
    }

    async init() {
        // Primero cargar Chart.js dinámicamente
        await this.cargarChartJS();
        // Luego cargar los datos del dashboard
        this.cargarDashboard();
        setInterval(() => this.cargarDashboard(), 120000); // Actualizar cada 2 minutos
    }

    async cargarChartJS() {
        if (typeof Chart === 'undefined') {
            // Cargar Chart.js dinámicamente
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        return Promise.resolve();
    }

    async cargarDashboard() {
        try {
            const response = await fetch('/dashboard/estadisticas');
            const data = await response.json();

            if (data.success) {
                this.actualizarEstadisticas(data.estadisticas);
                this.actualizarGraficoVentas(data.ventas_semana);
                this.actualizarAlertasStock(data.alertas_stock);
                this.actualizarActividadReciente(data.actividad_reciente); // ✅ AGREGAR ESTA LÍNEA
            }
        } catch (error) {
            console.error('Error al cargar dashboard:', error);
        }
    }

    actualizarEstadisticas(estadisticas) {
        // Ventas hoy
        if (document.getElementById('ventas-hoy')) {
            document.getElementById('ventas-hoy').textContent =
                'Q ' + this.formatearMoneda(estadisticas.ventas_hoy || 0);
        }

        if (document.getElementById('transacciones-hoy')) {
            document.getElementById('transacciones-hoy').textContent =
                (estadisticas.transacciones_hoy || 0) + ' transacciones';
        }

        // Productos
        if (document.getElementById('total-productos')) {
            document.getElementById('total-productos').textContent =
                (estadisticas.total_productos || 0).toLocaleString();
        }

        if (document.getElementById('total-categorias')) {
            document.getElementById('total-categorias').textContent =
                (estadisticas.total_categorias || 0) + ' categorías';
        }

        // Caja
        if (document.getElementById('saldo-caja')) {
            if (estadisticas.caja_abierta) {
                document.getElementById('saldo-caja').textContent =
                    'Q ' + this.formatearMoneda(estadisticas.caja_abierta.caja_saldo || 0);
                document.getElementById('estado-caja').textContent = 'ABIERTA';
                document.getElementById('estado-caja').className =
                    'text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full';
                document.getElementById('nombre-caja').textContent = estadisticas.caja_abierta.caja_nombre || 'Caja Principal';
            } else {
                document.getElementById('saldo-caja').textContent = 'Q 0.00';
                document.getElementById('estado-caja').textContent = 'CERRADA';
                document.getElementById('estado-caja').className =
                    'text-xs font-medium text-gray-600 bg-gray-50 px-2 py-1 rounded-full';
                document.getElementById('nombre-caja').textContent = 'Sin caja abierta';
            }
        }

        // Alertas stock
        if (document.getElementById('total-stock-bajo')) {
            document.getElementById('total-stock-bajo').textContent =
                (estadisticas.stock_bajo || 0).toLocaleString();
        }

        if (document.getElementById('total-sin-stock')) {
            document.getElementById('total-sin-stock').textContent =
                (estadisticas.sin_stock || 0) + ' sin stock';
        }

        if (document.getElementById('alertas-stock')) {
            document.getElementById('alertas-stock').textContent =
                'CRÍTICO: ' + (estadisticas.sin_stock || 0);
        }

        // Estadísticas rápidas
        if (document.getElementById('movimientos-hoy')) {
            document.getElementById('movimientos-hoy').textContent =
                (estadisticas.movimientos_hoy || 0).toLocaleString();
        }

        if (document.getElementById('productos-activos')) {
            document.getElementById('productos-activos').textContent =
                (estadisticas.total_productos || 0).toLocaleString();
        }
    }

    actualizarGraficoVentas(ventasSemana) {
        const canvas = document.getElementById('graficoVentas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Verificar que Chart esté disponible
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no está cargado');
            return;
        }

        // Destruir gráfica anterior si existe
        if (this.chart) {
            this.chart.destroy();
        }

        // Crear nueva gráfica
        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ventasSemana.dias || ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas (Q)',
                    data: ventasSemana.ventas || [0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#3b82f6',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 4
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
                            label: function (context) {
                                return 'Q ' + context.parsed.y.toLocaleString('es-GT', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
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
    }

    actualizarAlertasStock(alertas) {
        const container = document.getElementById('alertas-stock-lista');
        const contador = document.getElementById('contador-alertas');

        if (!container) return;

        if (contador) {
            contador.textContent = alertas ? alertas.length : 0;
        }

        if (!alertas || alertas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-gray-500">¡Todo bajo control!</p>
                    <p class="text-gray-400 text-sm">No hay alertas de stock</p>
                </div>
            `;
            return;
        }

        let html = '';
        alertas.forEach(alerta => {
            const colores = {
                'red': { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-700' },
                'orange': { bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-700' },
                'yellow': { bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-700' }
            };

            const color = colores[alerta.color] || colores.yellow;

            html += `
                <div class="p-3 ${color.bg} border ${color.border} rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800 text-sm">${alerta.nombre}</p>
                            <p class="text-xs ${color.text}">${alerta.texto_alerta}</p>
                        </div>
                        <a href="/productos/${alerta.id}/edit" class="text-xs ${color.text} font-medium hover:underline">
                            →
                        </a>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // ✅ NUEVO MÉTODO PARA ACTUALIZAR ACTIVIDAD RECIENTE
    actualizarActividadReciente(actividades) {
        const container = document.getElementById('actividad-reciente-lista');
        if (!container) return;

        if (!actividades || actividades.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-gray-500">No hay actividad reciente</p>
                    <p class="text-gray-400 text-sm">Las actividades aparecerán aquí</p>
                </div>
            `;
            return;
        }

        let html = '';
        actividades.forEach(actividad => {
            const colores = {
                'emerald': { bg: 'bg-emerald-100', text: 'text-emerald-600' },
                'blue': { bg: 'bg-blue-100', text: 'text-blue-600' },
                'green': { bg: 'bg-green-100', text: 'text-green-600' }
            };

            const color = colores[actividad.color] || colores.emerald;

            // Icono según el tipo de actividad
            let icono = '';
            switch (actividad.icono) {
                case 'success':
                    icono = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    break;
                case 'add':
                    icono = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>';
                    break;
                case 'package':
                    icono = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                    break;
                default:
                    icono = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>';
            }

            html += `
                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 ${color.bg} rounded-lg flex-shrink-0">
                        <div class="${color.text}">
                            ${icono}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">${actividad.mensaje}</p>
                        <p class="text-xs text-gray-600 mt-1">${actividad.detalle}</p>
                        <p class="text-xs text-gray-400 mt-1">${actividad.tiempo}</p>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    formatearMoneda(valor) {
        return parseFloat(valor || 0).toLocaleString('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

// Inicializar dashboard cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    new Dashboard();
});