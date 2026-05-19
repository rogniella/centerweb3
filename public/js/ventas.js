// ============================================
// Ventas — Lógica compartida (Ventas, pagos)
// ============================================

// === 1. CONFIGURACIÓN ===
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

var VALUES_IVA = { 5: 21, 4: 10.5, 1: 0 };
var METODOS_PAGO = {
    P: 'Pesos', T: 'Tarjeta Crédito', TB: 'Transferencia Bancaria',
    CC: 'Cuenta Corriente', PP: 'PayPal'
};

// === 2. ESTADO GLOBAL ===
var tipo_de_factura = '';
var venta = {
    items: {}, pagos: {}, items_count: 0, pagos_count: 0, total: 0, values_iva: VALUES_IVA,

    calculaPrecioConIva: function (precio, porc) {
        return precio + ((precio * porc) / 100);
    },

    addPago: function (moneda, monto, cuotas, cotizacion) {
        this.pagos[this.pagos_count] = { moneda: moneda, monto: monto, cuotas: cuotas, cotizacion: cotizacion };
        this.pagos_count++;
        return (this.pagos_count - 1);
    },

    addItem: function (id_producto, id_familia, descrip_producto, id_iva, cantidad, precio_unitario, bonif_unitario, precio_total) {
        var porc = this.values_iva[id_iva];
        var precio_con_iva = this.calculaPrecioConIva(precio_unitario, porc);
        this.items[this.items_count] = {
            id_producto: id_producto, id_familia: id_familia, descrip_producto: descrip_producto,
            id_iva: id_iva, cantidad: cantidad, precio_unitario: precio_unitario,
            bonif_unitario: bonif_unitario, precio_total: precio_total, precio_con_iva: precio_con_iva
        };
        this.total += precio_total;
        this.items_count++;
        return (this.items_count - 1);
    },

    deleteItem: function (key) {
        this.total -= this.items[key].precio_total;
        delete this.items[key];
    }
};

var pagosRealizados = [];
var totalPagar = 0;
var faltaPagar = 0;

// === 3. AUTONUMERIC (inicializado al cargar) ===
const auxcantidad = new AutoNumeric(document.getElementById('cantidad'), { digitGroupSeparator: '.', decimalCharacter: ',', decimalPlaces: 0 });
const auxprecio_unitario = new AutoNumeric(document.getElementById('precio_unitario'), 'commaDecimalCharDotSeparator');
const auxbonif_unitario = new AutoNumeric(document.getElementById('bonif_unitario'), { digitGroupSeparator: '.', decimalCharacter: ',', decimalPlaces: 0 });

// === 4. MÉTODOS DE PAGO ===

function obtenerNombreMetodo(codigo) {
    return METODOS_PAGO[codigo] || 'Otro';
}

function agregarMetodoPago() {
    var metodo = $('#metodoPago').val();
    if (!metodo) {
        Swal.fire({ type: 'error', title: 'Error', text: 'Seleccione un método de pago' });
        return;
    }

    var monto = 0, detalle = '', cuotas = 1;

    switch (metodo) {
        case 'P':
            monto = parseFloat($('#montoPago').val()) || 0;
            detalle = 'Efectivo';
            break;
        case 'T':
            monto = parseFloat($('#montoTarjeta').val()) || 0;
            cuotas = parseInt($('#cuotasTarjeta').val()) || 1;
            detalle = 'Tarjeta (' + cuotas + (cuotas === 1 ? ' cuota' : ' cuotas') + ')';
            break;
        case 'TB':
            monto = parseFloat($('#montoTransferencia').val()) || 0;
            detalle = 'Transferencia';
            break;
        case 'CC':
            monto = parseFloat($('#montoCC').val()) || 0;
            detalle = 'Cuenta Corriente';
            break;
    }

    if (monto <= 0) {
        Swal.fire({ type: 'error', title: 'Error', text: 'Ingrese un monto válido' });
        return;
    }

    pagosRealizados.push({ metodo: metodo, detalle: detalle, monto: monto, cuotas: cuotas });
    actualizarTablaPagos();
    actualizarTotales();

    $('#metodoPago').val('');
    $('#opcionesPago').empty();
    $('#addBtnPago').hide();
}

function eliminarPago(index) {
    pagosRealizados.splice(index, 1);
    actualizarTablaPagos();
    actualizarTotales();
}

