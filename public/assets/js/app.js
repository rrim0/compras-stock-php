// Inicialización común: tablas con DataTables (búsqueda, orden y paginación)
$(function () {
    var idioma = {
        decimal: ',', thousands: '.',
        processing: 'Procesando...',
        lengthMenu: 'Mostrar _MENU_ registros',
        zeroRecords: 'No se encontraron resultados',
        emptyTable: 'Ningún dato disponible en esta tabla',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
        infoFiltered: '(filtrado de _MAX_ registros)',
        search: 'Buscar:',
        paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' }
    };

    $('table.datatable').DataTable({
        language: idioma,
        pageLength: 10,
        columnDefs: [{ targets: 'no-sort', orderable: false, searchable: false }]
    });

    $('.select2').select2({ theme: 'bootstrap4', language: 'es', width: '100%' });

    // Vista previa de la foto de perfil antes de guardar
    $('#foto').on('change', function () {
        var archivo = this.files && this.files[0];
        if (archivo && /^image\/(jpeg|png)$/.test(archivo.type)) {
            $('#foto-preview').attr('src', URL.createObjectURL(archivo));
        }
    });
});
