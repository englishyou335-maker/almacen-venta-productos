<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

verificarLogin();
$pdo = db();

if (metodo() === 'POST') {
    if (!csrfValidar($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }

    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim((string) ($_POST['name'] ?? ''));
    $descripcion = trim((string) ($_POST['description'] ?? ''));
    $activo = isset($_POST['is_active']) ? 1 : 0;

    if ($accion === 'guardar') {
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 255) {
            flashSet('El nombre debe tener entre 2 y 255 caracteres.', 'error');
        } elseif (mb_strlen($descripcion) > 5000) {
            flashSet('La descripción es demasiado larga.', 'error');
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE categories SET name = :name, description = :desc, is_active = :activo WHERE id = :id'
                );
                $stmt->execute([':name' => $nombre, ':desc' => $descripcion, ':activo' => $activo, ':id' => $id]);
                flashSet('Categoría actualizada.');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO categories (name, description, is_active) VALUES (:name, :desc, :activo)'
                );
                $stmt->execute([':name' => $nombre, ':desc' => $descripcion, ':activo' => $activo]);
                flashSet('Categoría creada.');
            }
        }
    } elseif ($accion === 'eliminar' && $id > 0) {
        // ON DELETE SET NULL: los productos quedan sin categoría, no se borran.
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flashSet('Categoría eliminada. Los productos quedaron sin categoría.');
    }

    header('Location: /admin/categorias.php');
    exit;
}

$categorias = $pdo->query(
    'SELECT c.id, c.name, c.description, c.is_active,
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS total_productos
     FROM categories c ORDER BY c.name ASC'
)->fetchAll();

$editarId = (int) ($_GET['editar'] ?? 0);
$editar = null;
foreach ($categorias as $c) {
    if ((int) $c['id'] === $editarId) {
        $editar = $c;
        break;
    }
}

$pageTitle = 'Categorías';
$seccion = 'categorias';
$extraScripts = [
    'https://cdn.datatables.net/3.0.0/js/dataTables.min.js',
    'https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.min.js',
    '/admin/categorias.js',
];
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Formulario -->
    <form method="post" action="/admin/categorias.php" class="h-fit rounded-3xl border border-slate-100 bg-white p-6 shadow-soft">
        <h2 class="mb-5 font-display text-lg font-bold text-ink-900">
            <?= $editar !== null ? 'Editar categoría' : 'Nueva categoría' ?>
        </h2>
        <?= csrfField() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= $editar !== null ? (int) $editar['id'] : 0 ?>">

        <label class="block">
            <span class="text-sm font-semibold">Nombre *</span>
            <input name="name" required minlength="2" maxlength="255"
                   value="<?= e($editar['name'] ?? '') ?>"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
        </label>
        <label class="block mt-4">
            <span class="text-sm font-semibold">Descripción</span>
            <textarea name="description" rows="3" maxlength="5000"
                      class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"><?= e($editar['description'] ?? '') ?></textarea>
        </label>
        <label class="flex items-center gap-2 mt-4">
            <input name="is_active" type="checkbox" value="1"
                   <?= $editar === null || (bool) $editar['is_active'] ? 'checked' : '' ?>
                   class="w-4 h-4 text-blue-600 rounded">
            <span class="text-sm font-semibold">Categoría activa</span>
        </label>

        <div class="flex gap-2 mt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
            <?php if ($editar !== null): ?>
                <a href="/admin/categorias.php" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-ink-600 hover:bg-slate-200">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Listado -->
    <div class="overflow-x-auto rounded-3xl border border-slate-100 bg-white p-4 shadow-soft lg:col-span-2">
        <table id="tabla-categorias" class="w-full text-sm">
            <thead class="bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
                <tr><th class="text-left p-3">Nombre</th><th class="text-left p-3">Productos</th><th class="text-left p-3">Estado</th><th class="text-left p-3">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr class="border-t border-slate-100 hover:bg-cream">
                        <td class="p-3">
                            <p class="font-semibold"><?= e($c['name']) ?></p>
                            <p class="text-xs text-slate-400"><?= e(mb_substr((string) $c['description'], 0, 70)) ?></p>
                        </td>
                        <td class="p-3 text-slate-500"><?= (int) $c['total_productos'] ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs <?= (bool) $c['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>">
                                <?= (bool) $c['is_active'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-3 items-center">
                                <a href="/admin/categorias.php?editar=<?= (int) $c['id'] ?>"
                                   class="inline-flex min-w-[44px] min-h-[44px] items-center justify-center rounded-lg text-brand-700 hover:bg-brand-50"
                                   title="Editar" aria-label="Editar categoría">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="post" action="/admin/categorias.php">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button type="submit" class="inline-flex min-w-[44px] min-h-[44px] items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700"
                                            data-confirmar="¿Eliminar la categoría &quot;<?= e($c['name']) ?>&quot;? Los productos quedarán sin categoría."
                                            title="Eliminar" aria-label="Eliminar categoría">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
