
(() => {
    const qs = (s, r = document) => r.querySelector(s);
    const byId = (id) => document.getElementById(id);
    const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const loadingBtn = (btn, loading, text = 'Enviar enlace') => {
        if (!btn) return;
        btn.disabled = !!loading;
        btn.textContent = loading ? 'Enviando…' : text;
    };

    document.addEventListener('DOMContentLoaded', () => {
        const form = byId('forgotForm') || qs('form[action*="password/email"]');
        if (!form) return;

        const email = byId('email') || qs('input[type="email"]', form);
        const btn = qs('button[type="submit"]', form);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!email?.value?.trim()) {
                Swal.fire({ icon: 'warning', title: 'Campo vacío', text: 'Ingresa tu correo.', confirmButtonColor: '#059669' });
                return;
            }

            loadingBtn(btn, true);

            try {
                const res = await fetch(form.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: email.value.trim() }),
                    credentials: 'same-origin',
                });

                if (res.ok && (res.status === 200 || res.status === 204)) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Revisa tu correo',
                        text: 'Si existe una cuenta asociada, te enviamos un enlace de restablecimiento.',
                        confirmButtonColor: '#059669',
                    });
                    window.location.assign('/login');
                    return;
                }

                let data = {};
                try { data = await res.json(); } catch { }

                if (res.status === 422 && data?.errors?.email?.[0]) {
                    Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: data.errors.email[0], confirmButtonColor: '#059669' });
                } else if (res.status === 419) {
                    Swal.fire({ icon: 'info', title: 'Sesión expirada', text: 'Actualiza la página e inténtalo de nuevo.', confirmButtonColor: '#059669' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data?.message || 'No se pudo enviar el enlace.', confirmButtonColor: '#059669' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'Inténtalo más tarde.', confirmButtonColor: '#059669' });
            } finally {
                loadingBtn(btn, false);
            }
        });
    });
})();
// resources/js/auth/forgot.js
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

(() => {
    const qs = (s, r = document) => r.querySelector(s);
    const byId = (id) => document.getElementById(id);
    const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const loadingBtn = (btn, loading, text = 'Enviar enlace') => {
        if (!btn) return;
        btn.disabled = !!loading;
        btn.textContent = loading ? 'Enviando…' : text;
    };

    document.addEventListener('DOMContentLoaded', () => {
        const form = byId('forgotForm') || qs('form[action*="password/email"]');
        if (!form) return;

        const email = byId('email') || qs('input[type="email"]', form);
        const btn = qs('button[type="submit"]', form);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!email?.value?.trim()) {
                Swal.fire({ icon: 'warning', title: 'Campo vacío', text: 'Ingresa tu correo.', confirmButtonColor: '#059669' });
                return;
            }

            loadingBtn(btn, true);

            try {
                const res = await fetch(form.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: email.value.trim() }),
                    credentials: 'same-origin',
                });

                if (res.ok && (res.status === 200 || res.status === 204)) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Revisa tu correo',
                        text: 'Si existe una cuenta asociada, te enviamos un enlace de restablecimiento.',
                        confirmButtonColor: '#059669',
                    });
                    window.location.assign('/login');
                    return;
                }

                let data = {};
                try { data = await res.json(); } catch { }

                if (res.status === 422 && data?.errors?.email?.[0]) {
                    Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: data.errors.email[0], confirmButtonColor: '#059669' });
                } else if (res.status === 419) {
                    Swal.fire({ icon: 'info', title: 'Sesión expirada', text: 'Actualiza la página e inténtalo de nuevo.', confirmButtonColor: '#059669' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data?.message || 'No se pudo enviar el enlace.', confirmButtonColor: '#059669' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'Inténtalo más tarde.', confirmButtonColor: '#059669' });
            } finally {
                loadingBtn(btn, false);
            }
        });
    });
})();
