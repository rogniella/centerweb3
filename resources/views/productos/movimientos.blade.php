@extends('template.informes')
@section('titulo','Movimientos de Productos')
   
@section('contenido')
    
<form  role="form" >
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-exchange"></i> Consulta de Movimientos de Productos</h3>
        </div>
        <div class="panel-body">


            <x-accordion id="productosAccordion">
              <x-accordion-item id="filtros" title=" Más Opciones ..."  :expanded="false" icon="search" parent="productosAccordion" panelClass="filtros" headingClass="panel-heading--mas-opciones">


                <div class="row">
                    <div class="col-sm-3 col-xs-12">
                        <label class="control-label">Artículo:</label>
                        <input class="form-control" type="text" value="{{ $id_producto }}" id="id_producto" placeholder="Código de artículo">
                    </div>
                    <div class="col-sm-5 col-xs-12">
                        <label class="control-label">Descripción:</label>
                        <input class="form-control" type="text" value="{{ $desc_producto }}" id="desc_producto" placeholder="Buscar por descripción">
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <label class="control-label">Ignore Cod.Cero:</label>
                        <select name="cod_cero" id="cod_cero" class="form-control">
                            <option value="">NO</option>
                            <option value="S">SI</option>
                        </select>
                    </div>
                </div>
              </x-accordion-item>
            </x-accordion>


            <div class="row">
                <div class="col-sm-3 col-xs-12">
                    <label class="control-label">Rubro:</label>
                    <select name="filtro_flia" id="filtro_flia" class="form-control">
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == $familia ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <label class="control-label">Sucursal:</label>
                    <select name="Sucursal" id="Sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <label class="control-label">Fechas:</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-default btn-block daterange-btn" id="daterange-btn">
                            <i class="fa fa-calendar"></i>
                            <span>{{ $fecha }} al {{ $fecha_fin }}</span>
                            <i class="fa fa-caret-down" style="margin-left: 4px;"></i>
                        </button>
                    </div>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <label class="control-label">Operación:</label>
                    <select name="tipo_operacion" id="tipo_operacion" class="form-control">
                        <option value="">[Todas]</option>
                        <option value="V">Ventas</option>
                        <option value="C">Compras</option>
                    </select>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <label class="control-label">&nbsp;</label>
                    <button type="button" onClick="consultar()" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Consultar</button>
                </div>
            </div>
        </div>
        </div>

    <!-- Panel De la Tabla -->
    <div class="panel panel-success">
        <div class="panel-body">
            <table id="mitabla"
                data-toggle="table"
                data-search="true"
                data-show-export="true"
                data-show-print="true"
                data-cache="false"
                data-pagination="true"
                data-page-size="50"
                data-page-list="[25, 50, 100, 200]"
                class="table table-striped table-hover"
            >
            <thead>
            <tr>
                <th data-field="Mov_Sucursal" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
                <th data-field="Mov_FecMov" data-sortable="true" data-halign="center">Fecha-Hora</th>
                <th data-field="Mov_Familia" data-halign="center" data-align="center" data-sortable="true">Familia</th>
                <th data-field="Mov_IdProd" data-halign="center" data-align="center" data-sortable="true">Producto</th>
                <th data-field="marca" data-sortable="true">Marca</th>
                <th data-field="prod_descripcion" data-halign="center" data-sortable="true">Descripción</th>
                <th data-field="Mov_Operacion" data-halign="center" data-align="center" data-sortable="true" data-formatter="formatoOperacion">Operación</th>
                <th data-field="Mov_Cantidad" data-halign="center" data-align="right" data-sortable="true">Cantidad</th>
                <th data-field="Mov_PrecioUnitario" data-halign="center" data-align="right" data-sortable="true" data-formatter="formatoMoneda">Precio Unit.</th>
                <th data-field="Mov_Precio" data-halign="center" data-align="right" data-sortable="true" data-formatter="formatoMoneda">Total</th>
                <th data-field="Mov_TipoOT" data-align="center" data-sortable="true" data-formatter="formatoTipoOT">Tipo</th>
                <th data-field="Mov_IdOT" data-formatter="fotmatoColSel" data-halign="center" data-align="center" data-sortable="true">OT Nro</th>
                <th data-field="Mov_Motivo" data-sortable="true">Observación</th>
                <th data-field="Mov_Responsable" data-sortable="true" data-align="center">Vendedor</th>
            </tr>
            </thead>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Totalizados por:
                    <select name="groupby" id="groupby" class="form-control input-sm" style="width:auto;display:inline-block;">
                        <option value="">Agrupar por..</option>
                        <option value="Mov_Sucursal">Sucursal</option>
                        <option value="Mov_Operacion">Tipo Operacion</option>
                        <option value="marca">Marca</option>
                        <option value="Mov_Familia">Familia</option>
                        <option value="prod_descripcion">Producto</option>
                        <option value="Mov_Responsable">Vendedor</option>
                    </select>
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table id="tabla_total"
                        data-toggle="table"
                        data-cache="false"
                        data-page-list="false"
                        class="table table-striped table-hover"
                    >
                    <thead>
                    <tr>
                        <th class="active" data-field="label" data-halign="center" data-align="left" data-sortable="true">Descripción</th>
                        <th data-field="cantidad" data-sortable="true" data-halign="center" data-align="right">Cantidad</th>
                        <th data-field="mtos" data-sortable="true" data-halign="center" data-align="right" data-formatter="formatoMoneda">Importes</th>
                        <th data-field="valueCantidad" data-sortable="true" data-halign="center" data-align="right">% Cant</th>
                        <th data-field="value" data-sortable="true" data-halign="center" data-align="right">% Montos</th>
                    </tr>
                    </thead>
                    </table>
                </div>
                <div class="col-md-6">
                    <div id="morris-area-chart"></div>
                </div>
            </div>
        </div>
    </div>
   </div> <!-- Fin col -->
  </div>   <!-- /.Row -->
