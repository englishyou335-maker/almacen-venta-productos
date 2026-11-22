    <?php if (($seccion ?? '') !== 'base-datos'): ?>
        <div class="mt-10 border-t border-slate-200 pt-6 flex justify-center">
            <a href="/admin/base-datos.php"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-ink-600 shadow-soft transition hover:border-brand-300 hover:text-brand-700">
                <i class="fa-solid fa-database text-brand-500"></i> Ver base de datos
            </a>
        </div>
    <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="/assets/js/admin.js"></script>
<?php foreach ($extraScripts as $src): ?>
    <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
<?php
$flash = flashLeer();
if ($flash !== []): ?>
    <script>
        Swal.fire({
            icon: <?= json_encode($flash['tipo'] ?? 'success') ?>,
            title: <?= json_encode($flash['mensaje'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
            timer: 2500,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>
</body>
</html>
