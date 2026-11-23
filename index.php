<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/catalogo.php';

$categorias = categoriasActivas();
$destacados = productosDestacados(8);
$totalProductos = (int) db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$pageTitle = 'Inicio';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero-fondo relative overflow-hidden text-white">
    <div class="absolute inset-0 hero-malla pointer-events-none"></div>
    <div class="absolute -left-24 top-24 w-72 h-72 rounded-full bg-brand-500/10 blur-3xl pointer-events-none"></div>
    <div class="absolute right-[8%] bottom-[-8rem] w-96 h-96 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="grid items-center gap-14 lg:grid-cols-[1.1fr_0.9fr]">
            <div data-aos="fade-up">
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-300/30 bg-brand-400/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-brand-200">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
                    Catálogo con reserva previa
                </span>
                <h1 class="mt-6 font-display text-4xl sm:text-5xl xl:text-6xl font-extrabold leading-[1.08] tracking-tight">
                    <span class="block text-white">Mi Almacén</span>
                    <span class="block font-script text-5xl sm:text-6xl xl:text-7xl font-normal leading-[1.2] bg-gradient-to-r from-brand-200 via-white to-brand-300 bg-clip-text text-transparent">Laura</span>
                    <span class="block text-brand-300">- Murillo</span>
                </h1>
                <p class="mt-6 max-w-xl text-base sm:text-lg leading-relaxed text-slate-300">
                    Explora productos, consulta la disponibilidad y arma tu pedido.
                    Reservas en minutos y pagas al recibir en tienda, sin pagos online.
                </p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="/productos.php" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-6 py-3.5 font-bold text-ink-950 shadow-lift transition hover:bg-brand-400 hover:-translate-y-0.5">
                        <i class="fa-solid fa-table-cells"></i> Ver productos
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                    <a href="<?= e(WHATSAPP_ADMIN_URL) ?>" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-xl border border-brand-300/30 bg-brand-400/10 px-6 py-3.5 font-semibold text-brand-100 transition hover:bg-brand-400/20">
                        <i class="fa-brands fa-whatsapp"></i> Escribir por WhatsApp
                    </a>
                </div>
                <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-xs text-slate-300">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-cart-shopping text-brand-400"></i> Carrito de reservas</span>
                    <span class="flex items-center gap-2"><i class="fa-solid fa-store text-brand-400"></i> Retiro en tienda</span>
                    <span class="flex items-center gap-2"><i class="fa-solid fa-wallet text-brand-400"></i> Sin pago online</span>
                </div>
            </div>

            <!-- Tarjeta decorativa: resume el flujo sin inventar datos de productos. -->
            <div class="relative mx-auto w-full max-w-md" data-aos="fade-up" data-aos-delay="120">
                <div class="absolute inset-6 rounded-4xl bg-brand-400/20 blur-2xl"></div>
                <div class="cristal relative rounded-4xl border border-white/15 p-6 shadow-lift sm:p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-brand-200">Reserva en 3 pasos</p>
                            <h2 class="mt-1 font-display text-2xl font-bold">Así de fácil</h2>
                        </div>
                        <span class="w-12 h-12 rounded-2xl bg-brand-400/20 text-brand-200 grid place-items-center text-xl">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </span>
                    </div>
                    <ol class="mt-7 space-y-4">
                        <li class="flex items-center gap-4 rounded-2xl bg-white/5 border border-white/10 p-3.5">
                            <span class="w-9 h-9 shrink-0 rounded-xl bg-brand-400 text-ink-950 grid place-items-center font-display font-extrabold">1</span>
                            <span class="text-sm"><strong class="block text-white">Elige productos</strong><span class="text-slate-300">Agrégalos desde el catálogo</span></span>
                        </li>
                        <li class="flex items-center gap-4 rounded-2xl bg-white/5 border border-white/10 p-3.5">
                            <span class="w-9 h-9 shrink-0 rounded-xl bg-brand-400 text-ink-950 grid place-items-center font-display font-extrabold">2</span>
                            <span class="text-sm"><strong class="block text-white">Elige la fecha</strong><span class="text-slate-300">Indica cuándo quieres retirar</span></span>
                        </li>
                        <li class="flex items-center gap-4 rounded-2xl bg-white/5 border border-white/10 p-3.5">
                            <span class="w-9 h-9 shrink-0 rounded-xl bg-brand-400 text-ink-950 grid place-items-center font-display font-extrabold">3</span>
                            <span class="text-sm"><strong class="block text-white">Confirma y retira</strong><span class="text-slate-300">Te confirmamos la reserva</span></span>
                        </li>
                    </ol>
                    <div class="mt-6 flex items-center gap-2 border-t border-white/10 pt-5 text-xs text-brand-200">
                        <i class="fa-solid fa-shield-halved"></i> El stock se aparta al crear la reserva
                    </div>
                </div>
            </div>
        </div>

        <!-- Cifras reales del catálogo. -->
        <div class="mt-16 grid grid-cols-2 gap-3 lg:grid-cols-3" data-aos="fade-up">
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                <p class="font-display text-3xl font-extrabold text-white"><?= $totalProductos ?></p>
                <p class="text-xs text-slate-300 mt-1">Productos en catálogo</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                <p class="font-display text-3xl font-extrabold text-white"><?= count($categorias) ?></p>
                <p class="text-xs text-slate-300 mt-1">Categorías</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                <p class="font-display text-3xl font-extrabold text-brand-300">100%</p>
                <p class="text-xs text-slate-300 mt-1">Reserva previa</p>
            </div>
        </div>
    </div>
