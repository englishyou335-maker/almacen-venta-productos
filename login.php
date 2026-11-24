<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

// Si ya hay sesión, entra directo al panel.
if (usuarioActual() !== null) {
    header('Location: /admin/dashboard.php');
    exit;
}
$pageTitle = 'Acceso administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111d24">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/logo/favicon-32.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/assets/img/logo/favicon-192.png">
    <title><?= e($pageTitle) ?> — Mi Almacén</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php require_once __DIR__ . '/includes/tailwind-config.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="hero-fondo min-h-screen flex items-center justify-center p-4 text-ink-800">
    <div class="absolute inset-0 hero-malla opacity-50 pointer-events-none"></div>
    <div class="relative w-full max-w-md">
        <div class="text-center text-white mb-7">
            <span class="w-16 h-16 mx-auto rounded-3xl bg-brand-500 text-ink-950 grid place-items-center text-3xl shadow-lift">
                <i class="fa-solid fa-basket-shopping"></i>
            </span>
            <h1 class="mt-5 font-display text-3xl font-extrabold">Panel de administración</h1>
            <p class="text-slate-400 text-sm mt-2">Ingresa con tu cuenta para gestionar el almacén.</p>
        </div>

        <form id="form-login" class="rounded-3xl border border-white/60 bg-white p-7 shadow-lift sm:p-9">
            <?= csrfField() ?>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
                <span class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 grid place-items-center"><i class="fa-solid fa-lock"></i></span>
                <div>
                    <h2 class="font-display text-lg font-bold text-ink-900">Iniciar sesión</h2>
                    <p class="text-xs text-ink-500">Solo personal autorizado.</p>
                </div>
            </div>

            <label class="block">
                <span class="text-sm font-semibold text-ink-800">Email</span>
                <div class="relative mt-2">
                    <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <input id="login-email" type="email" required autocomplete="username"
                           value="admin@gmail.com"
                           class="w-full rounded-xl border border-slate-200 bg-cream py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
                </div>
            </label>
            <label class="block mt-5">
                <span class="text-sm font-semibold text-ink-800">Contraseña</span>
                <div class="relative mt-2">
                    <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <input id="login-password" type="password" required autocomplete="current-password"
                           placeholder="Tu contraseña"
                           class="w-full rounded-xl border border-slate-200 bg-cream py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
                </div>
            </label>
            <button id="btn-login" type="submit"
                    class="mt-7 w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white shadow-soft transition hover:bg-brand-700 active:scale-[0.99]">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Ingresar
            </button>
        </form>

        <p class="text-center text-slate-400 text-xs mt-6">
            <a href="/index.php" class="hover:text-brand-300 transition"><i class="fa-solid fa-arrow-left mr-2"></i>Volver al catálogo</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('form-login').addEventListener('submit', async (event) => {
        event.preventDefault();
        const boton = document.getElementById('btn-login');
        boton.disabled = true;
        boton.textContent = 'Verificando...';

        try {
            const respuesta = await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content,
                    email: document.getElementById('login-email').value.trim(),
                    password: document.getElementById('login-password').value
                })
            });
            const datos = await respuesta.json();
            if (datos.success) {
                window.location.href = '/admin/dashboard.php';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo ingresar',
                    html: (datos.errors || ['Error desconocido']).map((e) => `• ${e}`).join('<br>')
                });
            }
        } catch (error) {
            Swal.fire('Error de conexión', 'Inténtalo de nuevo en unos segundos.', 'error');
        } finally {
            boton.disabled = false;
            boton.innerHTML = '<i class="fa-solid fa-right-to-bracket mr-2"></i> Ingresar';
        }
    });
    </script>
</body>
</html>
