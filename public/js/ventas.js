// ============================================
// Ventas — Lógica compartida (Ventas, pagos)
// ============================================

// === 1. CONFIGURACIÓN ===
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

var VALUES_IVA = { 5: 21, 4: 10.5, 1: 0 };
var METODOS_PAGO = {
    P: 'Pesos', D: 'Dólares', R: 'Reales',
    T: 'Tarjetas', CC: 'Cuenta Corriente', CH: 'Cheques'
};

// === 2. ESTADO GLOBAL ===
var tipo_de_factura = '';
var venta = {
    items: {}, pagos: {}, items_count: 0, pagos_count: 0, total: 0, values_iva: VALUES_IVA,

    calculaPrecioConIva: function (precio, porc) {
        return precio + ((precio * porc) / 100);
    },

    addPago: function (detalle, moneda, tarjeta_id, monto,montomonori, cuotas, cotizacion) {
        this.pagos[this.pagos_count] = { detalle: detalle, moneda: moneda, tarjeta_id: tarjeta_id, monto: monto, montomonori: montomonori, cuotas : cuotas, cotizacion: cotizacion };
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

    var monto = 0, detalle = '', cuotas = 1, cotizacion = 1,montomonori=0;
    var tarjeta_id = '', banco = '', nro_cheque = '';

    switch (metodo) {
        case 'P':
            monto = parseFloat($('#montoPago').val()) || 0;
            detalle = 'Efectivo';
            break;
        case 'D':
            montomonori = parseFloat($('#montoDolares').val()) || 0;
            cotizacion = parseFloat($('#cotizacionDolares').val()) || window.COTIZACION_DOLAR || 1;
            monto = montomonori * cotizacion;
            detalle = 'Dólares (U$S ' + ($('#montoDolares').val() || 0) + ' x ' + cotizacion + ')';
            break;
        case 'R':
            montomonori = parseFloat($('#montoReales').val()) || 0;
            cotizacion = parseFloat($('#cotizacionReales').val()) || window.COTIZACION_REAL || 1;
            monto = montomonori * cotizacion;
            detalle = 'Reales (R$ ' + ($('#montoReales').val() || 0) + ' x ' + cotizacion + ')';
            break;
        case 'T':
            tarjeta_id = $('#selectTarjeta').val();
            cuotas = parseInt($('#selectedCuotaValue').val()) || 1;
            monto = parseFloat($('#montoTarjeta').val()) || 0;
            if (!tarjeta_id) {
                Swal.fire({ type: 'error', title: 'Error', text: 'Seleccione una tarjeta' });
                return;
            }
            if (!cuotas) {
                Swal.fire({ type: 'error', title: 'Error', text: 'Seleccione cantidad de cuotas' });
                return;
            }
            var tarjetaNombre = tarjeta_id;
            if (window.TARJETAS_LIST) {
                $.each(window.TARJETAS_LIST, function (_, t) {
                    if (t.Tar_Id === tarjeta_id) tarjetaNombre = t.Tar_Descri;
                });
            }
            detalle = tarjetaNombre + ' - ' + cuotas + (cuotas === 1 ? ' cuota' : ' cuotas');
            break;
        case 'CC':
            monto = parseFloat($('#montoCC').val()) || 0;
            detalle = 'Cuenta Corriente';
            break;
        case 'CH':
            banco = $('#bancoCheque').val().trim();
            nro_cheque = $('#nroCheque').val().trim();
            monto = parseFloat($('#montoCheque').val()) || 0;
            if (!banco || !nro_cheque) {
                Swal.fire({ type: 'error', title: 'Error', text: 'Complete banco y número de cheque' });
                return;
            }
            detalle = 'Cheque N°: ' + nro_cheque + ' - ' + banco;
            break;
    }

    if (monto <= 0) {
        Swal.fire({ type: 'error', title: 'Error', text: 'Ingrese un monto válido' });
        return;
    }

    var interesItemKeys = [];

    if (metodo === 'T') {
        var interesFactor = parseFloat($('#selectedCuotaInteres').val()) || 1;
        if (interesFactor > 1) {
            var interesMonto = monto * (interesFactor - 1);
            var descInteres = 'Interés ' + cuotas + (cuotas === 1 ? ' cuota' : ' cuotas') + ' - ' + tarjetaNombre;
            var key = venta.addItem('992', 'VAR', descInteres, 1, 1, interesMonto, 0, interesMonto);
            interesItemKeys.push(key);
            monto = monto + interesMonto;
            $('#tbl-items tbody').append(
                '<tr id="row-' + key + '" style="background:#fff3cd;">' +
                    '<td align="right">1</td>' +
                    '<td>' + descInteres + '</td>' +
                    '<td align="right">' + formatearNumeroConSeparadorDeMiles(interesMonto, 2) + '</td>' +
                    '<td align="right">0 %</td>' +
                    '<td align="right">' + formatearNumeroConSeparadorDeMiles(interesMonto, 2) + '</td>' +
                    '<td align="center"></td>' +
                '</tr>'
            );
        }
    }

    pagosRealizados.push({
        metodo: metodo, detalle: detalle, moneda: metodo, monto: monto, montomonori: montomonori,
        cuotas: cuotas, cotizacion: cotizacion,
        tarjeta_id: tarjeta_id, banco: banco, nro_cheque: nro_cheque,
        interesItemKeys: interesItemKeys
    });
    actualizarTablaPagos();
    actualizarTotales();

    $('#metodoPago').val('P');
    $('#opcionesPago').empty();
    $('#addBtnPago').show();
    $('#metodoPago').trigger('change');
    $('#metodoPago').focus();
}

function eliminarPago(index) {
    var pago = pagosRealizados[index];
    if (pago && pago.interesItemKeys && pago.interesItemKeys.length > 0) {
        $.each(pago.interesItemKeys, function (_, key) {
            venta.deleteItem(key);
            $('#row-' + key).remove();
        });
    }
    pagosRealizados.splice(index, 1);
    actualizarTablaPagos();
    actualizarTotales();
}

function limpiarPagos() {
    if (pagosRealizados.length === 0) return;
    Swal.fire({
        title: '¿Limpiar todos los pagos?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, limpiar'
    }).then(function (result) {
        if (result.value) {
            $.each(pagosRealizados, function (_, pago) {
                if (pago.interesItemKeys && pago.interesItemKeys.length > 0) {
                    $.each(pago.interesItemKeys, function (_, key) {
                        venta.deleteItem(key);
                        $('#row-' + key).remove();
                    });
                }
            });
            pagosRealizados = [];
            actualizarTablaPagos();
            actualizarTotales();
            $('#limpiarPagosBtn').hide();
        }
    });
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
        var cotStr = '';
        if (pago.metodo === 'D' || pago.metodo === 'R') {
            cotStr = formatearNumeroConSeparadorDeMiles(pago.cotizacion, 2);
        } else if (pago.metodo === 'T') {
            cotStr = pago.cuotas + (pago.cuotas === 1 ? ' cuota' : ' cuotas');
        } else {
            cotStr = '-';
        }
        tbody.append(
            '<tr>' +
                '<td>' + obtenerNombreMetodo(pago.metodo) + '</td>' +
                '<td>' + pago.detalle + '</td>' +
                '<td align="right">' + formatearNumeroConSeparadorDeMiles(pago.monto, 2) + '</td>' +
                '<td align="center">' + cotStr + '</td>' +
                '<td align="center"><button type="button" class="btn btn-xs btn-danger" onclick="eliminarPago(' + i + ')"><i class="glyphicon glyphicon-trash"></i></button></td>' +
            '</tr>'
        );
    });
}

