/* Descarga y compartir del QR configurado desde el panel. */
document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btn-compartir-qr');
    const imagen = document.getElementById('qr-pago-imagen');
    if (!boton || !imagen) return;

    const texto = 'QR de pago de Mi Almacén Laura - Murillo. Escanea y paga al retirar tu reserva.';

    async function copiarEnlace() {
        try {
            await navigator.clipboard.writeText(imagen.dataset.url);
            Swal.fire('Enlace copiado', 'Ya puedes pegar el enlace del QR donde quieras.', 'success');
        } catch (error) {
            Swal.fire('Comparte este QR', imagen.dataset.url, 'info');
        }
    }

    async function compartirEnlace() {
        if (!navigator.share) {
            await copiarEnlace();
            return;
        }
        try {
            await navigator.share({ title: 'QR de pago', text: texto, url: imagen.dataset.url });
        } catch (error) {
            if (error && error.name !== 'AbortError') await copiarEnlace();
        }
    }

    boton.addEventListener('click', async () => {
        if (!navigator.share || !navigator.canShare || !window.fetch) {
            await compartirEnlace();
            return;
        }

        boton.disabled = true;
        try {
            const respuesta = await fetch(imagen.dataset.url, { mode: 'cors' });
            if (!respuesta.ok) throw new Error('No se pudo descargar la imagen');
            const blob = await respuesta.blob();
            const extension = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
            const archivo = new File([blob], 'qr-pago.' + extension, { type: blob.type || 'image/png' });
            if (navigator.canShare({ files: [archivo] })) {
                await navigator.share({ files: [archivo], title: 'QR de pago', text: texto });
            } else {
                await compartirEnlace();
            }
        } catch (error) {
            if (!error || error.name !== 'AbortError') await copiarEnlace();
        } finally {
            boton.disabled = false;
        }
    });
});
