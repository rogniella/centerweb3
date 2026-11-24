@extends('template.main')

@section('titulo', 'Consulta Tipo Cristales')

@section('contenido')
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Consulta Tipo Cristales</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            @forelse ($categorias as $categoria)
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="nivel_{{ $categoria['indice'] }}">{{ $categoria['descripcion'] }}</label>
                        <select class="form-control select-categoria" id="nivel_{{ $categoria['indice'] }}" data-nivel="{{ $categoria['indice'] }}">
                            @foreach ($categoria['opciones'] as $opcion)
                                @if ($categoria['indice'] === 0 && strtoupper($opcion['codigo']) === 'L')
                                    @continue
                                @endif
                                <option value="{{ $opcion['codigo'] }}">{{ $opcion['descripcion'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @empty
                <div class="col-xs-12">
                    <div class="alert alert-warning">No hay categorías configuradas para la familia LEN.</div>
                </div>
            @endforelse
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="form-group">
                    <label for="graduacion_esf">Esferico</label>
                    <input type="number" step="0.25" class="form-control" id="graduacion_esf" name="esf" placeholder="Ej. -3.25">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="form-group">
                    <label for="graduacion_cil">Cilindro</label>
                    <input type="number" step="0.25" class="form-control" id="graduacion_cil" name="cil" placeholder="Ej. -1.25">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-right">
                <button type="button" class="btn btn-primary" id="consultar">Consultar</button>
                <button type="button" class="btn btn-default" id="limpiar">Limpiar</button>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">Resultados</h4>
    </div>
    <div class="panel-body">
        <table id="tabla_tipos_cristales"
            data-toggle="table"
            data-search="true"
            data-show-export="true"
            data-cache="false"
            class="table table-striped">
            <thead>
                <tr>
                    <th data-field="codigo" data-sortable="true">Código</th>
                    <th data-field="descripcion" data-sortable="true">Descripción</th>
                    <th data-field="precio" data-sortable="true" data-align="right" data-formatter="formatterPrecio" data-sorter="sorterPrecio">Precio</th>
                    <th data-field="rangos" data-sortable="false" data-formatter="formatterRangos">Rangos</th>
                    @if (Auth::check() && Auth::user()->perfil_id === 'ADM')
                        <th data-field="idweb" data-sortable="false" data-align="center" data-formatter="formatterAccionesRangos">Acciones</th>
                    @endif
                </tr>
            </thead>
        </table>
    </div>
</div>

@if (Auth::check() && Auth::user()->perfil_id === 'ADM')
    @include('cristales.rangos_modal')
@endif
@endsection

@section('scrip')
<script>
    var urlCategoriasTipoCristal = '{{ route('cristales.consultatipo.categorias') }}';
    var urlBuscarTipoCristal = '{{ route('cristales.consultatipo.buscar') }}';
    var categoriasTipoCristal = @json($categorias, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    function mensajeErrorValidacionTipoCristal(xhr, contexto) {
        var respuesta = xhr.responseJSON;
        var etiquetas = {
            esf: 'Esferico',
            cil: 'Cilindro',
            descripcion: 'Descripción',
            niveles: 'Niveles'
        };
        var mensajes = [];

        if (respuesta && respuesta.errors && typeof respuesta.errors === 'object') {
            Object.keys(respuesta.errors).forEach(function (campo) {
                var errores = respuesta.errors[campo];

                if (!Array.isArray(errores) || errores.length === 0) {
                    return;
                }

                var etiqueta = etiquetas[campo] || campo;
                errores.forEach(function (mensaje) {
                    mensajes.push(etiqueta + ': ' + mensaje);
                });
            });
        }

        if (mensajes.length > 0) {
            return contexto + ':<br>' + mensajes.join('<br>');
        }

        if (respuesta && respuesta.message) {
            return contexto + ': ' + respuesta.message;
        }

        return contexto + ': ' + xhr.status;
    }

    var formateadorPrecioTipoCristal = new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function valorNumericoPrecioTipoCristal(valor) {
        if (typeof valor === 'number') {
            return valor;
        }

        return parseFloat(String(valor).replace(/\./g, '').replace(',', '.'));
    }

    function formatterPrecio(value) {
        if (value === null || value === undefined || value === '') {
            return '';
        }

        return formateadorPrecioTipoCristal.format(parseFloat(value));
    }

    function sorterPrecio(a, b) {
        return valorNumericoPrecioTipoCristal(a) - valorNumericoPrecioTipoCristal(b);
    }

    function formatterRangos(value) {
        if (!Array.isArray(value) || value.length === 0) {
            return '';
        }

        var $contenedor = $('<div/>');
        $.each(value, function (_, rango) {
            $('<div/>').text(rango).appendTo($contenedor);
        });

        return $contenedor.html();
    }

    @if (Auth::check() && Auth::user()->perfil_id === 'ADM')
    var urlRangosTipoCristal = '{{ route('cristales.rangos.index', ['producto' => '__PRODUCTO__']) }}';
    var urlGuardarRangoTipoCristal = '{{ route('cristales.rangos.store', ['producto' => '__PRODUCTO__']) }}';
    var urlActualizarRangoTipoCristal = '{{ route('cristales.rangos.update', ['producto' => '__PRODUCTO__', 'rango' => '__RANGO__']) }}';
    var urlEliminarRangoTipoCristal = '{{ route('cristales.rangos.destroy', ['producto' => '__PRODUCTO__', 'rango' => '__RANGO__']) }}';
    var tokenRangosTipoCristal = '{{ csrf_token() }}';
    var productoRangosSeleccionado = null;

    function urlConProductoTipoCristal(url, codigo) {
        return url.replace('__PRODUCTO__', encodeURIComponent(codigo));
    }

    function urlConRangoTipoCristal(url, codigo, id) {
        return url
            .replace('__PRODUCTO__', encodeURIComponent(codigo))
            .replace('__RANGO__', encodeURIComponent(id));
    }

    function formatterAccionesRangos(value, row) {
        var $boton = $('<button/>', {
            type: 'button',
            'class': 'btn btn-primary btn-xs btn-administrar-rangos',
            title: 'Administrar rangos'
        })
            .append($('<i/>', { 'class': 'fa fa-adjust' }))
            .append(' Rangos')
            .attr('data-codigo', row.codigo)
            .attr('data-descripcion', row.descripcion);

        return $('<div/>').append($boton).html();
    }

    function limpiarMensajeRangosTipoCristal() {
        $('#mensajeRangosCristal').hide().empty();
    }

    function mostrarErrorRangosTipoCristal(xhr) {
        $('#mensajeRangosCristal')
            .html(mensajeErrorValidacionTipoCristal(xhr, 'No se pudo completar la operación'))
            .show();
    }

    function limpiarFormularioRangoTipoCristal() {
        $('#formRangoCristal')[0].reset();
        $('#rangoCristalId').val('');
        limpiarMensajeRangosTipoCristal();
    }

    function textoCeldaRangoTipoCristal(valor) {
        if (valor === null || valor === undefined || valor === '') {
            return '';
        }

        return valor;
    }

    function llenarTablaRangosTipoCristal(rangos) {
        var $cuerpo = $('#cuerpoRangosCristal').empty();

        if (!Array.isArray(rangos) || rangos.length === 0) {
            $('<tr/>').append(
                $('<td/>', { colspan: 9, class: 'text-center text-muted' }).text('Sin rangos cargados.')
            ).appendTo($cuerpo);

            return;
        }

        $.each(rangos, function (_, rango) {
            var $fila = $('<tr/>');

            [
                rango.Cri_rango_id,
                rango.Cri_esf,
                rango.Cri_esf_neg,
                rango.Cri_cil,
                rango.Cri_cil_neg,
                rango.Cri_add,
                rango.Cri_rango,
                rango.Cri_metodo
            ].forEach(function (valor) {
                $('<td/>').text(textoCeldaRangoTipoCristal(valor)).appendTo($fila);
            });

            var $editar = $('<button/>', {
                type: 'button',
                'class': 'btn btn-xs btn-default btn-editar-rango',
                title: 'Editar rango'
            })
                .append($('<i/>', { 'class': 'fa fa-pencil' }))
                .append(' Editar')
                .data('rango', rango);

            var $eliminar = $('<button/>', {
                type: 'button',
                'class': 'btn btn-xs btn-danger btn-eliminar-rango',
                title: 'Eliminar rango'
            })
                .append($('<i/>', { 'class': 'fa fa-trash' }))
                .append(' Eliminar')
                .data('id', rango.Cri_rango_id);

            $('<td/>', { class: 'text-right' })
                .append($editar)
                .append(' ')
                .append($eliminar)
                .appendTo($fila);
            $cuerpo.append($fila);
        });
    }

    function cargarFormularioRangoTipoCristal(rango) {
        $('#rangoCristalId').val(rango.Cri_rango_id);
        $('#Cri_esf').val(rango.Cri_esf);
        $('#Cri_esf_neg').val(rango.Cri_esf_neg);
        $('#Cri_cil').val(rango.Cri_cil);
        $('#Cri_cil_neg').val(rango.Cri_cil_neg);
        $('#Cri_add').val(rango.Cri_add);
        $('#Cri_rango').val(rango.Cri_rango);
        $('#Cri_metodo').val(rango.Cri_metodo);
        limpiarMensajeRangosTipoCristal();
        $('#Cri_esf').focus();
    }

    function abrirModalRangosTipoCristal(codigo, descripcion) {
        productoRangosSeleccionado = { codigo: codigo, descripcion: descripcion };

        $('#rangosProductoCodigo').text(codigo);
        $('#rangosProductoDescripcion').text(descripcion);
        limpiarFormularioRangoTipoCristal();

        $.ajax({
            global: false,
            dataType: 'json',
            url: urlConProductoTipoCristal(urlRangosTipoCristal, codigo),
            type: 'get'
        }).done(function (rangos) {
            llenarTablaRangosTipoCristal(rangos);
            $('#modalRangosCristal').modal('show');
        }).fail(function (xhr) {
            msgerror(mensajeErrorValidacionTipoCristal(xhr, 'No se pudieron cargar los rangos'));
        });
    }

    function datosFormularioRangoTipoCristal() {
        return {
            Cri_esf: $('#Cri_esf').val(),
            Cri_esf_neg: $('#Cri_esf_neg').val(),
            Cri_cil: $('#Cri_cil').val(),
            Cri_cil_neg: $('#Cri_cil_neg').val(),
            Cri_add: $('#Cri_add').val(),
            Cri_rango: $('#Cri_rango').val(),
            Cri_metodo: $('#Cri_metodo').val()
        };
    }

    function finalizarOperacionRangosTipoCristal() {
        $('#modalRangosCristal').modal('hide');
        consultarTipoCristal();
    }

    function guardarRangoTipoCristal() {
        if (!productoRangosSeleccionado) {
            return;
        }

        limpiarMensajeRangosTipoCristal();

        var id = $('#rangoCristalId').val();
        var esActualizacion = id !== '';
        var url = esActualizacion
            ? urlConRangoTipoCristal(urlActualizarRangoTipoCristal, productoRangosSeleccionado.codigo, id)
            : urlConProductoTipoCristal(urlGuardarRangoTipoCristal, productoRangosSeleccionado.codigo);

        $.ajax({
            global: false,
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': tokenRangosTipoCristal },
            data: datosFormularioRangoTipoCristal(),
            url: url,
            type: esActualizacion ? 'put' : 'post'
        }).done(function () {
            finalizarOperacionRangosTipoCristal();
        }).fail(function (xhr) {
            mostrarErrorRangosTipoCristal(xhr);
        });
    }

    function eliminarRangoTipoCristal(id) {
        if (!productoRangosSeleccionado || id === '' || id === undefined) {
            return;
        }

        if (!confirm('¿Eliminar el rango seleccionado?')) {
            return;
        }

        limpiarMensajeRangosTipoCristal();

        $.ajax({
            global: false,
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': tokenRangosTipoCristal },
            url: urlConRangoTipoCristal(urlEliminarRangoTipoCristal, productoRangosSeleccionado.codigo, id),
            type: 'delete'
        }).done(function () {
            finalizarOperacionRangosTipoCristal();
        }).fail(function (xhr) {
            mostrarErrorRangosTipoCristal(xhr);
        });
    }
    @endif

    function seleccionNivelesTipoCristal() {
        var seleccion = {};

        $('.select-categoria').each(function () {
            seleccion[$(this).data('nivel')] = $(this).val();
        });

        return seleccion;
    }

    function llenarComboTipoCristal(nivel) {
        var $combo = $('#nivel_' + nivel.indice);

        $combo.empty();
        $.each(nivel.opciones, function (_, opcion) {
            $('<option>', { value: opcion.codigo, text: opcion.descripcion }).appendTo($combo);
        });
        $combo.prop('disabled', false);
    }

    function recargarNivelesTipoCristal(desde) {
        var siguiente = desde + 1;

        if (siguiente >= categoriasTipoCristal.length) {
            return;
        }

        $('.select-categoria').each(function () {
            if (parseInt($(this).data('nivel')) > desde) {
                $(this)
                    .empty()
                    .append($('<option>', { value: '00', text: '[Todos]' }))
                    .prop('disabled', true);
            }
        });

        $.ajax({
            global: false,
            dataType: 'json',
            data: { nivel: siguiente, niveles: seleccionNivelesTipoCristal() },
            url: urlCategoriasTipoCristal,
            type: 'get'
        }).done(function (nivel) {
            if (!nivel) {
                return;
            }

            llenarComboTipoCristal(nivel);
            recargarNivelesTipoCristal(siguiente);
        }).fail(function (xhr) {
            msgerror(mensajeErrorValidacionTipoCristal(xhr, 'No se pudieron cargar las opciones'));
        });
    }

    function consultarTipoCristal() {
        $.ajax({
            dataType: 'json',
            data: {
                niveles: seleccionNivelesTipoCristal(),
                esf: $('#graduacion_esf').val(),
                cil: $('#graduacion_cil').val()
            },
            url: urlBuscarTipoCristal,
            type: 'get'
        }).done(function (data) {
            $('#tabla_tipos_cristales').bootstrapTable('load', data);
        }).fail(function (xhr) {
            msgerror(mensajeErrorValidacionTipoCristal(xhr, 'No se pudo realizar la consulta'));
        });
    }

    $(document).ready(function () {
        $('.select-categoria').on('change', function () {
            recargarNivelesTipoCristal(parseInt($(this).data('nivel')));
        });

        $('#consultar').on('click', consultarTipoCristal);

        $('#limpiar').on('click', function () {
            $('.select-categoria').each(function () {
                $(this).val('00').prop('disabled', false);
            });

            $('#graduacion_esf, #graduacion_cil').val('');

            $('#tabla_tipos_cristales').bootstrapTable('load', []);
        });

        @if (Auth::check() && Auth::user()->perfil_id === 'ADM')
        $(document).on('click', '.btn-administrar-rangos', function () {
            abrirModalRangosTipoCristal($(this).data('codigo'), $(this).data('descripcion'));
        });

        $(document).on('click', '.btn-editar-rango', function () {
            cargarFormularioRangoTipoCristal($(this).data('rango'));
        });

        $(document).on('click', '.btn-eliminar-rango', function () {
            eliminarRangoTipoCristal($(this).data('id'));
        });

        $('#btnNuevoRangoCristal').on('click', limpiarFormularioRangoTipoCristal);
        $('#btnGuardarRangoCristal').on('click', guardarRangoTipoCristal);
        @endif
    });
</script>
@endsection
