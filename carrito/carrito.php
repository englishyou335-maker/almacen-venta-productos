<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Mi carrito';
$extraScripts = ['/carrito/reserva.js', '/carrito/qr-pago.js'];
$qrPago = qrPago();
include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <nav class="mb-4 flex items-center gap-2 text-xs text-ink-500">
        <a href="/productos.php" class="hover:text-brand-700">Productos</a>
        <i class="fa-solid fa-chevron-right text-[9px]"></i>
        <span class="font-semibold text-ink-800">Mi carrito</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4" data-aos="fade-up">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Paso 1 de 2</p>
            <h1 class="mt-2 font-display text-3xl md:text-4xl font-extrabold tracking-tight text-ink-900">
                <i class="fa-solid fa-cart-shopping mr-2 text-brand-500"></i>Mi carrito
            </h1>
            <p class="mt-3 text-ink-500">Revisa las cantidades y completa tus datos para reservar.</p>
        </div>
        <a href="/productos.php" class="inline-flex items-center gap-2 self-start text-sm font-bold text-brand-700 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Seguir explorando
        </a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_340px]">
        <section class="rounded-3xl border border-slate-100 bg-white p-4 shadow-soft sm:p-6" data-aos="fade-up">
            <div class="mb-2 flex items-center justify-between px-1">
                <h2 class="font-display text-lg font-bold text-ink-900">Productos seleccionados</h2>
                <span class="text-xs text-ink-500">Ajusta las cantidades si quieres</span>
            </div>
            <div id="carrito-items"></div>
        </section>

        <aside class="h-fit rounded-3xl border border-slate-100 bg-white p-6 shadow-soft lg:sticky lg:top-24" data-aos="fade-up" data-aos-delay="100">
            <span class="w-11 h-11 rounded-2xl bg-brand-50 text-brand-600 grid place-items-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </span>
            <h2 class="mt-4 font-display text-xl font-bold text-ink-900">Resumen</h2>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between text-ink-500">
                    <span>Unidades</span><span id="carrito-cantidades" class="font-bold text-ink-900">0</span>
                </div>
                <div class="flex justify-between text-ink-500">
                    <span>Entrega</span><span class="font-semibold text-ink-800">Retiro en tienda</span>
                </div>
                <div class="flex justify-between text-ink-500">
                    <span>Pago</span><span class="font-semibold text-ink-800">Al retirar</span>
                </div>
            </div>
            <div class="mt-5 flex items-end justify-between border-t border-slate-100 pt-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Total estimado</p>
                    <p id="carrito-total" class="mt-1 font-display text-2xl font-extrabold text-ink-900">A consultar</p>
                </div>
            </div>
            <p class="mt-4 rounded-xl bg-cream p-3 text-[11px] leading-relaxed text-ink-500">
                <i class="fa-solid fa-circle-info mr-1 text-brand-500"></i>
                El precio y la disponibilidad se confirman al retirar.
            </p>
        </aside>
    </div>

    <?php if ($qrPago['url'] !== ''): ?>
        <section id="pago-qr" class="mt-6 max-w-4xl rounded-3xl border border-brand-100 bg-white p-5 shadow-soft sm:p-8" data-aos="fade-up">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                <div class="shrink-0 rounded-3xl bg-cream p-3 ring-1 ring-slate-100">
                    <img id="qr-pago-imagen" src="<?= e($qrPago['url']) ?>"
                         data-url="<?= e($qrPago['url']) ?>"
                         alt="Código QR para pago rápido"
                         class="mx-auto h-44 w-44 object-contain sm:h-52 sm:w-52">
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Pago rápido con QR</p>
                    <h2 class="mt-2 font-display text-xl font-bold text-ink-900 sm:text-2xl">Escanea y paga con QR al retirar tu reserva</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-500">
                        Descarga la imagen o compártela con quien vaya a retirar el pedido.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="<?= e(urlDescargaCloudinary($qrPago['url'])) ?>" download="qr-pago"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-3 font-bold text-white transition hover:bg-brand-700">
                            <i class="fa-solid fa-download"></i> Descargar QR
                        </a>
                        <button id="btn-compartir-qr" type="button"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 font-bold text-ink-700 transition hover:border-brand-300 hover:text-brand-700 disabled:opacity-50">
                            <i class="fa-solid fa-share-nodes"></i> Compartir QR
                        </button>
                    </div>
                    <p class="mt-4 text-[11px] leading-relaxed text-ink-500">
                        <i class="fa-solid fa-circle-info mr-1 text-brand-500"></i>
                        No se realiza ningún pago online: el QR es solo una forma rápida de pagar al retirar.
                    </p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Formulario de reserva -->
    <form id="form-reserva"
          class="mt-6 max-w-4xl rounded-3xl border border-slate-100 bg-white p-5 shadow-soft sm:p-8" data-aos="fade-up">
        <div class="flex items-start gap-4 border-b border-slate-100 pb-5">
            <span class="w-12 h-12 shrink-0 rounded-2xl bg-ink-900 text-brand-300 grid place-items-center text-xl">
                <i class="fa-solid fa-calendar-check"></i>
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Paso 2 de 2</p>
                <h2 class="mt-1 font-display text-xl font-bold text-ink-900 sm:text-2xl">Datos para la reserva</h2>
                <p class="mt-1 text-sm text-ink-500">Te contactaremos para confirmarla.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-semibold text-ink-800">Nombre completo *</span>
                <input id="cliente-nombre" required minlength="3" maxlength="255" autocomplete="name"
                       placeholder="Tu nombre y apellido"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-cream px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
            </label>
            <label class="block">
                <span class="text-sm font-semibold text-ink-800">Teléfono *</span>
                <input id="cliente-telefono" type="tel" required inputmode="numeric"
                       pattern="[67][0-9]{7}" minlength="8" maxlength="8"
                       autocomplete="tel" placeholder="62585878"
                       title="8 dígitos, empieza con 6 o 7"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-cream px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
            </label>
            <label class="block">
                <span class="text-sm font-semibold text-ink-800">Fecha deseada de retiro *</span>
                <input id="cliente-fecha" type="date" required
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-cream px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100">
            </label>
        </div>
        <label class="mt-5 block">
            <span class="text-sm font-semibold text-ink-800">Notas (opcional)</span>
            <textarea id="cliente-notas" rows="3" maxlength="1000" placeholder="Indicaciones para tu pedido..."
                      class="mt-2 w-full rounded-xl border border-slate-200 bg-cream px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"></textarea>
        </label>

        <div class="mt-7 flex flex-col gap-4 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-start gap-2 text-xs text-ink-500 max-w-sm">
                <i class="fa-solid fa-lock mt-0.5 text-brand-500"></i>
                Tu stock queda apartado al enviar la reserva. No se realiza ningún cobro online.
            </p>
            <button id="btn-reservar" type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-7 py-3.5 font-bold text-white shadow-soft transition hover:bg-brand-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                <i class="fa-solid fa-check"></i> Confirmar reserva
            </button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
