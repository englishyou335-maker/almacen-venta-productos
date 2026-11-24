<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

verificarLogin();
$usuario = usuarioActual();
$pageTitle = $pageTitle ?? 'Panel';
$extraScripts = $extraScripts ?? [];
$seccion = $seccion ?? '';

$enlaces = [
    ['dashboard.php', 'Dashboard', 'fa-gauge-high', 'dashboard'],
    ['productos.php', 'Productos', 'fa-box', 'productos'],
    ['categorias.php', 'Categorías', 'fa-layer-group', 'categorias'],
    ['reservas.php', 'Reservas', 'fa-clipboard-list', 'reservas'],
    ['qr-pago.php', 'QR de pago', 'fa-qrcode', 'qr-pago'],
];

/** Clase del enlace activo del sidebar. */
function adminNavActivo(string $clave, string $seccion): string
{
    return $clave === $seccion
        ? 'bg-brand-500/15 text-brand-200 ring-1 ring-brand-400/20'
        : 'text-slate-400 hover:bg-white/5 hover:text-white';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111d24">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($pageTitle) ?> — Panel | Mi Almacén</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php require_once __DIR__ . '/tailwind-config.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/3.0.0/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="bg-cream text-ink-800 font-sans antialiased min-h-screen flex flex-col">

<!-- Sidebar -->
<aside id="sidebar" class="w-64 min-h-screen flex-shrink-0 bg-ink-950 text-slate-300 hidden md:flex flex-col fixed inset-y-0 left-0 z-30">
    <div class="px-5 py-6 border-b border-white/10">
        <a href="/admin/dashboard.php" class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl bg-brand-500 text-ink-950 grid place-items-center text-lg shadow-lift">
                <i class="fa-solid fa-basket-shopping"></i>
            </span>
            <span class="leading-tight">
                <span class="block font-display font-extrabold text-lg text-white">Mi Almacén</span>
                <span class="block text-[9px] uppercase tracking-[0.2em] text-brand-300 font-bold">Administración</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 px-3 py-5 space-y-1 text-sm" aria-label="Menú del panel">
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600">Menú principal</p>
        <?php foreach ($enlaces as [$archivo, $etiqueta, $icono, $clave]): ?>
            <a href="/admin/<?= $archivo ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= adminNavActivo($clave, $seccion) ?>">
                <i class="fa-solid <?= e($icono) ?> w-5 text-center"></i><?= e($etiqueta) ?>
                <?php if ($clave === $seccion): ?><i class="fa-solid fa-chevron-right ml-auto text-xs"></i><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="px-3 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-2 pb-3">
            <span class="w-9 h-9 rounded-xl bg-white/10 text-brand-300 grid place-items-center font-display font-bold">
                <?= e(mb_strtoupper(mb_substr((string) $usuario['name'], 0, 1))) ?>
            </span>
            <span class="min-w-0">
                <span class="block text-xs font-semibold text-white truncate"><?= e($usuario['name']) ?></span>
                <span class="block text-[10px] text-slate-500 truncate"><?= e($usuario['email']) ?></span>
            </span>
        </div>
        <button id="btn-logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 hover:text-white text-sm transition">
            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Cerrar sesión
        </button>
        <a href="/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 hover:text-white text-sm transition mt-1">
            <i class="fa-solid fa-arrow-left w-5 text-center"></i> Ver catálogo
        </a>
    </div>
</aside>

<!-- Barra superior móvil -->
<div class="sticky top-0 z-40 md:hidden bg-ink-950 text-white px-4 py-3 flex items-center justify-between">
    <a href="/admin/dashboard.php" class="flex items-center gap-2 font-display font-bold">
        <span class="w-8 h-8 rounded-xl bg-brand-500 text-ink-950 grid place-items-center"><i class="fa-solid fa-basket-shopping"></i></span>
        Mi Almacén
    </a>
    <button id="btn-menu" class="text-xl w-9 h-9 rounded-xl bg-white/10 grid place-items-center" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu-movil">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>
<div id="menu-backdrop" class="hidden fixed inset-0 z-30 bg-black/50 md:hidden"></div>
<div id="menu-movil" class="hidden fixed inset-x-0 top-14 z-40 max-h-[calc(100vh-3.5rem)] overflow-y-auto bg-ink-950 text-slate-300 px-4 pb-4 pt-2 space-y-1 text-sm border-t border-white/10 shadow-lift md:hidden">
    <?php foreach ($enlaces as [$archivo, $etiqueta, $icono, $clave]): ?>
        <a href="/admin/<?= $archivo ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= adminNavActivo($clave, $seccion) ?>">
            <i class="fa-solid <?= e($icono) ?> w-5"></i><?= e($etiqueta) ?>
        </a>
    <?php endforeach; ?>
    <button id="btn-logout-movil" class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 hover:text-white">
        <i class="fa-solid fa-right-from-bracket w-5"></i>Cerrar sesión
    </button>
    <a href="/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 hover:text-white">
        <i class="fa-solid fa-arrow-left w-5"></i>Ver catálogo
    </a>
</div>

<div class="flex-1 md:ml-64 min-w-0">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 px-5 md:px-8 py-4 flex items-center justify-between gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-600">Panel de administración</p>
            <h1 class="font-display text-xl md:text-2xl font-extrabold text-ink-900 leading-tight"><?= e($pageTitle) ?></h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden sm:flex items-center gap-2 text-sm text-ink-600">
                <i class="fa-solid fa-user-circle text-brand-500"></i><?= e($usuario['name']) ?>
            </span>
            <a href="/index.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-ink-600 hover:border-brand-300 hover:text-brand-700 transition">
                <i class="fa-solid fa-arrow-up-right-from-square"></i><span class="hidden sm:inline">Ver sitio</span>
            </a>
        </div>
    </header>
    <main class="p-5 md:p-8">
