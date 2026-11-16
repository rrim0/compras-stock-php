// JavaScript del formulario de ventas
var CSRF = $('meta[name="csrf-token"]').attr('content');
var URL_PRODUCTOS = 'modules/ventas/productos.php';
var URL_TMP = 'modules/ventas/tmp.php';
var LOADER = '<img src="assets/img/ajax-loader.gif" alt="Cargando..."> Cargando...';

function deposito() {
    return $('#id_deposito').val() || 0;
}

// Muestra el error del servidor; si la sesión terminó, vuelve al login
function errorAjax(xhr, destino) {
    if (xhr.status === 401) {
        alert(xhr.responseText);
        window.location = 'index.php?alert=expirada';
        return;
    }
    var msg = xhr.responseText || 'No se pudo comunicar con el servidor. Verifique su conexión.';
    $(destino).html('<div class="alert alert-danger">' + $('<div>').text(msg).html() + '</div>');
}

// Carga la tabla de productos de la página indicada
function load(page) {
    $('#loader').html(LOADER);
    $.ajax({
        url: URL_PRODUCTOS,
        type: 'GET',
        data: { q: $('#q').val(), page: page, deposito: deposito() },
        success: function (html) { $('#outer_div').html(html); },
        error: function (xhr) { errorAjax(xhr, '#outer_div'); },
        complete: function () { $('#loader').html(''); }
    });
}

// Carga el detalle (lista temporal) con subtotales, total e IVA
function cargarDetalle() {
    $.ajax({
        url: URL_TMP,
        type: 'GET',
        data: { act: 'list', deposito: deposito() },
        success: function (html) { $('#detalle-venta').html(html); },
        error: function (xhr) { errorAjax(xhr, '#detalle-venta'); }
    });
}

// Envía una operación sobre la lista temporal y refresca el detalle
function operarTmp(act, datos) {
    datos.csrf = CSRF;
    datos.deposito = deposito();
    $.ajax({
        url: URL_TMP + '?act=' + act,
        type: 'POST',
        data: datos,
        success: function (html) { $('#detalle-venta').html(html); },
        error: function (xhr) { errorAjax(xhr, '#detalle-venta'); }
    });
}

// Agrega el producto con la cantidad ingresada
function agregar(cod) {
    var campo = $('#cantidad_' + cod);
    var cantidad = parseInt(campo.val(), 10);
    var maximo = parseInt(campo.attr('max'), 10);

    if (isNaN(cantidad) || cantidad < 1) {
        alert('Ingrese una cantidad válida (número entero mayor a cero).');
        campo.focus();
        return;
    }
    if (cantidad > maximo) {
        alert('La cantidad supera el stock disponible en el depósito (' + maximo + ').');
        campo.focus();
        return;
    }
    operarTmp('add', { cod: cod, cantidad: cantidad });
}

// Quita un producto del detalle
function eliminar(idTmp) {
    if (confirm('¿Quitar este producto de la venta?')) {
        operarTmp('del', { id_tmp: idTmp });
    }
}

$(function () {
    cargarDetalle();

    // El modal necesita saber de qué depósito sale la mercadería
    $('#btn-productos').on('click', function () {
        if (!deposito()) {
            alert('Seleccione primero el depósito.');
            return;
        }
        $('#modal-deposito').text($('#id_deposito option:selected').text());
        $('#modalProductos').modal('show');
    });
    $('#modalProductos').on('shown.bs.modal', function () {
        load(1);
        $('#q').trigger('focus');
    });

    $('#btn-buscar').on('click', function () { load(1); });
    $('#q').on('keypress', function (ev) {
        if (ev.which === 13) {
            ev.preventDefault(); // Enter busca, no envía el formulario
            load(1);
        }
    });

    // Al cambiar de depósito se vuelve a comparar el detalle con el stock de ese depósito
    $('#id_deposito').on('change', cargarDetalle);

    $('#btn-vaciar').on('click', function () {
        if (confirm('¿Quitar todos los productos cargados?')) {
            operarTmp('vaciar', {});
        }
    });

    // Validación antes de guardar
    $('#form-venta').on('submit', function (ev) {
        var items = parseInt($('#tabla-detalle').data('items'), 10) || 0;
        if (items === 0) {
            ev.preventDefault();
            alert('Debe agregar al menos un producto a la venta.');
            return;
        }
        if (!confirm('¿Confirma registrar la venta y emitir la factura?')) {
            ev.preventDefault();
            return;
        }
        $('#btn-guardar').prop('disabled', true); // evita doble envío
    });
});
