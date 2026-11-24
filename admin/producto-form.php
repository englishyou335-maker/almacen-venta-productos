<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cloudinary.php';

verificarLogin();
$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$esEdicion = $id > 0;
$usuario = usuarioActual();

$producto = [
    'id' => 0, 'name' => '', 'description' => '', 'price' => '0.00', 'stock' => 0,
    'stock_min' => 5, 'category_id' => '', 'is_active' => 1, 'image_url' => '', 'image_public_id' => ''
];
if ($id > 0) {
    $stmt = $pdo->prepare(
        'SELECT p.*, i.image_url, i.image_public_id
         FROM products p
         LEFT JOIN product_images i ON i.product_id = p.id AND i.is_primary = 1
         WHERE p.id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $encontrado = $stmt->fetch();
    if ($encontrado === false) {
        flashSet('Producto no encontrado.', 'error');
        header('Location: /admin/productos.php');
        exit;
    }
    $producto = $encontrado;
}

$categorias = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC')->fetchAll();
$errores = [];

if (metodo() === 'POST') {
    if (!csrfValidar($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }

    $nombre = trim((string) ($_POST['name'] ?? ''));
    $descripcion = trim((string) ($_POST['description'] ?? ''));
    $precio = trim((string) ($_POST['price'] ?? ''));
    $stockMin = (int) ($_POST['stock_min'] ?? 5);
    $categoriaId = (int) ($_POST['category_id'] ?? 0);
    $activo = isset($_POST['is_active']) ? 1 : 0;
    $stockInicial = (int) ($_POST['stock'] ?? 0);

    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 255) {
        $errores[] = 'El nombre debe tener entre 3 y 255 caracteres.';
    }
    if (mb_strlen($descripcion) > 5000) {
        $errores[] = 'La descripción es demasiado larga (máximo 5000 caracteres).';
    }
    if (!is_numeric($precio) || (float) $precio < 0 || (float) $precio > 99999999) {
        $errores[] = 'El precio debe ser un número válido.';
    }
    if ($stockMin < 0 || $stockMin > 100000) {
        $errores[] = 'El stock mínimo no es válido.';
    }
    if ($id === 0 && ($stockInicial < 0 || $stockInicial > 100000)) {
        $errores[] = 'El stock inicial no es válido.';
    }
    if ($categoriaId > 0) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id AND is_active = 1');
        $chk->execute([':id' => $categoriaId]);
        if ((int) $chk->fetchColumn() === 0) {
            $errores[] = 'La categoría seleccionada no existe.';
        }
    }

    $nuevaImagen = null;
    $subidaError = '';
    if (isset($_FILES['imagen']) && (int) $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $nuevaImagen = cloudinarySubir($_FILES['imagen']);
        if (!$nuevaImagen['ok']) {
            $subidaError = $nuevaImagen['error'];
        }
    }

    if (!$errores) {
        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                // El stock NO se edita aquí: se ajusta en productos.php para
                // garantizar que todo cambio quede en stock_movements.
                $stmt = $pdo->prepare(
                    'UPDATE products SET name = :name, description = :desc, price = :price,
                            stock_min = :minimo, category_id = :cat, is_active = :activo
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':name'   => $nombre,
                    ':desc'   => $descripcion,
                    ':price'  => (float) $precio,
                    ':minimo' => $stockMin,
                    ':cat'    => $categoriaId > 0 ? $categoriaId : null,
                    ':activo' => $activo,
                    ':id'     => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO products (name, description, price, stock, stock_min, category_id, is_active)
                     VALUES (:name, :desc, :price, :stock, :minimo, :cat, :activo)'
                );
                $stmt->execute([
                    ':name'   => $nombre,
                    ':desc'   => $descripcion,
                    ':price'  => (float) $precio,
                    ':stock'  => $stockInicial,
                    ':minimo' => $stockMin,
                    ':cat'    => $categoriaId > 0 ? $categoriaId : null,
                    ':activo' => $activo,
                ]);
                $id = (int) $pdo->lastInsertId();

                if ($stockInicial > 0) {
                    $mov = $pdo->prepare(
                        "INSERT INTO stock_movements (product_id, type, quantity, reason, reference_type, reference_id, user_id)
                         VALUES (:producto, 'adjustment', :cantidad, 'Stock inicial', 'manual', NULL, :usuario)"
                    );
                    $mov->execute([
                        ':producto' => $id,
                        ':cantidad' => $stockInicial,
                        ':usuario'  => $usuario['id'],
                    ]);
                }
            }

            if ($nuevaImagen !== null && $nuevaImagen['ok']) {
                $pdo->exec('DELETE FROM product_images WHERE product_id = ' . $id);
                $img = $pdo->prepare(
                    'INSERT INTO product_images (product_id, image_url, image_public_id, is_primary, sort_order)
                     VALUES (:id, :url, :public, 1, 0)'
                );
                $img->execute([
                    ':id'     => $id,
                    ':url'    => $nuevaImagen['url'],
                    ':public' => $nuevaImagen['public_id'],
                ]);
            }

            $pdo->commit();

            // Se borra la imagen anterior solo después del commit.
            if ($nuevaImagen !== null && $nuevaImagen['ok'] && !empty($producto['image_public_id'])) {
                cloudinaryEliminar((string) $producto['image_public_id']);
            }

            $mensaje = $esEdicion ? 'Producto actualizado.' : 'Producto creado.';
            if ($subidaError !== '') {
                $mensaje .= ' Aviso: ' . $subidaError;
            }
            flashSet($mensaje, $subidaError !== '' ? 'warning' : 'success');
            header('Location: /admin/productos.php');
            exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error al guardar producto: ' . $ex->getMessage());
            $errores[] = 'Error interno al guardar. Inténtalo de nuevo.';
        }
    } else {
        if ($subidaError !== '') {
            $errores[] = $subidaError;
        }
        // Conserva lo escrito para no perder el formulario.
        $producto = array_merge($producto, [
            'name' => $nombre, 'description' => $descripcion, 'price' => $precio,
            'stock' => $stockInicial, 'stock_min' => $stockMin,
            'category_id' => $categoriaId, 'is_active' => $activo,
        ]);
    }
}

