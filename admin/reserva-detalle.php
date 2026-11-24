<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$reserva = $stmt->fetch();

if ($reserva === false) {
    $pageTitle = 'Reserva no encontrada';
    require_once __DIR__ . '/../includes/admin-header.php';
    ?>
    <div class="rounded-3xl border border-slate-100 bg-white p-10 text-center shadow-soft">
        <h2 class="text-xl font-bold">Reserva no encontrada</h2>
        <a href="/admin/reservas.php" class="inline-block mt-4 text-blue-600 hover:underline">Volver al listado</a>
    </div>
    <?php
    require_once __DIR__ . '/../includes/admin-footer.php';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT ri.quantity, ri.price, p.id AS product_id, p.name AS product_name
     FROM reservation_items ri
     JOIN products p ON ri.product_id = p.id
     WHERE ri.reservation_id = :id
     ORDER BY ri.id ASC'
);
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += (int) $item['quantity'] * (float) $item['price'];
}

// Debe coincidir con api/actualizar-reserva.php.
$transiciones = [
    'pending'   => ['confirmed', 'cancelled'],
    'confirmed' => ['cancelled', 'completed'],
    'cancelled' => [],
    'completed' => [],
];
$posibles = $transiciones[$reserva['status']] ?? [];

$estilosEstado = [
    'pending'   => 'bg-amber-100 text-amber-700',
    'confirmed' => 'bg-blue-100 text-blue-700',
    'cancelled' => 'bg-red-100 text-red-700',
    'completed' => 'bg-emerald-100 text-emerald-700',
];
$etiquetasEstado = [
    'pending'   => 'Pendiente',
    'confirmed' => 'Confirmada',
    'cancelled' => 'Cancelada',
    'completed' => 'Completada',
];
$etiquetasAccion = [
    'confirmed' => 'Confirmar',
    'cancelled' => 'Cancelar',
    'completed' => 'Completar',
];
$coloresAccion = [
    'confirmed' => 'bg-blue-600 hover:bg-blue-700',
    'cancelled' => 'bg-red-600 hover:bg-red-700',
    'completed' => 'bg-emerald-600 hover:bg-emerald-700',
];

$pageTitle = 'Reserva #' . $id;
$seccion = 'reservas';
$extraScripts = ['/admin/reserva-detalle.js'];
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-6">
    <a href="/admin/reservas.php" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:underline">
        <i class="fa-solid fa-arrow-left"></i> Volver a reservas
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <!-- Datos del cliente -->
        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-lg">Datos del cliente</h2>
                <span class="px-3 py-1 rounded text-sm <?= $estilosEstado[$reserva['status']] ?? 'bg-slate-100' ?>">
                    <?= e($etiquetasEstado[$reserva['status']] ?? $reserva['status']) ?>
                </span>
            </div>
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-400">Nombre</dt><dd class="font-semibold"><?= e($reserva['customer_name']) ?></dd></div>
                <div><dt class="text-slate-400">Teléfono</dt><dd><?= e($reserva['customer_phone']) ?></dd></div>
                <div><dt class="text-slate-400">Fecha de retiro</dt><dd class="font-semibold"><?= e($reserva['pickup_date']) ?></dd></div>
                <div><dt class="text-slate-400">Creada</dt><dd><?= e($reserva['created_at']) ?></dd></div>
                <div><dt class="text-slate-400">Notas</dt><dd><?= nl2br(e($reserva['notes'])) ?></dd></div>
            </dl>
        </section>

        <!-- Productos reservados -->
        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-soft">
            <h2 class="font-bold text-lg mb-4">Productos reservados</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr><th class="text-left p-2">Producto</th><th class="text-left p-2">Cantidad</th><th class="text-left p-2">Precio unit.</th><th class="text-left p-2">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr class="border-t border-slate-100">
                                <td class="p-2">
                                    <a href="/admin/producto-form.php?id=<?= (int) $item['product_id'] ?>" class="hover:text-blue-600">
                                        <?= e($item['product_name']) ?>
                                    </a>
                                </td>
                                <td class="p-2"><?= (int) $item['quantity'] ?></td>
                                <td class="p-2">Bs <?= e(number_format((float) $item['price'], 2)) ?></td>
                                <td class="p-2 font-semibold">Bs <?= e(number_format((int) $item['quantity'] * (float) $item['price'], 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200">
                            <td colspan="3" class="p-2 text-right font-bold">Total</td>
                            <td class="p-2 font-bold text-emerald-600">Bs <?= e(number_format($total, 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>

    <!-- Cambio de estado -->
    <aside class="order-first rounded-3xl border border-slate-100 bg-white p-6 shadow-soft h-fit lg:order-none">
        <h2 class="font-bold text-lg mb-2">Cambiar estado</h2>
        <p class="text-xs text-slate-500 mb-5">
            El stock se apartó al crear la reserva. Solo cancelar lo devuelve.
        </p>
        <?php if ($posibles === []): ?>
            <p class="text-sm text-slate-500">Esta reserva está finalizada; no hay transiciones disponibles.</p>
        <?php else: ?>
            <div class="space-y-2" id="acciones-estado">
                <?php foreach ($posibles as $nuevo): ?>
                    <button type="button" class="w-full text-white font-semibold py-2 rounded-lg <?= $coloresAccion[$nuevo] ?>"
                            data-estado="<?= $nuevo ?>"
                            data-id="<?= (int) $reserva['id'] ?>"
                            data-pregunta="<?= $nuevo === 'cancelled' ? '¿Cancelar la reserva? El stock volverá a estar disponible.' : '¿Confirmar este cambio de estado?' ?>">
                        <?= e($etiquetasAccion[$nuevo]) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
