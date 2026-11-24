<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';

$pdo = db();
$estado = (string) ($_GET['estado'] ?? '');
$estadosValidos = ['pending', 'confirmed', 'cancelled', 'completed'];
if (!in_array($estado, $estadosValidos, true)) {
    $estado = '';
}

$sql = 'SELECT r.id, r.customer_name, r.customer_phone, r.pickup_date, r.status, r.created_at,
               (SELECT COUNT(*) FROM reservation_items ri WHERE ri.reservation_id = r.id) AS total_items,
               (SELECT COALESCE(SUM(ri.quantity * ri.price), 0) FROM reservation_items ri WHERE ri.reservation_id = r.id) AS total
        FROM reservations r';
$params = [];
if ($estado !== '') {
    $sql .= ' WHERE r.status = :estado';
    $params[':estado'] = $estado;
}
$sql .= ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

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

$pageTitle = 'Reservas';
$seccion = 'reservas';
$extraScripts = [
    'https://cdn.datatables.net/3.0.0/js/dataTables.min.js',
    'https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.min.js',
    '/admin/reservas.js',
];
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="flex flex-wrap gap-2 mb-6">
    <?php
    $filtros = ['' => 'Todas'] + array_combine($estadosValidos, ['Pendientes', 'Confirmadas', 'Canceladas', 'Completadas']);
    foreach ($filtros as $clave => $etiqueta):
        $activo = $estado === $clave;
    ?>
        <a href="/admin/reservas.php<?= $clave !== '' ? '?estado=' . $clave : '' ?>"
           class="rounded-full border px-4 py-2 text-sm font-semibold transition <?= $activo ? 'border-brand-600 bg-brand-600 text-white shadow-soft' : 'border-slate-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' ?>">
            <?= e($etiqueta) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="overflow-x-auto rounded-3xl border border-slate-100 bg-white p-4 shadow-soft">
    <table id="tabla-reservas" class="w-full text-sm">
        <thead class="bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
            <tr>
                <th class="text-left p-3">ID</th>
                <th class="text-left p-3">Cliente</th>
                <th class="text-left p-3">Retiro</th>
                <th class="text-left p-3">Estado</th>
                <th class="text-left p-3">Items</th>
                <th class="text-left p-3">Total</th>
                <th class="text-left p-3">Creada</th>
                <th class="text-left p-3">Ver</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $r): ?>
                <tr class="border-t border-slate-100 hover:bg-cream">
                    <td class="p-3 font-bold">#<?= (int) $r['id'] ?></td>
                    <td class="p-3">
                        <p class="font-semibold"><?= e($r['customer_name']) ?></p>
                        <p class="text-xs text-slate-400"><i class="fa-solid fa-phone mr-1"></i><?= e($r['customer_phone']) ?></p>
                    </td>
                    <td class="p-3 text-slate-500"><?= e($r['pickup_date']) ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs <?= $estilosEstado[$r['status']] ?? 'bg-slate-100' ?>">
                            <?= e($etiquetasEstado[$r['status']] ?? $r['status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-slate-500"><?= (int) $r['total_items'] ?></td>
                    <td class="p-3 font-semibold">Bs <?= e(number_format((float) $r['total'], 2)) ?></td>
                    <td class="p-3 text-slate-400 text-xs"><?= e($r['created_at']) ?></td>
                    <td class="p-3">
                        <a href="/admin/reserva-detalle.php?id=<?= (int) $r['id'] ?>"
                           class="inline-flex min-w-[44px] min-h-[44px] items-center justify-center rounded-lg text-brand-700 hover:bg-brand-50"
                           title="Ver reserva" aria-label="Ver reserva"><i class="fa-solid fa-eye"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
