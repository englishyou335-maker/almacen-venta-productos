document.addEventListener('DOMContentLoaded', () => {
    if (typeof DataTable !== 'undefined') {
        new DataTable('#tabla-reservas', {
            responsive: true,
            language: {
                search: 'Buscar:',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ reservas',
                infoEmpty: 'No hay reservas',
                zeroRecords: 'No se encontraron reservas',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [7] }]
        });
    }
});