</form> 

@include('common.modal_consulta')

@endsection <!-- Fin Contenido -->

@section('scrip')


<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>

    var $fecha;
    var $fechafin;
    var $table;

    var DONUT_COLORS = ['#00a65a', '#f39c12', '#dd4b39', '#3c8dbc', '#605ca8', '#00c0ef', '#f012be', '#39cccc', '#ff851b', '#001f3f'];


    function formatoOperacion(value, row, index) {
        if (value == 'V') return '<span class="label label-success label-operacion">Venta</span>';
        if (value == 'C') return '<span class="label label-primary label-operacion">Compra</span>';
        if (value == 'R') return '<span class="label label-danger label-operacion">Anulación</span>';
        return '<span class="label label-default label-operacion">' + (value || '-') + '</span>';
    }

    function formatoTipoOT(value, row, index) {
        if (!value) return '<span class="text-muted">-</span>';
        var map = { 'OT': 'label-info', 'ND': 'label-warning', 'NC': 'label-danger', 'AJ': 'label-default' };
        var cls = map[value] || 'label-default';
        return '<span class="label ' + cls + '">' + value + '</span>';
    }

    $(function () {
        $table = $('#mitabla');

        $fecha = '{{ $fecha }}';
        $fechafin = '{{ $fecha_fin }}';

        $('#tipo_operacion').val('{{ $operacion }}');
        $('#cod_cero').val('{{ $cod_cero }}');

        // Todos los Eventos de la Tabla
        $table.on('all.bs.table', function (e, name, args) {
            if (name == 'click-cell.bs.table') {
                if (args[0] == 'Mov_IdOT') {
                    consulta_comprobante(args[2].Mov_TipoOT, args[2].Mov_IdOT, args[2].Mov_Sucursal);
                }
            }
            if (name == 'load-success.bs.table') {
                var count = $table.bootstrapTable('getData').length;
                $('#total-registros').text(count + ' registro' + (count !== 1 ? 's' : ''));
            }
        });

        // Si cambia el cmb de Agrupacion (tabla principal)
        $('select[name="groupby"]').change(function () {
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            generarTotales($(this).val());
        });

        // Si cambia el cmb de Agrupacion (tabla totales)
        $('select[name="dropdown"]').change(function () {
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            generarTotales($(this).val());
        });

        // Si Cambia el cmb de Tipo de Ot
        $('select[name="estado"]').change(function () {
            consultar();
        });

        $('#daterange-btn').daterangepicker(
            {
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment($fecha, 'YYYY-M-D'),
                endDate: moment($fechafin, 'YYYY-M-D'),
                locale: { format: 'D MMMM YYYY' }
            },
            function (start, end) {
                $('#daterange-btn span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D MMMM YYYY'));
                $fecha = start.format('YYYY-M-D');
                $fechafin = end.format('YYYY-M-D');
                consultar();
            }
        );

        consultar();
    });

    function consultar() {
        $tipo_operacion = $('#tipo_operacion').val();
        $suc = $('#Sucursal').val();


        $.ajax({
            dataType: "json",
            data: { filtro_tipo_operacion: $tipo_operacion, filtro_sucursal: $suc, filtro_fecini: $fecha, filtro_fecfin: $fechafin, filtro_familia: $('#filtro_flia').val(),
                filtro_idprod: $('#id_producto').val(),
                filtro_cero: $('#cod_cero').val(),
                filtro_descripcion: $('#desc_producto').val() },
            url: 'buscar_movimientos',
            type: 'get',
            success: function (data) {
                $table.bootstrapTable('load', data.results);
                generarTotales('Tipo');
            },
            error: function (xhr, err) {
                if (xhr.readyState == 401) {
                    msgerror("Se desconecto. Vuelva a Ingresar su Usuario");
                } else {
                    msgerror(xhr.responseText, err);
                }
            }
        });
    }

    var areachart1 = new Morris.Donut({
        element: 'morris-area-chart',
        data: [{ value: 100, label: 'Sin Datos' }],
        colors: DONUT_COLORS,
        formatter: function (x) { return x + '%'; },
        resize: true
    });

    function generarTotales(columna) {

        //console.log('Columna:', columna);

        var tablajson = $table.bootstrapTable('getData');
        var mto;
        var mtototal = 0;
        var cantTotal = 0;

        var TotalesCodigo = [];
        var TotalesCantidad = [];
        var TotalesMontos = [];

        var pos = 0;

        for (var fila in tablajson) {
            var factor = 1;
            if (tablajson[fila]['Mov_Operacion'] == 'R') {
                factor = -1;
            }
            mto = tablajson[fila]['Mov_Precio'];
            var cantidad = Math.abs(tablajson[fila]['Mov_Cantidad']) * factor;
            pos = TotalesCodigo.indexOf(tablajson[fila][columna]);
            if (pos == -1) {
                pos = TotalesCodigo.length;
                TotalesCodigo.push(tablajson[fila][columna]);
                TotalesCantidad.push(0);
                TotalesMontos.push(0);
            }
            TotalesCantidad[pos] = TotalesCantidad[pos] + cantidad;
            TotalesMontos[pos] = TotalesMontos[pos] + parseFloat(mto);
            mtototal = mtototal + parseFloat(mto);
            cantTotal = cantTotal + cantidad;
        }

        var $tableTotales = $('#tabla_total');
        var DataGrafico = [];

        var filas = 0;

        TotalesCodigo.forEach(function (elemento, indice, array) {
            filas = filas + 1;
        console.log('mtototal:', mtototal);

            var procentaje = mtototal > 0 ? TotalesMontos[indice] / mtototal * 100 : 0;
            var procentajeCantidad = cantTotal > 0 ? TotalesCantidad[indice] / cantTotal * 100 : 0;
            DataGrafico[indice] = {
                mtos: TotalesMontos[indice].toFixed(0),
                cantidad: TotalesCantidad[indice].toFixed(0),
                value: procentaje.toFixed(2),
                valueCantidad: procentajeCantidad.toFixed(2),
                label: TotalesCodigo[indice]
            };
        });

        areachart1.setData(DataGrafico);

        if (mtototal > 0) {
            DataGrafico[filas] = {
                mtos: "<b>" + mtototal.toFixed(0) + "</b>",
                cantidad: "<b>" + cantTotal + "</b>",
                value: "100",
                valueCantidad: "100",
                label: "<b>T O T A L E S</b>"
            };
        }
        $tableTotales.bootstrapTable('load', DataGrafico);
    }

</script>

@endsection <!-- Fin scrip -->