function ingresar_pagos() {

    $.each(pagosRealizados, function (_, pago) {
        venta.addPago(pago.detalle, pago.moneda, pago.tarjeta_id, pago.monto, pago.montomonori, pago.cuotas || 1, pago.cotizacion || 1,pago.banco, pago.nro_cheque     );           
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

    var esProducto99 = (id_producto === '99' && id_familia === 'VAR');
    if (esProducto99) {
        var existeOtro = false;
        var yaExiste99 = false;
        $.each(venta.items, function (_, item) {
            if (item.id_producto === '992' && item.id_familia === 'VAR') return true;
            if (item.id_producto === '99' && item.id_familia === 'VAR') {
                yaExiste99 = true;
                return false;
            }
            existeOtro = true;
            return false;
        });
        if (existeOtro) {
            Swal.fire({ type: 'error', title: 'Error', text: 'El producto 99 con familia VAR no puede combinarse con otros productos' });
            return false;
        }
    } else {
        var existeProducto99 = false;
        $.each(venta.items, function (_, item) {
            if (item.id_producto === '992' && item.id_familia === 'VAR') return true;
            if (item.id_producto === '99' && item.id_familia === 'VAR') {
                existeProducto99 = true;
                return false;
            }
        });
        if (existeProducto99) {
            Swal.fire({ type: 'error', title: 'Error', text: 'No puede agregar este producto porque ya existe un producto 99 en la venta' });
            return false;
        }
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
    checkHabilitarPagos();
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
    checkHabilitarPagos();
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

    var sumaPagos = pagosRealizados.reduce(function (acc, p) {
        return acc + p.monto;
    }, 0);
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
        '<div class="alert alert-' + estado + '" style="font-size:14px; font-weight:bold; padding:10px;">' +
            '<table style="width:100%;font-size:14px;">' + lineas + '</table>' +
        '</div>';
    $('#resumenPagosInfo').html(resumen);

    if (pagosRealizados.length > 0) {
        $('#limpiarPagosBtn').show();
    }

    var badge = pagosRealizados.length > 0
        ? '<span class="label label-success" style="font-size:14px;">' + pagosRealizados.length + ' pago(s)</span>'
        : '<span class="label label-default" style="font-size:14px;">Sin pagos</span>';
    $('#resumenPagos').html(badge);

    actualizarMontoMetodoActivo();
}

function actualizarMontoMetodoActivo() {
    var metodo = $('#metodoPago').val();
    var saldo = faltaPagar > 0 ? faltaPagar.toFixed(2) : '0.00';
    switch (metodo) {
        case 'P':
            if ($('#montoPago').length) $('#montoPago').val(saldo);
            break;
        case 'T':
            if ($('#montoTarjeta').length) {
                $('#montoTarjeta').val(saldo);
                actualizarMontoTarjeta();
            }
            break;
        case 'CC':
            if ($('#montoCC').length) $('#montoCC').val(saldo);
            break;
        case 'CH':
            if ($('#montoCheque').length) $('#montoCheque').val(saldo);
            break;
    }
}

function actualizarMontoTarjeta() {
    var $montoInput = $('#montoTarjeta');
    var $interesCol = $('#colInteresTarjeta');
    var $interesInput = $('#interesTarjeta');
    if (!$montoInput.length) return;
    var interes = parseFloat($('#selectedCuotaInteres').val()) || 1;
    var montoBase = parseFloat($montoInput.val()) || 0;
    if (interes > 1 && montoBase > 0) {
        var interesMonto = montoBase * (interes - 1);
        $interesInput.val(formatearNumeroConSeparadorDeMiles(interesMonto, 2));
        $interesCol.show();
    } else {
        $interesInput.val('');
        $interesCol.hide();
    }
}

function togglePagosPanel(habilitar) {
    $('#metodoPago').prop('disabled', !habilitar);
    $('#addBtnPago').prop('disabled', !habilitar);
    if (habilitar) {
        if ($('#pagosDisabledMsg').length) {
            $('#pagosDisabledMsg').remove();
            $('#metodoPago').trigger('change');
        }
    } else if (!$('#pagosDisabledMsg').length) {
        $('#opcionesPago').html(
            '<div id="pagosDisabledMsg" class="alert alert-info" style="margin-bottom:0;padding:8px;">' +
                '<i class="fa fa-info-circle"></i> Agregue productos para habilitar las formas de pago' +
            '</div>'
        );
    }
}

function checkHabilitarPagos() {
    var tieneProductoHabilitado = false;
    $.each(venta.items, function (_, item) {
        if (item.id_producto === '992' && item.id_familia === 'VAR') return true;
        if (item.id_producto === '99' && item.id_familia === 'VAR') return true;
        tieneProductoHabilitado = true;
        return false;
    });
    togglePagosPanel(tieneProductoHabilitado);
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

    var Existe99 = false;
    $.each(venta.items, function (_, item) {
        if (item.id_producto === '99' && item.id_familia === 'VAR') {
            Existe99 = true;
            return false;
        }
    });

    if (operacion === 'Vta' && !Existe99  ) {
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

        var sumaPagos = pagosRealizados.reduce(function (acc, p) {
            return acc + p.monto;
        }, 0);
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
    $('#datos_cliente, #modif-cliente-btn, #limpiarPagosBtn').hide();
    $('#tbl-items tbody').empty();
    $('#tbl-pagos tbody').empty();
    $('#tbl-pagos').hide();
    $('#opcionesPago, #resumenPagosInfo, #resumenPagos').empty();
    $('#addBtnPago').show();
    $('#VentaButton, #PresuButton').prop('disabled', false);
    $('#VentaButton').html('COBRAR');
    $('#PresuButton').html('PRESUPUESTAR');

    $('#metodoPago').val('P').trigger('change');
    togglePagosPanel(false);
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
    $('#datos_cliente, #modif-cliente-btn, #tbl-pagos, #limpiarPagosBtn').hide();

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
    $('#opcionesPago').on('keyup', 'input, select', function (e) {
        if (e.which === 13) agregarMetodoPago();
    });

    // --- Cargar cuotas al seleccionar tarjeta ---
    $('#opcionesPago').on('change', '#selectTarjeta', function () {
        var tarjetaId = this.value;
        var $cuotasCol = $('#colCuotasTarjeta');
        var $interesCol = $('#colInteresTarjeta');
        var $container = $('#cuotasTableContainer');
        if (!tarjetaId) {
            $cuotasCol.hide();
            $interesCol.hide();
            $('#selectedCuotaValue').val('');
            $('#selectedCuotaInteres').val('1');
            return;
        }
        var montoBase = parseFloat($('#montoTarjeta').val()) || 0;
        $cuotasCol.show();
        $container.html('<div style="padding:8px;color:#999;">Cargando...</div>');
        $.ajax({
            dataType: 'json',
            type: 'get',
            url: 'cuotas_tarjeta?tarjeta_id=' + tarjetaId,
            success: function (data) {
                if (data.length === 0) {
                    $cuotasCol.hide();
                    $interesCol.hide();
                    $('#selectedCuotaValue').val('');
                    $('#selectedCuotaInteres').val('1');
                    return;
                }
                var html = '<table class="table table-condensed table-hover" style="margin-bottom:0;font-size:12px;">' +
                            '<thead><tr>' +
                                '<th>Cuotas</th>' +
                                '<th class="text-right">Monto c/int</th>' +
                                '<th class="text-right">c/cuota</th>' +
                                '<th class="text-right">%</th>' +
                            '</tr></thead><tbody>';
                $.each(data, function (_, row) {
                    var interes = ((row.TarCuo_Interes - 1) * 100).toFixed(2);
                    if (montoBase > 0) {
                        var montoConInt = montoBase * (row.TarCuo_Interes );
                    }
                    html += '<tr class="cuota-row" data-cuota="' + row.TarCuo_Cuota + '" data-interes="' + row.TarCuo_Interes + '" style="cursor:pointer;">' +
                            '<td>' + row.TarCuo_Detalle + '</td>' +
                            '<td class="text-right">$' + formatearNumeroConSeparadorDeMiles(montoConInt, 2) + '</td>' +
                            '<td class="text-right">$' + formatearNumeroConSeparadorDeMiles(montoConInt / row.TarCuo_CuotaReal, 2) + '</td>' +
                            '<td class="text-right">' + interes + '%</td>' +
                            '</tr>';
                });
                html += '</tbody></table>';
                $container.html(html);
            },
            error: function () {
                $container.html('<div style="padding:8px;color:#a00;">Error al cargar cuotas</div>');
            }
        });
    });

    // --- Actualizar interés al cambiar monto base ---
    $('#opcionesPago').on('input', '#montoTarjeta', function () {
        actualizarMontoTarjeta();
    });

    // --- Seleccionar cuota desde la tabla ---
    $('#opcionesPago').on('click', '.cuota-row', function () {
        $('#cuotasTableContainer .cuota-row').removeClass('active');
        $(this).addClass('active');
        $('#selectedCuotaValue').val($(this).data('cuota'));
        $('#selectedCuotaInteres').val($(this).data('interes'));
        actualizarMontoTarjeta();
    });

    // --- Actualizar equivalente en pesos para Dólares ---
    $('#opcionesPago').on('input', '#montoDolares', function () {
        var monto = parseFloat($('#montoDolares').val()) || 0;
        var cotiz = parseFloat($('#cotizacionDolares').val()) || 1;
        $('#equivalenteDolares').val(formatearNumeroConSeparadorDeMiles(monto * cotiz, 2));
    });

    $('#opcionesPago').on('input', '#cotizacionDolares', function () {
        var equivaleteVal = parseFloat(numberFormatBd($('#equivalenteDolares').val())) || faltaPagar || 0;
        var cotiz = parseFloat($(this).val()) || 1;
        var foreignAmount = equivaleteVal > 0 ? equivaleteVal / cotiz : 0;
        $('#montoDolares').val(foreignAmount.toFixed(2));
        $('#equivalenteDolares').val(formatearNumeroConSeparadorDeMiles(equivaleteVal, 2));
    });

    // --- Actualizar equivalente en pesos para Reales ---
    $('#opcionesPago').on('input', '#montoReales', function () {
        var monto = parseFloat($('#montoReales').val()) || 0;
        var cotiz = parseFloat($('#cotizacionReales').val()) || 1;
        $('#equivalenteReales').val(formatearNumeroConSeparadorDeMiles(monto * cotiz, 2));
    });

    $('#opcionesPago').on('input', '#cotizacionReales', function () {
        var equivaleteVal = parseFloat(numberFormatBd($('#equivalenteReales').val())) || faltaPagar || 0;
        var cotiz = parseFloat($(this).val()) || 1;
        var foreignAmount = equivaleteVal > 0 ? equivaleteVal / cotiz : 0;
        $('#montoReales').val(foreignAmount.toFixed(2));
        $('#equivalenteReales').val(formatearNumeroConSeparadorDeMiles(equivaleteVal, 2));
    });

    // --- Cambio de método de pago ---
    $('#metodoPago').change(function () {
        var metodo = this.value;
        var opciones = $('#opcionesPago').empty();
        $('#addBtnPago').show();
        var html = '';
        var saldo = faltaPagar > 0 ? faltaPagar.toFixed(2) : '0.00';

        switch (metodo) {
            case 'P':
                html = '<div class="input-group">' +
                            '<span class="input-group-addon">$</span>' +
                            '<input class="form-control text-right" type="number" id="montoPago" step="0.01" value="' + saldo + '">' +
                       '</div>';
                break;

            case 'D':
                var cotizacionD = window.COTIZACION_DOLAR || 1;
                var montoInicialD = saldo > 0 ? (saldo / cotizacionD).toFixed(2) : '0.00';
                html = '<div class="row" style="margin-left:0;margin-right:0">' +
                            '<div class="col-xs-4" style="margin-left:0;margin-right:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">Cotiz.</span>' +
                                    '<input class="form-control text-right" type="number" id="cotizacionDolares" step="0.01" value="' + cotizacionD + '">' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-xs-4" style="padding-left:0;margin-right:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">U$S</span>' +
                                    '<input class="form-control text-right" type="number" id="montoDolares" step="0.01" value="' + montoInicialD + '">' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-xs-4" style="margin-left:0;margin-right:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">= $</span>' +
                                    '<input class="form-control text-right" type="text" id="equivalenteDolares" readonly style="font-weight:bold;background:#f5f5f5;">' +
                                '</div>' +
                            '</div>' +
                       '</div>';
                break;

            case 'R':
                var cotizacionR = window.COTIZACION_REAL || 1;
                var montoInicialR = saldo > 0 ? (saldo / cotizacionR).toFixed(2) : '0.00';
                html = '<div class="row" style="margin-left:0;margin-right:0">' +
                            '<div class="col-xs-4" style="padding-left:0;margin-right:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">Cotiz.</span>' +
                                    '<input class="form-control text-right" type="number" id="cotizacionReales" step="0.01" value="' + cotizacionR + '">' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-xs-4" style="padding-left:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">R$</span>' +
                                    '<input class="form-control text-right" type="number" id="montoReales" step="0.01" value="' + montoInicialR + '">' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-xs-4" style="margin-left:0;margin-right:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">= $</span>' +
                                    '<input class="form-control text-right" type="text" id="equivalenteReales" readonly style="font-weight:bold;background:#f5f5f5;">' +
                                '</div>' +
                            '</div>' +
                       '</div>';
                break;

            case 'T':
                var tarjetasHtml = '<option value="">Seleccione tarjeta</option>';
                if (window.TARJETAS_LIST && window.TARJETAS_LIST.length > 0) {
                    $.each(window.TARJETAS_LIST, function (_, t) {
                        tarjetasHtml += '<option value="' + t.Tar_Id + '">' + t.Tar_Descri + '</option>';
                    });
                }
                html = '<div class="row" style="margin-left:0;margin-right:0;padding-bottom:4px;">' +
                            '<div class="col-xs-7" style="padding:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">$</span>' +
                                    '<input class="form-control text-right" type="number" id="montoTarjeta" step="0.01" value="' + saldo + '">' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-xs-5" style="padding-right:0">' +
                                '<select class="form-control" id="selectTarjeta">' + tarjetasHtml + '</select>' +
                            '</div>' +
                       '</div>' +
                       '<div class="row" style="margin-left:0;margin-right:0">' +
                            '<div class="col-xs-8" style="padding:0;display:none;" id="colCuotasTarjeta">' +
                                '<div id="cuotasTableContainer" style="max-height:180px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;"></div>' +
                                '<input type="hidden" id="selectedCuotaValue" value="">' +
                                '<input type="hidden" id="selectedCuotaInteres" value="1">' +
                            '</div>' +
                            '<div class="col-xs-4" style="padding-right:0;padding-left:0;display:none;" id="colInteresTarjeta">' +
                                '<div class="input-group" id="interesTarjetaGroup">' +
                                    '<span class="input-group-addon">Interés $</span>' +
                                    '<input class="form-control text-right" type="text" id="interesTarjeta" readonly style="font-weight:bold;background:#f5f5f5;">' +
                                '</div>' +
                            '</div>' +
                       '</div>';
                break;

            case 'CC':
                html = '<div class="input-group">' +
                            '<span class="input-group-addon">$</span>' +
                            '<input class="form-control text-right" type="number" id="montoCC" step="0.01" value="' + saldo + '">' +
                       '</div>';
                break;

            case 'CH':
                html = '<div class="row" style="margin-left:0;margin-right:0">' +
                            '<div class="col-xs-4" style="padding-left:0;margin-right:0">' +
                                '<input class="form-control" type="text" id="bancoCheque" placeholder="Banco">' +
                            '</div>' +
                            '<div class="col-xs-3" style="padding-left:0;margin-right:0">' +
                                '<input class="form-control" type="text" id="nroCheque" placeholder="N° Cheque">' +
                            '</div>' +
                            '<div class="col-xs-5" style="padding-right:0">' +
                                '<div class="input-group">' +
                                    '<span class="input-group-addon">$</span>' +
                                    '<input class="form-control text-right" type="number" id="montoCheque" step="0.01" value="' + saldo + '">' +
                                '</div>' +
                            '</div>' +
                       '</div>';
                break;
        }

        opciones.html(html);

        if (metodo === 'D') {
            $('#montoDolares').trigger('input').focus();
        } else if (metodo === 'R') {
            $('#montoReales').trigger('input').focus();
        }
    });

    // === 11. BOOTSTRAP ===
    inicializar();
    valida_estado_servidor();
});