</section>

<!-- Categorías -->
<section class="max-w-7xl mx-auto px-4 py-16 md:py-20">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4" data-aos="fade-up">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Explora por categoría</p>
            <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold tracking-tight text-ink-900">Todo lo que necesitas, organizado</h2>
        </div>
        <a href="/productos.php" class="text-sm font-bold text-brand-700 hover:underline inline-flex items-center gap-2">
            Ver catálogo completo <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <?php if (!$categorias): ?>
        <p class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-ink-500">Todavía no hay categorías activas.</p>
    <?php endif; ?>

    <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($categorias as $c): ?>
            <?php
            $categoriaId = (int) $c['id'];
            $nombreCategoria = (string) $c['name'];
            [$iconoTono, $degradado] = categoriaTono($categoriaId, $nombreCategoria);
            $iconoCategoria = categoriaIcono($categoriaId, $nombreCategoria);
            ?>
            <a href="/productos.php?categoria=<?= $categoriaId ?>"
               class="categoria-card group relative overflow-hidden rounded-3xl border border-slate-100 bg-white p-5 shadow-soft hover:border-brand-200 hover:shadow-lift"
               data-aos="fade-up">
                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br <?= e($degradado) ?> opacity-80"></div>
                <div class="relative">
                    <span class="w-14 h-14 rounded-2xl bg-white shadow-sm grid place-items-center ring-1 ring-slate-100">
                        <i class="fa-solid <?= e($iconoCategoria) ?> text-2xl <?= e($iconoTono) ?>"></i>
                    </span>
                    <h3 class="mt-5 font-display text-lg font-bold leading-snug text-ink-900 min-h-[3.25rem]"><?= e($nombreCategoria) ?></h3>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                        <span class="text-xs font-semibold text-ink-500"><?= (int) $c['total_productos'] ?> productos</span>
                        <span class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 grid place-items-center transition group-hover:bg-brand-500 group-hover:text-white">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Cómo funciona -->