function actualizarTablaPagos() {
    if (pagosRealizados.length === 0) {
        $('#tbl-pagos').hide();
        return;
    }
    $('#tbl-pagos').show();
    var tbody = $('#tbl-pagos tbody');
    tbody.empty();
    $.each(pagosRealizados, function (i, pago) {
        tbody.append(
            '<tr>' +
                '<td>' + obtenerNombreMetodo(pago.metodo) + '</td>' +
                '<td>' + pago.detalle + '</td>' +
                '<td align="right">' + formatearNumeroConSeparadorDeMiles(pago.monto, 2) + '</td>' +
                '<td align="center"><button type="button" class="btn btn-xs btn-danger" onclick="eliminarPago(' + i + ')"><i class="glyphicon glyphicon-trash"></i></button></td>' +
            '</tr>'
        );
    });
}

function ingresar_pagos() {
    if (pagosRealizados.length === 0) {
        venta.addPago('P', venta.total, 1, 1);
        return;
    }
    $.each(pagosRealizados, function (_, pago) {
        venta.addPago(pago.metodo, pago.monto, pago.cuotas || 1, 1);
    });
}

// === 5. PRODUCTOS ===

function busca_articulo() { }

function ingresar_articulo() {
    var id_producto = $('#id_producto').val();
    var id_familia = $('#id_familia').val();
    var descrip_producto = $('#descrip_producto').val();
    var id_iva = 5;
    var cantidad = Number(numberFormatBd($('#cantidad').val()));
    var precio_unitario = Number(numberFormatBd($('#precio_unitario').val()));
    var bonif_unitario = Number(numberFormatBd($('#bonif_unitario').val()));
    var precio_total = Number(precio_unitario * cantidad * (1 - (bonif_unitario / 100)));

    if (!id_producto || !descrip_producto) {
        Swal.fire({ type: 'error', title: 'Error', text: 'Debe ingresar el código y descripción del artículo' });
        return false;
    }
    if (cantidad === 0 || precio_unitario === 0) {
        Swal.fire({ type: 'error', title: 'Error', text: 'Debe ingresar cantidad y precio mayor a cero' });
        return false;
    }

    var key = venta.addItem(id_producto, id_familia, descrip_producto, id_iva, cantidad, precio_unitario, bonif_unitario, precio_total);

    $('#tbl-items tbody').append(
        '<tr id="row-' + key + '">' +
            '<td align="right">' + formatearNumeroConSeparadorDeMiles(cantidad, 0) + '</td>' +
            '<td>' + descrip_producto + '</td>' +
            '<td align="right">' + formatearNumeroConSeparadorDeMiles(precio_unitario, 2) + '</td>' +
            '<td align="right">' + formatearNumeroConSeparadorDeMiles(bonif_unitario, 0) + ' %</td>' +
            '<td align="right">' + formatearNumeroConSeparadorDeMiles(precio_total, 2) + '</td>' +
            '<td align="center"><button type="button" class="btn btn-xs btn-danger" onclick="borrar_articulo(' + key + ')"><i class="glyphicon glyphicon-trash"></i></button></td>' +
        '</tr>'
    );

    actualizarTotales();
    $('#id_producto, #descrip_producto').val('');
    auxcantidad.clear();
    auxprecio_unitario.clear();
    auxbonif_unitario.clear();
    $('#id_familia').focus();
}

function borrar_articulo(key) {
    venta.deleteItem(key);
    $('#row-' + key).remove();
    actualizarTotales();
}

// === 6. CLIENTES ===

function searchByFormdata() {
    $('#modif-cliente-btn, #datos_cliente').show();
    $('#id_cliente').val($('#operation').val() === 'store' ? $('#id').val() : $('#Cli_Id').val());
    $('#id_clienteweb').val($('#id').val());
    $('#nombre_cliente').html($('#Cli_ApeNom').val());
    $('#dni_cliente').html($('#Cli_CodDocumento').val() + ':' + $('#Cli_Documento').val());
    $('#telefono_cliente').html('Teléfono:' + $('#Cli_Pais').val() + ' ' + $('#Cli_Telefono').val());
}

function BtnModificaCliente() {
    var id = $('#id_cliente').val();
    if (id.length < 1 || isNaN(id)) return false;
    $('#msgErrDNI').hide();
    showEditModal(true, $('#id_clienteweb').val());
}

