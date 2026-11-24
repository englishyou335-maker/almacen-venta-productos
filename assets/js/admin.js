/* Utilidades compartidas del panel admin. */
document.addEventListener('DOMContentLoaded', () => {
    // Logout mediante la API existente.
    const logout = async () => {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        try {
            await fetch('/api/logout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: token })
            });
        } finally {
            window.location.href = '/login.php';
        }
    };
    document.getElementById('btn-logout')?.addEventListener('click', logout);
    document.getElementById('btn-logout-movil')?.addEventListener('click', logout);

    // Menú móvil: drawer con backdrop, cierre por Escape/fuera y reset en escritorio.
    const botonMenu = document.getElementById('btn-menu');
    const menuMovil = document.getElementById('menu-movil');
    const backdrop = document.getElementById('menu-backdrop');

    const cerrarMenu = () => {
        menuMovil?.classList.add('hidden');
        backdrop?.classList.add('hidden');
        botonMenu?.setAttribute('aria-expanded', 'false');
        if (botonMenu) botonMenu.innerHTML = '<i class="fa-solid fa-bars"></i>';
    };
    const abrirMenu = () => {
        menuMovil?.classList.remove('hidden');
        backdrop?.classList.remove('hidden');
        botonMenu?.setAttribute('aria-expanded', 'true');
        if (botonMenu) botonMenu.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    };

    botonMenu?.addEventListener('click', () => {
        if (menuMovil?.classList.contains('hidden')) abrirMenu();
        else cerrarMenu();
    });
    backdrop?.addEventListener('click', cerrarMenu);
    menuMovil?.querySelectorAll('a').forEach((enlace) => enlace.addEventListener('click', cerrarMenu));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') cerrarMenu();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) cerrarMenu();
    });

    // Confirmación para acciones destructivas en formularios clásicos.
    document.querySelectorAll('[data-confirmar]').forEach((boton) => {
        boton.addEventListener('click', async (event) => {
            event.preventDefault();
            const resultado = await Swal.fire({
                title: '¿Estás seguro?',
                text: boton.dataset.confirmar,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            });
            if (resultado.isConfirmed) {
                boton.closest('form')?.submit();
            }
        });
    });
});

window.adminMensaje = function (texto, icono = 'success') {
    Swal.fire({ icon: icono, title: texto, timer: 2000, showConfirmButton: false });
};

/** Petición JSON con token CSRF, usada por los módulos del panel. */
window.adminFetch = async function (url, datos) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const respuesta = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...datos, csrf_token: token })
    });
    return respuesta.json();
};
