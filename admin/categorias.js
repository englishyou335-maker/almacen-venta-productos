document.addEventListener('DOMContentLoaded', () => {
    if (typeof DataTable !== 'undefined') {
        new DataTable('#tabla-categorias', {
            responsive: true,
            language: {
                search: 'Buscar:',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ categorías',
                infoEmpty: 'No hay categorías',
                zeroRecords: 'No se encontraron categorías',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columnDefs: [{ orderable: false, targets: [3] }]
        });
    }
});
