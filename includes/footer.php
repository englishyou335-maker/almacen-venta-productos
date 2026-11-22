</main>

<footer class="mt-16 bg-ink-950 text-slate-300 relative overflow-hidden">
    <div class="absolute inset-0 hero-malla opacity-40 pointer-events-none"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-500/20 text-brand-300 grid place-items-center">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display font-extrabold text-lg text-white">Mi Almacén Laura</span>
                        <span class="block text-[9px] uppercase tracking-[0.22em] text-brand-300 font-bold">Murillo</span>
                    </span>
                </div>
                <p class="text-sm text-slate-400 mt-4 leading-relaxed">
                    Catálogo de almacén con reserva previa. Arma tu lista, elige la fecha de retiro y pagas en tienda.
                </p>
                <div class="flex gap-2 mt-5">
                    <span class="px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-semibold"><i class="fa-solid fa-store mr-2 text-brand-400"></i>Retiro en tienda</span>
                    <span class="px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-semibold"><i class="fa-solid fa-wallet mr-2 text-brand-400"></i>Sin pago online</span>
                </div>
            </div>

            <div>
                <h4 class="text-white font-display font-bold text-sm uppercase tracking-widest mb-4">Explorar</h4>
                <ul class="space-y-3 text-sm">
                    <li><a class="hover:text-brand-300 transition" href="/index.php">Inicio</a></li>
                    <li><a class="hover:text-brand-300 transition" href="/productos.php">Todos los productos</a></li>
                    <li><a class="hover:text-brand-300 transition" href="/carrito/carrito.php">Mi carrito</a></li>
                    <li><a class="hover:text-brand-300 transition" href="<?= e(WHATSAPP_ADMIN_URL) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-display font-bold text-sm uppercase tracking-widest mb-4">Ayuda</h4>
                <ul class="space-y-3 text-sm">
                    <li><a class="hover:text-brand-300 transition" href="/index.php#como-funciona">Cómo reservar</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-display font-bold text-sm uppercase tracking-widest mb-4">Contacto</h4>
                <a class="text-sm flex items-start gap-3 mb-3 hover:text-brand-300 transition"
                   href="<?= e(WHATSAPP_ADMIN_URL) ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-whatsapp mt-1 text-brand-400"></i><?= e(WHATSAPP_ADMIN_NUMERO) ?>
                </a>
                <p class="text-sm flex items-start gap-3 mt-3"><i class="fa-solid fa-clock mt-1 text-brand-400"></i>Lun a Vie · 9:00 – 18:00</p>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> Mi Almacén Laura - Murillo. Catálogo con reserva previa.</p>
            <p class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-brand-400"></span> Los precios se confirman al retirar.</p>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            Hecho por: Cristian Ronald Mamani Nina y Yoel Cristian Calamani Laura
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="/assets/js/main.js"></script>
<script src="/assets/js/carrito.js"></script>
<?php foreach ($extraScripts as $src): ?>
    <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
<script>
    if (window.AOS) AOS.init({ duration: 650, once: true, offset: 60 });
</script>
</body>
</html>
