// Dashboard específico para vendedores
import { Loader } from "./app";

class DashboardVendedor {
    constructor() {
        this.token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
        this.init();
    }

    init() {
        console.log('Inicializando dashboard vendedor...');
        this.cargarVentasHoy();
        this.actualizarAutomaticamente();
    }

    async cargarVentasHoy() {
        try {
            const url = '/dashboard/estadisticas';
            const config = {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            };

            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();

            if (datos.success && datos.estadisticas) {
                this.actualizarUI(datos.estadisticas);
            } else {
                console.warn('No se pudieron cargar las estadísticas:', datos.message);
            }
        } catch (error) {
            console.error('Error cargando ventas del día:', error);
            this.mostrarError('Error al cargar datos');
        }
    }

    actualizarUI(estadisticas) {
        // Actualizar ventas hoy
        this.actualizarElementoSiExiste('ventas-hoy-vendedor', `Q ${this.formatearNumero(estadisticas.ventas_hoy)}`);
        
        // Actualizar transacciones
        this.actualizarElementoSiExiste('transacciones-hoy-vendedor', `${estadisticas.transacciones_hoy} transacciones`);

        // Actualizar indicador del header si existe
        this.actualizarElementoSiExiste('ventas-hoy-indicador', `Ventas hoy: Q ${this.formatearNumero(estadisticas.ventas_hoy)}`);

        console.log('Dashboard vendedor actualizado:', estadisticas);
    }

    actualizarElementoSiExiste(id, texto) {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.textContent = texto;
        }
    }

    formatearNumero(numero) {
        return new Intl.NumberFormat('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(numero);
    }

    mostrarError(mensaje) {
        console.error(mensaje);
        // Podrías mostrar una notificación suave aquí si quieres
        this.actualizarElementoSiExiste('ventas-hoy-vendedor', 'Q 0.00');
        this.actualizarElementoSiExiste('transacciones-hoy-vendedor', '0 transacciones');
    }

    actualizarAutomaticamente() {
        // Actualizar cada 2 minutos
        setInterval(() => {
            this.cargarVentasHoy();
        }, 120000); // 120,000 ms = 2 minutos

        // También actualizar cuando la página gana foco
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.cargarVentasHoy();
            }
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    new DashboardVendedor();
});

// También exportar para uso global si es necesario
window.DashboardVendedor = DashboardVendedor;