<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();

// Alternar activo (formulario clásico + PRG).
if (metodo() === 'POST') {
    require_once __DIR__ . '/../includes/auth.php';
    verificarLogin();
    if (!csrfValidar($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $accion = (string) ($_POST['accion'] ?? '');
    if ($id > 0 && $accion === 'toggle') {
        $stmt = $pdo->prepare('UPDATE products SET is_active = NOT is_active WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flashSet('Estado del producto actualizado.');
    }
    header('Location: /admin/productos.php');
    exit;
}

$productos = $pdo->query(
    'SELECT p.id, p.name, p.price, p.stock, p.stock_min, p.is_active, p.category_id,
            c.name AS categoria, i.image_url
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN product_images i ON i.product_id = p.id AND i.is_primary = 1
     ORDER BY p.id DESC'
)->fetchAll();

$pageTitle = 'Productos';
$seccion = 'productos';
$extraScripts = [
    'https://cdn.datatables.net/3.0.0/js/dataTables.min.js',
    'https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.min.js',
    '/admin/productos.js',
];
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm text-ink-500">Gestiona el catálogo, el stock y la visibilidad de los productos.</p>
        <p class="mt-1 text-xs text-ink-400">Los precios y las imágenes se completan aquí; el stock se ajusta con su propio botón.</p>
    </div>
    <a href="/admin/producto-form.php" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-soft transition hover:bg-brand-700">
        <i class="fa-solid fa-plus"></i> Nuevo producto
    </a>
</div>

<div class="overflow-x-auto rounded-3xl border border-slate-100 bg-white p-4 shadow-soft">
    <table id="tabla-productos" class="w-full text-sm">
        <thead class="bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
            <tr>
                <th class="text-left p-3">Imagen</th>
                <th class="text-left p-3">Producto</th>
                <th class="text-left p-3">Categoría</th>
                <th class="text-left p-3">Precio</th>
                <th class="text-left p-3">Stock</th>
                <th class="text-left p-3">Estado</th>
                <th class="text-left p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr class="border-t border-slate-100 hover:bg-cream">
                    <td class="p-3">
                        <img src="<?= e(!empty($p['image_url']) ? $p['image_url'] : '/assets/img/placeholder.svg') ?>"
                             alt="" class="w-12 h-12 rounded-xl object-cover bg-brand-50">
                    </td>
                    <td class="p-3 font-semibold"><?= e($p['name']) ?></td>
                    <td class="p-3 text-slate-500"><?= e($p['categoria'] ?? '—') ?></td>
                    <td class="p-3">Bs <?= e(number_format((float) $p['price'], 2)) ?></td>
                    <td class="p-3">
                        <span class="font-bold <?= (int) $p['stock'] <= (int) $p['stock_min'] ? 'text-red-600' : 'text-emerald-600' ?>">
                            <?= (int) $p['stock'] ?>
                        </span>
                        <span class="text-xs text-slate-400">/ mín <?= (int) $p['stock_min'] ?></span>
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs <?= (bool) $p['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>">
                            <?= (bool) $p['is_active'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="p-3">
                        <div class="flex flex-wrap gap-2 items-center">
                            <a href="/admin/producto-form.php?id=<?= (int) $p['id'] ?>"
                               class="inline-flex min-w-[44px] min-h-[44px] items-center justify-center rounded-lg text-brand-700 hover:bg-brand-50"
                               title="Editar" aria-label="Editar producto">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <button type="button" class="inline-flex min-w-[44px] min-h-[44px] items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 btn-stock"
                                    data-id="<?= (int) $p['id'] ?>" data-nombre="<?= e($p['name']) ?>"
                                    data-stock="<?= (int) $p['stock'] ?>" title="Ajustar stock" aria-label="Ajustar stock">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <form method="post" action="/admin/productos.php" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="accion" value="toggle">
                                <button type="submit" class="inline-flex min-w-[44px] min-h-[44px] items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                                        title="<?= (bool) $p['is_active'] ? 'Desactivar' : 'Activar' ?>"
                                        aria-label="<?= (bool) $p['is_active'] ? 'Desactivar producto' : 'Activar producto' ?>">
                                    <i class="fa-solid <?= (bool) $p['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal de ajuste manual de stock (usa la API existente) -->
<div id="modal-stock" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
    <form id="form-stock" class="w-full max-w-md max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white p-6 shadow-lift">
        <h2 class="text-xl font-bold mb-1">Ajustar stock</h2>
        <p id="stock-producto" class="text-sm text-slate-500 mb-5"></p>
        <input type="hidden" id="stock-id">
        <label class="block">
            <span class="text-sm font-semibold">Cantidad (+ entrada / − salida) *</span>
            <input id="stock-cantidad" type="number" required min="-10000" max="10000"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
        </label>
        <label class="block mt-4">
            <span class="text-sm font-semibold">Motivo *</span>
            <input id="stock-motivo" type="text" required minlength="3" maxlength="255"
                   placeholder="Ej. Venta física"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
        </label>
        <p class="text-xs text-slate-400 mt-2">Ejemplo: <code>-2</code> para una venta física, <code>+20</code> para una compra.</p>
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" id="stock-cancelar" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-ink-600 hover:bg-slate-200">Cancelar</button>
            <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Guardar</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
