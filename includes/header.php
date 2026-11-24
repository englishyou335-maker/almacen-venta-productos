<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// La sesión debe iniciarse ANTES de imprimir cualquier HTML: así se puede
// enviar la cookie de sesión y el token CSRF funciona en la primera visita.
csrfToken();

$pageTitle = $pageTitle ?? 'Inicio';
$extraScripts = $extraScripts ?? [];
$scriptActual = basename($_SERVER['SCRIPT_NAME'] ?? '');

/** Resalta el enlace de la página actual en ambas navegaciones. */
function navActivo(string $archivo, string $actual): string
{
    return $archivo === $actual
        ? 'bg-brand-50 text-brand-700'
        : 'text-ink-600 hover:bg-slate-100 hover:text-ink-900';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111d24">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/logo/favicon-32.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/assets/img/logo/favicon-512.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/assets/img/logo/favicon-192.png">
    <meta name="description" content="Catálogo de productos con reserva previa y retiro en tienda. Sin pagos online.">
    <title><?= e($pageTitle) ?> — Mi Almacén</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php require_once __DIR__ . '/tailwind-config.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="bg-cream text-ink-800 font-sans antialiased min-h-screen flex flex-col">

<!-- Franja superior: argumentos clave del sistema. -->
<div class="bg-ink-950 text-white/70 text-[11px] sm:text-xs">
    <div class="max-w-7xl mx-auto px-4 py-2 flex flex-wrap items-center justify-between gap-x-6 gap-y-1">
        <span class="flex items-center gap-2"><i class="fa-solid fa-store text-brand-400"></i> Retiro en tienda · sin pagos online</span>
        <span class="hidden sm:flex items-center gap-2"><i class="fa-solid fa-circle-check text-brand-400"></i> Consulta la disponibilidad antes de reservar</span>
    </div>
</div>

<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-lg border-b border-slate-200/80 shadow-sm">
    <div class="max-w-7xl mx-auto px-4">
        <div class="h-16 flex items-center justify-between gap-3">
            <a href="/index.php" class="flex items-center gap-3 group shrink-0">
                <img src="/assets/img/logo/logo-160.webp"
                     srcset="/assets/img/logo/logo-160.webp 160w, /assets/img/logo/logo-320.webp 320w, /assets/img/logo/logo-640.webp 640w"
                     sizes="(min-width: 640px) 48px, 40px"
                     width="48" height="48"
                     alt="Mi Almacén Laura - Murillo"
                     class="h-10 w-10 sm:h-11 sm:w-11 object-contain rounded-2xl bg-white ring-1 ring-slate-100 shadow-soft group-hover:rotate-6 transition-transform">
                <span class="leading-tight">
                    <span class="block font-display font-extrabold text-lg text-ink-900 tracking-tight">Mi Almacén Laura</span>
                    <span class="block text-[9px] uppercase tracking-[0.22em] text-brand-600 font-bold">Murillo · Catálogo y reservas</span>
                </span>
            </a>

            <div class="flex items-center gap-1 sm:gap-2">
                <nav class="hidden md:flex items-center gap-1 text-sm font-semibold" aria-label="Navegación principal">
                    <a href="/index.php" class="px-3 py-2 rounded-xl transition <?= navActivo('index.php', $scriptActual) ?>">
                        <i class="fa-solid fa-house mr-2"></i>Inicio
                    </a>
                    <a href="/productos.php" class="px-3 py-2 rounded-xl transition <?= navActivo('productos.php', $scriptActual) ?>">
                        <i class="fa-solid fa-table-cells mr-2"></i>Productos
                    </a>
                    <a href="<?= e(WHATSAPP_ADMIN_URL) ?>" target="_blank" rel="noopener noreferrer"
                       class="px-3 py-2 rounded-xl transition text-ink-600 hover:bg-slate-100 hover:text-ink-900">
                        <i class="fa-brands fa-whatsapp mr-2 text-brand-600"></i>WhatsApp
                    </a>
                </nav>

                <a href="/carrito/carrito.php" aria-label="Ver carrito"
                   class="relative h-10 px-3 sm:px-4 rounded-xl bg-ink-900 text-white hover:bg-brand-700 transition flex items-center gap-2 text-sm font-semibold">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="hidden lg:inline">Mi carrito</span>
                    <span id="carrito-contador"
                          class="absolute -top-2 -right-2 min-w-5 h-5 px-1 rounded-full bg-brand-400 text-ink-950 text-[11px] font-extrabold grid place-items-center ring-2 ring-white">0</span>
                </a>

                <button id="btn-menu-publico" type="button" aria-expanded="false" aria-controls="menu-publico"
                        class="md:hidden h-10 w-10 rounded-xl border border-slate-200 text-ink-800 hover:bg-slate-100 transition grid place-items-center"
                        aria-label="Abrir menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú móvil -->
    <div id="menu-publico" class="hidden md:hidden border-t border-slate-100 bg-white px-4 pb-4 pt-2">
        <nav class="grid gap-1 text-sm font-semibold" aria-label="Navegación móvil">
            <a href="/index.php" class="px-3 py-2.5 rounded-xl <?= navActivo('index.php', $scriptActual) ?>"><i class="fa-solid fa-house mr-3"></i>Inicio</a>
            <a href="/productos.php" class="px-3 py-2.5 rounded-xl <?= navActivo('productos.php', $scriptActual) ?>"><i class="fa-solid fa-table-cells mr-3"></i>Productos</a>
            <a href="<?= e(WHATSAPP_ADMIN_URL) ?>" target="_blank" rel="noopener noreferrer" class="px-3 py-2.5 rounded-xl text-ink-600 hover:bg-slate-100 hover:text-ink-900"><i class="fa-brands fa-whatsapp mr-3 text-brand-600"></i>WhatsApp</a>
            <a href="/carrito/carrito.php" class="px-3 py-2.5 rounded-xl <?= navActivo('carrito.php', $scriptActual) ?>"><i class="fa-solid fa-cart-shopping mr-3"></i>Mi carrito</a>
        </nav>
    </div>
</header>

<main class="flex-1">