$esNuevo = (int) $producto['id'] === 0;
$pageTitle = $esNuevo ? 'Nuevo producto' : 'Editar producto';
$seccion = 'productos';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($errores): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
        <ul class="list-disc list-inside">
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" action="/admin/producto-form.php<?= $esNuevo ? '' : '?id=' . (int) $producto['id'] ?>"
      class="max-w-3xl rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">
    <?= csrfField() ?>

    <div class="grid gap-5 md:grid-cols-2">
        <label class="block md:col-span-2">
            <span class="text-sm font-semibold">Nombre *</span>
            <input name="name" required minlength="3" maxlength="255"
                   value="<?= e($producto['name']) ?>"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
        </label>

        <label class="block md:col-span-2">
            <span class="text-sm font-semibold">Descripción</span>
            <textarea name="description" rows="4" maxlength="5000"
                      class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"><?= e($producto['description']) ?></textarea>
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Precio (Bs) *</span>
            <input name="price" type="number" step="0.01" min="0" required
                   value="<?= e($producto['price']) ?>"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Categoría</span>
            <select name="category_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
                <option value="0">Sin categoría</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $producto['category_id'] === (int) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <?php if ($esNuevo): ?>
            <label class="block">
                <span class="text-sm font-semibold">Stock inicial</span>
                <input name="stock" type="number" min="0" value="0"
                       class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
                <span class="text-xs text-slate-400">Se registrará como movimiento "Stock inicial".</span>
            </label>
        <?php else: ?>
            <div class="block">
                <span class="text-sm font-semibold">Stock actual</span>
                <p class="mt-1 border border-slate-200 bg-slate-50 rounded-lg px-3 py-2 font-bold">
                    <?= (int) $producto['stock'] ?> unidades
                </p>
                <a href="/admin/productos.php" class="text-xs text-blue-600 hover:underline">
                    Ajustar stock desde el listado →
                </a>
            </div>
        <?php endif; ?>

        <label class="block">
            <span class="text-sm font-semibold">Stock mínimo (alerta)</span>
            <input name="stock_min" type="number" min="0" required value="<?= (int) $producto['stock_min'] ?>"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm outline-none focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
        </label>

        <label class="block md:col-span-2">
            <span class="text-sm font-semibold">Imagen (JPG, PNG, WEBP o GIF — máx. 5 MB)</span>
            <input name="imagen" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                   class="mt-1 w-full rounded-xl border border-slate-200 bg-cream px-4 py-2.5 text-sm file:mr-3 file:font-semibold">
            <?php if (!empty($producto['image_url'])): ?>
                <img src="<?= e($producto['image_url']) ?>" alt="Imagen actual" class="mt-3 w-24 h-24 rounded object-cover">
            <?php endif; ?>
        </label>

        <label class="flex items-center gap-2 md:col-span-2">
            <input name="is_active" type="checkbox" value="1" <?= (bool) $producto['is_active'] ? 'checked' : '' ?>
                   class="w-4 h-4 text-blue-600 rounded">
            <span class="text-sm font-semibold">Producto activo (visible en el catálogo)</span>
        </label>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
            <i class="fa-solid fa-floppy-disk"></i> Guardar
        </button>
        <a href="/admin/productos.php" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-semibold text-ink-600 hover:bg-slate-200">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
