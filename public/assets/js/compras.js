// JavaScript del formulario de compras
var CSRF = $('meta[name="csrf-token"]').attr('content');
var URL_PRODUCTOS = 'modules/compras/productos_pedidos.php';
var URL_TMP = 'modules/compras/tmp.php';
var LOADER = '<img src="assets/img/ajax-loader.gif" alt="Cargando..."> Cargando...';

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
    var q = $('#q').val();
    $('#loader').html(LOADER);
    $.ajax({
        url: URL_PRODUCTOS,
        type: 'GET',
        data: { q: q, page: page },
        success: function (html) {
            $('#outer_div').html(html);
        },
        error: function (xhr) {
            errorAjax(xhr, '#outer_div');
        },
        complete: function () {
            $('#loader').html('');
        }
    });
}

// Carga el detalle (tabla temporal) con los subtotales y el total
function cargarDetalle() {
    $.ajax({
        url: URL_TMP,
        type: 'GET',
        data: { act: 'list' },
        success: function (html) { $('#detalle-compra').html(html); },
        error: function (xhr) { errorAjax(xhr, '#detalle-compra'); }
    });
}

// Envía una operación sobre la tabla temporal y refresca el detalle
function operarTmp(act, datos) {
    datos.csrf = CSRF;
    $('#detalle-compra').html('<div class="text-center p-3">' + LOADER + '</div>');
    $.ajax({
        url: URL_TMP + '?act=' + act,
        type: 'POST',
        data: datos,
        success: function (html) { $('#detalle-compra').html(html); },
        error: function (xhr) { errorAjax(xhr, '#detalle-compra'); }
    });
}

// Agrega el producto con la cantidad y el precio ingresados
function agregar(cod) {
    var cantidad = parseInt($('#cantidad_' + cod).val(), 10);
    var precio = parseInt($('#precio_' + cod).val(), 10);

    if (isNaN(cantidad) || cantidad < 1) {
        alert('Ingrese una cantidad válida (número entero mayor a cero).');
        $('#cantidad_' + cod).focus();
        return;
    }
    if (isNaN(precio) || precio < 1) {
        alert('Ingrese un precio válido (número entero mayor a cero).');
        $('#precio_' + cod).focus();
        return;
    }
    operarTmp('add', { cod: cod, cantidad: cantidad, precio: precio });
}

// Quita un producto del detalle
function eliminar(idTmp) {
    if (confirm('¿Quitar este producto de la compra?')) {
        operarTmp('del', { id_tmp: idTmp });
    }
}

$(function () {
    cargarDetalle();

    // Al abrir el modal se carga la primera página de productos
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

    $('#btn-vaciar').on('click', function () {
        if (confirm('¿Quitar todos los productos cargados?')) {
            operarTmp('vaciar', {});
        }
    });

    // Validación antes de guardar: debe haber al menos un producto
    $('#form-compra').on('submit', function (ev) {
        var items = parseInt($('#tabla-detalle').data('items'), 10) || 0;
        if (items === 0) {
            ev.preventDefault();
            alert('Debe agregar al menos un producto a la compra.');
            return;
        }
        if (!confirm('¿Confirma registrar la compra?')) {
            ev.preventDefault();
            return;
        }
        $('#btn-guardar').prop('disabled', true); // evita doble envío
    });

    // Reloj del campo Hora
    setInterval(function () {
        var d = new Date();
        var hh = ('0' + d.getHours()).slice(-2), mm = ('0' + d.getMinutes()).slice(-2), ss = ('0' + d.getSeconds()).slice(-2);
        $('#hora').val(hh + ':' + mm + ':' + ss);
    }, 1000);
});