<section id="como-funciona" class="bg-white border-y border-slate-100 scroll-mt-24">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-20">
        <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Reservar es simple</p>
            <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold tracking-tight text-ink-900">Tres pasos y listo</h2>
            <p class="mt-3 text-ink-500">Sin formularios complicados ni pagos anticipados.</p>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <?php
            $pasos = [
                ['fa-table-cells', 'Explora el catálogo', 'Filtra por categoría o busca el producto que necesitas.'],
                ['fa-cart-plus', 'Arma tu carrito', 'Elige las cantidades y revisa tu lista antes de reservar.'],
                ['fa-calendar-check', 'Reserva y retira', 'Indica la fecha de retiro y te confirmamos la reserva.'],
            ];
            foreach ($pasos as $i => [$iconoPaso, $tituloPaso, $textoPaso]): ?>
                <div class="relative rounded-3xl border border-slate-100 bg-cream p-6 md:p-7" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                    <span class="absolute right-5 top-4 font-display text-5xl font-extrabold text-slate-100">0<?= $i + 1 ?></span>
                    <span class="relative w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 grid place-items-center text-xl">
                        <i class="fa-solid <?= e($iconoPaso) ?>"></i>
                    </span>
                    <h3 class="relative mt-5 font-display text-xl font-bold text-ink-900"><?= e($tituloPaso) ?></h3>
                    <p class="relative mt-2 text-sm leading-relaxed text-ink-500"><?= e($textoPaso) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Productos recientes -->
<section class="max-w-7xl mx-auto px-4 py-16 md:py-20">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4" data-aos="fade-up">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Recién agregados</p>
            <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold tracking-tight text-ink-900">Productos recientes</h2>
        </div>
        <a href="/productos.php" class="text-sm font-bold text-brand-700 hover:underline inline-flex items-center gap-2">
            Ver todos <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <?php if (!$destacados): ?>
        <p class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-ink-500">Todavía no hay productos publicados.</p>
    <?php endif; ?>

    <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($destacados as $p): ?>
            <?php include __DIR__ . '/includes/producto-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Ubicación -->
<section id="ubicacion" class="max-w-7xl mx-auto px-4 py-10 md:py-14 scroll-mt-24">
    <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Encuéntranos</p>
        <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold tracking-tight text-ink-900">Nuestra ubicación</h2>
        <p class="mt-3 text-ink-500">Retira tu reserva en tienda, en la zona de Murillo (El Alto – La Paz).</p>
    </div>

    <div class="mt-9 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="flex flex-col justify-between rounded-4xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8" data-aos="fade-up">
            <div>
                <span class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 grid place-items-center text-xl">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
                <h3 class="mt-5 font-display text-xl font-bold text-ink-900">Mi Almacén Laura - Murillo</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink-500">
                    Estamos en la zona de Murillo, El Alto – La Paz, Bolivia. Pásanos a retirar tu pedido en el horario de atención.
                </p>
            </div>

            <ul class="mt-7 space-y-3 text-sm text-ink-600">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-map-pin mt-0.5 w-5 text-brand-600"></i>
                    <span>Murillo, El Alto – La Paz, Bolivia</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-clock mt-0.5 w-5 text-brand-600"></i>
                    <span>Lun a Vie · 9:00 – 18:00</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-brands fa-whatsapp mt-0.5 w-5 text-brand-600"></i>
                    <span><?= e(WHATSAPP_ADMIN_NUMERO) ?></span>
                </li>
            </ul>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="https://www.google.com/maps/search/?api=1&query=-16.503212276280355%2C-68.20760578229628"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-3 font-bold text-white transition hover:bg-brand-700">
                    <i class="fa-solid fa-diamond-turn-right"></i> Cómo llegar
                </a>
                <a href="<?= e(WHATSAPP_ADMIN_URL) ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 font-bold text-ink-700 transition hover:bg-slate-50">
                    <i class="fa-brands fa-whatsapp text-brand-600"></i> Escribir por WhatsApp
                </a>
            </div>
        </div>

        <div class="relative h-72 overflow-hidden rounded-4xl border border-slate-100 bg-white shadow-soft sm:h-96 lg:h-[430px]" data-aos="fade-up" data-aos-delay="100">
            <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d239.09091917697464!2d-68.20720881154716!3d-16.503126352123992!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zLTE2LjUwMzEyNjM1MjEyMzk5MiwtNjguMjA3MjA4ODExNTQ3MTY!5e0!3m2!1ses!2sbo!4v1795450346552!5m2!1ses!2sbo"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                    title="Ubicación de Mi Almacén Laura - Murillo en Google Maps"
                    class="absolute inset-0 h-full w-full"></iframe>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
