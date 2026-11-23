<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cloudinary.php';

verificarLogin();

if (metodo() === 'POST') {
    if (!csrfValidar($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }

    $accion = (string) ($_POST['accion'] ?? '');
    $qrActual = qrPago();

    if ($accion === 'eliminar') {
        settingEliminar('qr_pago_url');
        settingEliminar('qr_pago_public_id');
        if ($qrActual['public_id'] !== '') {
            cloudinaryEliminar($qrActual['public_id']);
        }
        flashSet('El QR de pago se eliminó. Ya no se mostrará en el carrito.');
    } elseif ($accion === 'subir') {
        $subida = cloudinarySubir($_FILES['imagen'] ?? [], 'qr');
        if (!$subida['ok']) {
            flashSet((string) $subida['error'], 'error');
        } else {
            try {
                settingGuardar('qr_pago_url', (string) $subida['url']);
                settingGuardar('qr_pago_public_id', (string) $subida['public_id']);
                if ($qrActual['public_id'] !== '' && $qrActual['public_id'] !== $subida['public_id']) {
                    cloudinaryEliminar($qrActual['public_id']);
                }
                flashSet('QR de pago actualizado. Ya se muestra en el carrito.');
            } catch (Throwable $e) {
                cloudinaryEliminar((string) $subida['public_id']);
                error_log('Error al guardar el QR de pago: ' . $e->getMessage());
                flashSet('No se pudo guardar el QR. ¿Se ejecutó database/migrate-qr-pago.sql?', 'error');
            }
        }
    }

    header('Location: /admin/qr-pago.php');
    exit;
}

$qr = qrPago();
$diagnostico = cloudinaryDiagnostico();
$origenes = [
    'base-de-datos'       => 'Base de datos',
    'variable-de-entorno' => 'Variable del servidor',
    'variables-separadas' => 'Variables separadas',
    'sin-configurar'      => 'Sin configurar',
];
$origenTexto = $origenes[$diagnostico['origen']] ?? $diagnostico['origen'];
$pageTitle = 'QR de pago';
$seccion = 'qr-pago';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<section class="mb-6 rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="w-11 h-11 shrink-0 rounded-2xl grid place-items-center text-xl <?= $diagnostico['configurado'] ? 'bg-brand-100 text-brand-700' : 'bg-amber-50 text-amber-600' ?>">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </span>
            <div>
                <h2 class="font-display text-xl font-bold text-ink-900">Conexión Cloudinary</h2>
                <p class="mt-1 text-sm text-ink-500">
                    <?= $diagnostico['configurado']
                        ? 'Credenciales listas para subir imágenes.'
                        : 'Faltan credenciales en la base de datos o en el servidor.' ?>
                </p>
            </div>
        </div>
        <span class="self-start rounded-full px-3 py-1 text-[11px] font-bold <?= $diagnostico['configurado'] ? 'bg-brand-50 text-brand-700' : 'bg-amber-50 text-amber-700' ?>">
            <i class="fa-solid <?= $diagnostico['configurado'] ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> mr-1"></i>
            <?= $diagnostico['configurado'] ? 'Activa' : 'Inactiva' ?>
        </span>
    </div>

    <dl class="mt-6 grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl bg-cream p-4">
            <dt class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Origen</dt>
            <dd class="mt-1 text-sm font-semibold text-ink-800"><?= e($origenTexto) ?></dd>
        </div>
        <div class="rounded-2xl bg-cream p-4">
            <dt class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Cloud</dt>
            <dd class="mt-1 text-sm font-semibold text-ink-800"><?= e($diagnostico['cloud'] !== '' ? $diagnostico['cloud'] : '—') ?></dd>
        </div>
        <div class="rounded-2xl bg-cream p-4">
            <dt class="text-[10px] font-bold uppercase tracking-widest text-ink-400">API key</dt>
            <dd class="mt-1 text-sm font-semibold text-ink-800"><?= e($diagnostico['api_key']) ?></dd>
        </div>
    </dl>

    <p class="mt-5 text-xs text-ink-500">
        <i class="fa-solid fa-lock mr-1 text-brand-500"></i>
        La cadena de conexión se administra en la base de datos; aquí solo se muestra su estado.
    </p>
</section>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="h-fit rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">
        <span class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 grid place-items-center text-xl">
            <i class="fa-solid fa-qrcode"></i>
        </span>
        <h2 class="mt-5 font-display text-xl font-bold text-ink-900">Subir QR de pago</h2>
        <p class="mt-2 text-sm leading-relaxed text-ink-500">
            La imagen se mostrará en el carrito para escanear, descargar o compartir.
            Al reemplazarla aquí, el cambio se refleja enseguida en el sitio público.
        </p>

        <form method="post" enctype="multipart/form-data" action="/admin/qr-pago.php" class="mt-7 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="accion" value="subir">
            <label class="block">
                <span class="text-sm font-semibold text-ink-800">Imagen del QR</span>
                <input name="imagen" type="file" required accept="image/jpeg,image/png,image/webp,image/gif"
                       class="mt-2 block w-full text-sm text-ink-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:font-bold file:text-brand-700 hover:file:bg-brand-100">
                <span class="mt-2 block text-xs text-ink-500">JPG, PNG, WEBP o GIF · máximo 5 MB</span>
            </label>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3 font-bold text-white transition hover:bg-brand-700">
                <i class="fa-solid fa-cloud-arrow-up"></i> Subir / reemplazar QR
            </button>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-display text-xl font-bold text-ink-900">QR actual</h2>
            <?php if ($qr['url'] !== ''): ?>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-[11px] font-bold text-brand-700">
                    <i class="fa-solid fa-circle-check mr-1"></i> Visible en el carrito
                </span>
            <?php endif; ?>
        </div>

        <?php if ($qr['url'] !== ''): ?>
            <div class="mt-6 grid place-items-center rounded-3xl border border-dashed border-slate-200 bg-cream p-6">
                <img src="<?= e($qr['url']) ?>" alt="QR de pago actual"
                     class="max-h-80 w-full object-contain">
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <a href="<?= e($qr['url']) ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver imagen completa
                </a>
                <form method="post" action="/admin/qr-pago.php" onsubmit="return confirm('¿Eliminar el QR de pago? Dejará de mostrarse en el carrito.');">
                    <?= csrfField() ?>
                    <input type="hidden" name="accion" value="eliminar">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50">
                        <i class="fa-solid fa-trash"></i> Eliminar QR
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="mt-6 grid place-items-center rounded-3xl border border-dashed border-slate-200 bg-cream px-6 py-16 text-center">
                <span class="w-16 h-16 rounded-2xl bg-white text-slate-300 grid place-items-center text-3xl shadow-soft">
                    <i class="fa-solid fa-qrcode"></i>
                </span>
                <p class="mt-5 font-display text-lg font-bold text-ink-800">Todavía no hay QR configurado</p>
                <p class="mt-2 max-w-sm text-sm text-ink-500">Sube una imagen desde el formulario para mostrarla en el carrito de reservas.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