function BtnNuevoCliente() {
    $('#msgErrDNI').hide();
    showEditModal(false, 0);
}

// === 7. TOTALES ===

function actualizarTotales() {
    $('#total').val(formatearNumeroConSeparadorDeMiles(venta.total, 2));
    totalPagar = venta.total;

    var sumaPagos = pagosRealizados.reduce(function (acc, p) { return acc + p.monto; }, 0);
    faltaPagar = totalPagar - sumaPagos;

    $('#VentaButton, #PresuButton').prop('disabled', totalPagar <= 0);

    var cambio = sumaPagos > totalPagar ? sumaPagos - totalPagar : 0;
    var estado = faltaPagar > 0.01 ? 'warning' : (cambio > 0.01 ? 'info' : 'success');
    var lineas = '';

    lineas += '<tr><td>Total</td><td class="text-right"><b>$' + formatearNumeroConSeparadorDeMiles(totalPagar, 2) + '</b></td></tr>';
    lineas += '<tr><td>Pagado</td><td class="text-right">$' + formatearNumeroConSeparadorDeMiles(sumaPagos, 2) + '</td></tr>';

    if (faltaPagar > 0.01) {
        lineas += '<tr style="color:#c9302c"><td>Saldo pendiente</td><td class="text-right"><b>$' + formatearNumeroConSeparadorDeMiles(faltaPagar, 2) + '</b></td></tr>';
    } else if (cambio > 0.01) {
        lineas += '<tr style="color:#1e7e34"><td>Cambio</td><td class="text-right"><b>$' + formatearNumeroConSeparadorDeMiles(cambio, 2) + '</b></td></tr>';
    } else {
        lineas += '<tr style="color:#1e7e34"><td colspan="2" class="text-center">✓ Completo</td></tr>';
    }

    var resumen =
        '<div class="alert alert-' + estado + '" style="font-size:17px; font-weight:bold; padding:12px;">' +
            '<table style="width:100%;font-size:17px;">' + lineas + '</table>' +
        '</div>';
    $('#resumenPagos').html(resumen);
}

// === 8. FINALIZAR ===

function FinalizaVenta() { Finalizar('Vta'); }
function FinalizaPresu() { Finalizar('Presu'); }

async function Finalizar(operacion) {
    if (Object.keys(venta.items).length === 0) {
        Swal.fire({ type: 'error', title: 'Error', text: 'Debe cargar al menos un artículo' });
        return false;
    }

    var operacionDetalle = operacion === 'Vta' ? 'esta Venta' : 'este PRESUPUESTO';
    var numeroOrig = '', punto_facturaOriginal = '';

    if (operacion === 'Vta') {
        if (tipo_de_factura !== 'Z') tipo_de_factura = $('#id_tipo_cbte').val();
        if (tipo_de_factura == 3 || tipo_de_factura == 8) {
            var ncResult = await Swal.fire({
                title: 'Nota de Crédito — Comprobante Original',
                html:
                    '<input id="swal-punto" class="swal2-input" placeholder="Punto de Venta" style="margin-bottom:8px">' +
                    '<input id="swal-numero" class="swal2-input" placeholder="Nro. Comprobante">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                preConfirm: function () {
                    var pto = $('#swal-punto').val().trim();
                    var nro = $('#swal-numero').val().trim();
                    if (!pto) { Swal.showValidationMessage('Ingrese el Punto de Venta'); return false; }
                    if (!nro) { Swal.showValidationMessage('Ingrese el Nro. de Comprobante'); return false; }
                    return { punto: pto, numero: nro };
                }
            });
            if (!ncResult.value) return false;
            punto_facturaOriginal = ncResult.value.punto;
            numeroOrig = ncResult.value.numero;
        }

        if (pagosRealizados.length === 0) {
            Swal.fire({ type: 'error', title: 'Error', text: 'Debe agregar al menos una forma de pago' });
            return false;
        }

        var sumaPagos = pagosRealizados.reduce(function (acc, p) { return acc + p.monto; }, 0);
        if (sumaPagos < venta.total - 0.01) {
            Swal.fire({ type: 'error', title: 'Error', text: 'El total de pagos aún no cubre el importe de la venta' });
            return false;
        }
    }

    var confirmResult = await Swal.fire({
        title: 'Finalizar ' + operacionDetalle + '?<br>Total: $' + $('#total').val(),
        text: '¿Está seguro de cerrar ' + operacionDetalle + '?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Finalizar'
    });
    if (!confirmResult.value) return;

    $('#VentaButton, #PresuButton').prop('disabled', true).html(
        '<i class="fa fa-spinner fa-spin"></i> Procesando...'
    );

    ingresar_pagos();

    var json_items = JSON.stringify(venta.items);
    var json_pagos = JSON.stringify(venta.pagos);
    $('#json_items').val(json_items);
    $('#json_pagos').val(json_pagos);

    var formdata = {
        sucursal: $('#sucursal').val(),
        id_vendedor: $('#id_vendedor').val(),
        id_tipo_cbte: tipo_de_factura,
        numeroOrig: numeroOrig,
        punto_facturaOriginal: punto_facturaOriginal,
        fecha: $('#fecha').val(),
        observaciones: $('#observaciones').val(),
        operacion: operacion,
        id_cliente: $('#id_cliente').val(),
        json_items: json_items,
        json_pagos: json_pagos
    };

    formdata._token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        dataType: 'json',
        type: 'post',
        data: formdata,
        url: 'store',
        success: function (data) {
            if (!data.retError) {
                if (data.pdf) {
                    window.open(data.pdf, '', '_blank');
                } else if (data.errorAFIP) {
                    window.open('generaComprobanteAFIP?id=' + data.id + '&tipo=VT', '', '_blank');
                }
                inicializar();
                muestroMsg(data.mensaje);
            } else {
                msgerror(data.retError);
                $('#VentaButton, #PresuButton').prop('disabled', false);
                $('#VentaButton').html('COBRAR');
                $('#PresuButton').html('PRESUPUESTAR');
            }
        },
        error: function (xhr) {
            msgerror(xhr.responseText);
            $('#VentaButton, #PresuButton').prop('disabled', false);
            $('#VentaButton').html('COBRAR');
            $('#PresuButton').html('PRESUPUESTAR');
        }
    });
}

