import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

(() => {
    const qs = (s, r = document) => r.querySelector(s);
    const byId = (id) => document.getElementById(id);
    const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const loadingBtn = (btn, loading, text = 'Guardar contraseña') => {
        if (!btn) return;
        btn.disabled = !!loading;
        btn.textContent = loading ? 'Guardando…' : text;
    };

    document.addEventListener('DOMContentLoaded', () => {
        const form = qs('form[action*="password"]');
        if (!form) return;

        const email = byId('email');
        const password = byId('password');
        const password2 = byId('password_confirmation');
        const token = qs('input[name="token"]', form);
        const btn = qs('button[type="submit"]', form);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!email?.value?.trim() || !password?.value || !password2?.value) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Campos incompletos', 
                    text: 'Completa todos los campos.', 
                    confirmButtonColor: '#059669' 
                });
                return;
            }

            if (password.value !== password2.value) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Contraseñas no coinciden', 
                    text: 'Verifica que ambas contraseñas sean iguales.', 
                    confirmButtonColor: '#059669' 
                });
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
                    body: JSON.stringify({
                        token: token?.value || '',
                        email: email.value.trim(),
                        password: password.value,
                        password_confirmation: password2.value,
                    }),
                    credentials: 'same-origin',
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Contraseña actualizada',
                        text: 'Ahora puedes iniciar sesión con tu nueva contraseña.',
                        confirmButtonColor: '#059669',
                    });
                    window.location.href = '/login';
                    return;
                }

                if (res.status === 422 && data?.errors) {
                    const firstError = Object.values(data.errors)[0]?.[0];
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error de validación', 
                        text: firstError || 'Revisa la información ingresada.', 
                        confirmButtonColor: '#059669' 
                    });
                } else if (res.status === 419) {
                    Swal.fire({ 
                        icon: 'info', 
                        title: 'Sesión expirada', 
                        text: 'Actualiza la página e intenta nuevamente.', 
                        confirmButtonColor: '#059669' 
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: data?.message || 'No se pudo restablecer la contraseña.', 
                        confirmButtonColor: '#059669' 
                    });
                }
            } catch (err) {
                console.error('Error al restablecer contraseña:', err);
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error de conexión', 
                    text: 'Verifica tu conexión e intenta más tarde.', 
                    confirmButtonColor: '#059669' 
                });
            } finally {
                loadingBtn(btn, false);
            }
        });
    });
})();