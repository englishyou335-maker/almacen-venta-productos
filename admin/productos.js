/* DataTables + ajuste de stock en el listado de productos. */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof DataTable !== 'undefined') {
        new DataTable('#tabla-productos', {
            responsive: true,
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ por página',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ productos',
                infoEmpty: 'No hay productos',
                zeroRecords: 'No se encontraron productos',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columnDefs: [{ orderable: false, targets: [0, 6] }]
        });
    }

    const modal = document.getElementById('modal-stock');
    const abrir = (boton) => {
        document.getElementById('stock-id').value = boton.dataset.id;
        document.getElementById('stock-producto').textContent =
            `${boton.dataset.nombre} — stock actual: ${boton.dataset.stock}`;
        document.getElementById('stock-cantidad').value = '';
        document.getElementById('stock-motivo').value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    const cerrar = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('.btn-stock').forEach((boton) => {
        boton.addEventListener('click', () => abrir(boton));
    });
    document.getElementById('stock-cancelar')?.addEventListener('click', cerrar);
    modal?.addEventListener('click', (e) => { if (e.target === modal) cerrar(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) cerrar();
    });

    document.getElementById('form-stock')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const datos = {
            product_id: Number(document.getElementById('stock-id').value),
            cantidad: Number(document.getElementById('stock-cantidad').value),
            motivo: document.getElementById('stock-motivo').value.trim()
        };
        try {
            const respuesta = await window.adminFetch('/api/actualizar-stock.php', datos);
            if (respuesta.success) {
                cerrar();
                await Swal.fire({
                    icon: 'success',
                    title: 'Stock actualizado',
                    text: `Nuevo stock: ${respuesta.data.stock}`,
                    timer: 1800,
                    showConfirmButton: false
                });
                window.location.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo ajustar',
                    html: (respuesta.errors || ['Error desconocido']).map((e) => `• ${e}`).join('<br>')
                });
            }
        } catch (error) {
            Swal.fire('Error de conexión', 'Inténtalo de nuevo.', 'error');
        }
    });
});