// === 9. INICIALIZAR ===

function inicializar() {
    venta.values_iva = VALUES_IVA;
    tipo_de_factura = '';
    pagosRealizados = [];

    venta.items = {};
    venta.pagos = {};
    venta.items_count = 0;
    venta.pagos_count = 0;
    venta.total = 0;

    totalPagar = 0;
    faltaPagar = 0;

    $('#id_cliente, #observaciones').val('');
    $('#total').val('0');
    $('#datos_cliente, #modif-cliente-btn').hide();
    $('#tbl-items tbody, #tbl-pagos tbody').empty();
    $('#tbl-pagos, #addBtnPago').hide();
    $('#opcionesPago, #resumenPagos').empty();
    $('#VentaButton, #PresuButton').prop('disabled', false);
    $('#VentaButton').html('COBRAR');
    $('#PresuButton').html('PRESUPUESTAR');
}

function valida_estado_servidor() {
    $('#msgErrAfip').hide();
    $.ajax({
        dataType: 'json',
        type: 'get',
        url: '../afip/valida_estado_servidor',
        success: function (data) {
            if (data.msgError) {
                $('#msgErrAfip').html('Afip:' + data.msgError + '<br>' + data.informacion).show();
            }
        },
        error: function (xhr) {
            msgerror('Al Validar Afip:' + xhr.responseText);
        }
    });
}

// === 10. DOM READY ===

