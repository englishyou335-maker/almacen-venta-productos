<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

verificarLogin();

require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Base de datos';
$seccion = 'base-datos';

$pdo = db();
$porPagina = 50;

/** Nombres de tabla de la BD actual. Nunca se aceptan desde $_GET. */
function bdvTablas(PDO $pdo): array
{
    return $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
}

/** Columnas de una tabla ya validada contra SHOW TABLES. */
function bdvColumnas(PDO $pdo, string $tabla): array
{
    return $pdo->query('SHOW COLUMNS FROM `' . $tabla . '`')->fetchAll(PDO::FETCH_COLUMN);
}

/** Total de filas de una tabla validada. */
function bdvTotal(PDO $pdo, string $tabla): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM `' . $tabla . '`')->fetchColumn();
}

/** Una página de filas. Tabla y columna vienen de la lista del servidor. */
function bdvFilas(PDO $pdo, string $tabla, string $orden, int $limite, int $offset): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM `' . $tabla . '` ORDER BY `' . $orden . '` LIMIT :limite OFFSET :offset'
    );
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Enlace que conserva la paginación de las demás tablas. */
function bdvUrl(int $pagina, string $parametro, array $actuales): string
{
    $params = $actuales;
    $params[$parametro] = $pagina;
    return '/admin/base-datos.php?' . http_build_query($params);
}

$tablas = array_values(array_filter(
    bdvTablas($pdo),
    static fn($nombre): bool => is_string($nombre)
        && preg_match('/^[A-Za-z0-9_]+$/', $nombre) === 1
));

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <p class="text-sm text-ink-500">Volcado de solo lectura de todas las tablas. Datos al momento de cargar la página.</p>
        <p class="text-xs text-ink-400 mt-1"><?= count($tablas) ?> tablas · <?= $porPagina ?> filas por página</p>
    </div>
    <a href="/admin/dashboard.php"
       class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-ink-600 hover:border-brand-300 hover:text-brand-700 transition">
        <i class="fa-solid fa-arrow-left"></i> Volver al dashboard
    </a>
</div>

<?php foreach ($tablas as $tabla): ?>
    <?php
    $columnas = bdvColumnas($pdo, $tabla);
    $total = bdvTotal($pdo, $tabla);
    $paginas = max(1, (int) ceil($total / $porPagina));
    $parametro = 'p_' . $tabla;
    $pagina = isset($_GET[$parametro]) && is_scalar($_GET[$parametro])
        && ctype_digit((string) $_GET[$parametro])
        ? (int) $_GET[$parametro]
        : 1;
    $pagina = max(1, min($paginas, $pagina));
    $offset = ($pagina - 1) * $porPagina;
    $filas = $columnas !== [] ? bdvFilas($pdo, $tabla, $columnas[0], $porPagina, $offset) : [];
    ?>
    <section class="mb-8 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-soft">
        <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 grid place-items-center">
                <i class="fa-solid fa-table"></i>
            </span>
            <h2 class="font-display font-bold text-ink-900"><?= e($tabla) ?></h2>
            <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-ink-500">
                <?= $total ?> filas
            </span>
        </div>

        <div class="max-h-[32rem] overflow-auto">
            <table class="w-full text-xs font-mono">
                <thead class="sticky top-0 z-10 bg-ink-50 text-[11px] uppercase tracking-wider text-ink-500">
                    <tr>
                        <?php foreach ($columnas as $columna): ?>
                            <th class="whitespace-nowrap text-left px-4 py-3"><?= e($columna) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($filas === []): ?>
                        <tr>
                            <td colspan="<?= max(1, count($columnas)) ?>" class="px-4 py-8 text-center text-ink-400">Sin filas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filas as $fila): ?>
                            <tr class="border-t border-slate-100 hover:bg-cream">
                                <?php foreach ($columnas as $columna): ?>
                                    <?php if (strtolower($columna) === 'password'): ?>
                                        <td class="px-4 py-2 text-ink-400">***</td>
                                    <?php elseif ($fila[$columna] === null): ?>
                                        <td class="px-4 py-2 text-ink-400">NULL</td>
                                    <?php else: ?>
                                        <td class="px-4 py-2 text-ink-800 break-all"><?= e((string) $fila[$columna]) ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 text-xs">
            <?php if ($pagina > 1): ?>
                <a href="<?= e(bdvUrl($pagina - 1, $parametro, $_GET)) ?>"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-bold text-ink-600 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                </a>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-bold text-slate-300">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                </span>
            <?php endif; ?>

            <span class="font-semibold text-ink-500">Página <?= $pagina ?> de <?= $paginas ?></span>

            <?php if ($pagina < $paginas): ?>
                <a href="<?= e(bdvUrl($pagina + 1, $parametro, $_GET)) ?>"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-bold text-ink-600 hover:bg-slate-100 transition">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-bold text-slate-300">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
