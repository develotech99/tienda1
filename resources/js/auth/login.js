// resources/js/auth/login.js
import Swal from 'sweetalert2';

(() => {
    const qs = (s, r = document) => r.querySelector(s);
    const byId = (id) => document.getElementById(id);
    const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true,
    });

    const fieldError = (el, msg) => {
        if (!el) return;
        el.textContent = msg || '';
        el.classList.toggle('hidden', !msg);
    };

    const setLoading = (btn, loading, text = 'Iniciar sesión') => {
        if (!btn) return;
        btn.disabled = !!loading;
        if (loading) {
            btn.dataset.label = text;
            btn.innerHTML = `
        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"></path>
        </svg>
        <span class="ml-2">Ingresando…</span>`;
        } else {
            btn.innerHTML = btn.dataset.label || text;
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const root = byId('loginContainer');
        if (!root) return;

        const email = byId('email');
        const password = byId('password');
        const emailError = byId('emailError');
        const passwordError = byId('passwordError');
        const remember = byId('remember');
        const btnLogin = byId('loginBtn');
        const btnToggle = byId('togglePass');
        const goRegisterBtn = byId('goRegister');

        // Toggle password
        btnToggle?.addEventListener('click', () => {
            const isPwd = password.type === 'password';
            password.type = isPwd ? 'text' : 'password';
            btnToggle.textContent = isPwd ? '🙈' : '👁️';
        });

        // Enter submit
        [email, password].forEach(el => el?.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnLogin?.click();
            }
        }));

        // Go register (si usas botón en vez de <a>)
        goRegisterBtn?.addEventListener('click', () => {
            const url = root.dataset.routeRegister || '/register';
            window.location.assign(url);
        });

        const validate = () => {
            let ok = true;
            fieldError(emailError, '');
            fieldError(passwordError, '');
            if (!email?.value?.trim()) {
                fieldError(emailError, 'Ingresa tu correo.');
                ok = false;
            }
            if (!password?.value) {
                fieldError(passwordError, 'Ingresa tu contraseña.');
                ok = false;
            }
            if (!ok) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Revisa el correo y la contraseña.',
                    confirmButtonColor: '#059669',
                });
            }
            return ok;
        };

        btnLogin?.addEventListener('click', async () => {
            if (!validate()) return;

            const endpoint = root.dataset.endpointLogin?.trim() || '/login';
            const redirectTo = root.dataset.redirectSuccess?.trim() || '/dashboard';

            const body = {
                email: email.value.trim(),
                password: password.value,
                remember: !!(remember && remember.checked),
            };

            setLoading(btnLogin, true);

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                    credentials: 'same-origin',
                });

                if (res.ok && (res.status === 204 || res.status === 200)) {
                    await toast.fire({ icon: 'success', title: '¡Bienvenido!' });
                    window.location.assign(redirectTo);
                    return;
                }

                let data = {};
                try { data = await res.json(); } catch { }

                if (res.status === 422 && data?.errors) {
                    if (data.errors.email?.[0]) fieldError(emailError, data.errors.email[0]);
                    if (data.errors.password?.[0]) fieldError(passwordError, data.errors.password[0]);
                    Swal.fire({ icon: 'error', title: 'No se pudo iniciar sesión', text: data.message || 'Verifica tus datos.', confirmButtonColor: '#059669' });
                } else if (res.status === 419) {
                    Swal.fire({ icon: 'info', title: 'Sesión expirada', text: 'Actualiza la página e intenta de nuevo.', confirmButtonColor: '#059669' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data?.message || 'Credenciales inválidas.', confirmButtonColor: '#059669' });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'Revisa tu conexión.', confirmButtonColor: '#059669' });
            } finally {
                setLoading(btnLogin, false);
            }
        });
    });
})();