$(document).ready(function () {
    $('#datos_cliente, #modif-cliente-btn, #tbl-pagos, #addBtnPago').hide();

    // --- Cliente typeahead ---
    $('#id_cliente').typeahead({
        items: 10, minLength: 3, highlight: true,
        source: function (query, process) {
            $.ajax({
                global: false, dataType: 'json', data: {},
                url: '../clientes/busca_autocompletar?terms=' + query,
                type: 'get',
                success: function (data) {
                    if (data.length <= 0) {
                        $('#datos_cliente').show();
                        $('#nombre_cliente').html('No se encuentra coincidencia');
                        $('#dni_cliente, #telefono_cliente').html('');
                    }
                    process(data);
                },
                error: function () {
                    $('#datos_cliente').show();
                    $('#nombre_cliente').html('Error al buscar');
                }
            });
        },
        afterSelect: function (item) {
            $('#datos_cliente, #modif-cliente-btn').show();
            $('#id_cliente').val(item.idSUC);
            $('#id_clienteweb').val(item.id);
            $('#nombre_cliente').html(item.apenom);
            $('#dni_cliente').html(item.coddocumento + ':' + item.documento);
            $('#telefono_cliente').html('Teléfono:' + item.telefono);
            $('#id_familia').focus();
        }
    });

    // --- Producto typeahead ---
    $('#id_producto').typeahead({
        items: 15, minLength: 2, highlight: true,
        source: function (query, process) {
            var familia = $('#id_familia').val();
            if (familia === 'VAR' && query === '99') return;
            $.ajax({
                global: false, dataType: 'json', data: {},
                url: '../productos/buscaproducto?terms=' + query + '&familia=' + familia,
                type: 'get',
                success: function (data) { process(data); },
                error: function (xhr) { msgerror(xhr.responseText); }
            });
        },
        afterSelect: function (item) {
            $('#id_producto').val(item.id);
            $('#descrip_producto').val(item.name);
            auxcantidad.set(1);
            auxprecio_unitario.set(item.precio);
            $('#cantidad').focus();
        }
    });

    // --- Atajos de teclado ---
    $('#id_familia').keyup(function (e) { if (e.which === 13) $('#id_producto').focus(); });

    $('#id_producto').keyup(function (e) {
        if (e.which === 13) {
            if ($('#id_familia').val() === 'VAR' && $('#id_producto').val() === '99') {
                $('#descrip_producto').prop('disabled', false).focus();
            }
        }
    });

    $('#id_producto').on('input', function () {
        var familia = $('#id_familia').val();
        var producto = $(this).val();

        if (familia === 'VAR' && producto === '99') {
            $('#descrip_producto').prop('disabled', false);
        } else {
            $('#descrip_producto').prop('disabled', true).val('');
        }
    });

    $('#descrip_producto').keyup(function (e) {
        if (e.which === 13) {
            $('#cantidad').focus();
        }
    });

    $('#id_familia').change(function () {
        var producto = $('#id_producto').val();
        if ($(this).val() !== 'VAR' || producto !== '99') {
            $('#descrip_producto').prop('disabled', true).val('');
        }
    });

    $('#cantidad').keyup(function (e) { if (e.which === 13) $('#precio_unitario').focus(); });
    $('#precio_unitario').keyup(function (e) { if (e.which === 13) $('#bonif_unitario').focus(); });
    $('#bonif_unitario').keyup(function (e) { if (e.which === 13) ingresar_articulo(); });

    $(document).keyup(function (e) {
        if (e.which === 120) { tipo_de_factura = 'Z'; FinalizaVenta(); }
    });

    // --- Enter en campos de monto de pago (event delegation) ---
    $('#opcionesPago').on('keyup', 'input', function (e) {
        if (e.which === 13) agregarMetodoPago();
    });

    // --- Cambio de método de pago ---
    $('#metodoPago').change(function () {
        var metodo = this.value;
        var opciones = $('#opcionesPago').empty();
        $('#addBtnPago').show();
        var html = '';

        var saldo = faltaPagar > 0 ? faltaPagar.toFixed(2) : '';

        switch (metodo) {
            case 'P':
                html = '<div class="input-group"><span class="input-group-addon">$</span><input class="form-control text-right" type="number" id="montoPago" step="0.01" value="' + saldo + '"></div>';
                break;
            case 'T':
                html = '<div class="row" style="margin-left:0;margin-right:0">' +
                        '<div class="col-xs-4" style="padding-left:0"><input type="number" class="form-control" id="cuotasTarjeta" placeholder="Cuotas" value="1" min="1"></div>' +
                        '<div class="col-xs-8" style="padding-right:0"><div class="input-group"><span class="input-group-addon">$</span><input class="form-control text-right" type="number" id="montoTarjeta" step="0.01" value="' + saldo + '"></div></div>' +
                    '</div>';
                break;
            case 'TB':
                html = '<div class="input-group"><span class="input-group-addon">$</span><input class="form-control text-right" type="number" id="montoTransferencia" step="0.01" value="' + saldo + '"></div>';
                break;
            case 'CC':
                html = '<div class="input-group"><span class="input-group-addon">$</span><input class="form-control text-right" type="number" id="montoCC" step="0.01" value="' + saldo + '"></div>';
                break;
        }

        opciones.html(html);
    });
});

// === 11. BOOTSTRAP ===
inicializar();
valida_estado_servidor();
