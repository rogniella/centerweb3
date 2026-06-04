@extends('template.informes')
@section('titulo','Movimientos de Productos')
   
@section('contenido')

<form role="form" >
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-exchange"></i> Consulta de Movimientos de Productos</h3>
        </div>
        <div class="panel-body">
            <a class="mas-filtros-divider" data-toggle="collapse" href="#collapseFiltro" role="button" aria-expanded="false" aria-controls="collapseFiltro">
                    <span class="line"></span>
                    <span class="arrow">▼</span>
                    <span class="divider-label divider-label-contracted">Más Opciones...</span>
                    <span class="divider-label divider-label-expanded">Menos Opciones</span>
                    <span class="line"></span>
            </a>

            <div class="collapse" id="collapseFiltro">
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
                        <label class="control-label">Cod. Cero:</label>
                        <select name="cod_cero" id="cod_cero" class="form-control">
                            <option value="">NO</option>
                            <option value="S">SI</option>
                        </select>
                    </div>
                </div>
            </div>

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
      </div>
     </div> <!-- Fin col -->
  </div>   <!-- /.Row -->

    <!-- Panel De la Tabla -->
    <div class="panel panel-success">        
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

</form> 

@include('common.modal_consulta')

@endsection <!-- Fin Contenido -->

@section('scrip')

<style>

    :root {
        --card-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
        --card-shadow-hover: 0 4px 12px rgba(0,0,0,.1), 0 2px 4px rgba(0,0,0,.06);
        --accent: #2c5f8a;
        --accent-light: #e8f0f9;
        --bg-soft: #f6f8fa;
        --border-soft: #e4e7ec;
    }

    .panel {
        border-radius: 10px !important;
        box-shadow: var(--card-shadow);
        border: none !important;
        transition: box-shadow .25s ease;
        overflow: hidden;
    }
    .panel:hover { box-shadow: var(--card-shadow-hover); }

    .panel-info { border-left: 4px solid #5bc0de !important; }
    .panel-success { border-left: 4px solid #5cb85c !important; }
    .panel-default { border-left: 4px solid #bbb !important; }

    .panel-heading {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f4f8 100%) !important;
        border-bottom: 1px solid var(--border-soft) !important;
        padding: 14px 18px !important;
        border-radius: 10px 10px 0 0 !important;
    }
    .panel-success > .panel-heading {
        background: linear-gradient(135deg, #f0faf0 0%, #e6f3e6 100%) !important;
    }
    .panel-info > .panel-heading {
        background: linear-gradient(135deg, #f0f8fc 0%, #e3f0f7 100%) !important;
    }

    .panel-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a2a3a;
        letter-spacing: .01em;
    }
    .panel-title i { margin-right: 6px; }

    .panel-body {
        padding: 20px !important;
        background: #fff;
    }

    .panel-body > .row { margin-bottom: 16px; }
    .panel-body > .row:last-child { margin-bottom: 0; }
    .panel-body .col-xs-12 { margin-bottom: 12px; }
    .panel-body .row:last-child .col-xs-12:last-child { margin-bottom: 0; }

    #tabla-container.loading { opacity: .6; pointer-events: none; }

    .form-control {
        border-radius: 7px;
        border: 1.5px solid var(--border-soft);
        box-shadow: none !important;
        transition: border-color .2s ease, box-shadow .2s ease;
        font-size: 13.5px;
        padding: 7px 12px;
    }
    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(44,95,138,.12) !important;
    }
    select.form-control {
        cursor: pointer;
        appearance: auto;
    }

    .control-label {
        font-weight: 600;
        font-size: 12.5px;
        color: #3a4a5a;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 5px;
        display: block;
    }

    .btn {
        border-radius: 7px;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 18px;
        transition: all .2s ease;
        position: relative;
    }
    .btn-primary {
        background: linear-gradient(135deg, #3b7cb6 0%, #2c5f8a 100%);
        border: none;
        box-shadow: 0 2px 6px rgba(44,95,138,.25);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(44,95,138,.35);
        background: linear-gradient(135deg, #4688c2 0%, #326a99 100%);
    }
    .btn-primary:active { transform: translateY(0); }
    .btn-default {
        background: #fff;
        border: 1.5px solid var(--border-soft);
        color: #3a4a5a;
    }
    .btn-default:hover {
        background: var(--bg-soft);
        border-color: #c8cdd4;
    }
    .btn-block { padding: 8px 12px; }

    .label-operacion {
        display: inline-block;
        min-width: 60px;
        font-weight: 600;
        letter-spacing: .02em;
    }
    .label {
        border-radius: 5px;
        font-size: 11.5px;
        padding: 4px 10px;
        font-weight: 600;
        letter-spacing: .02em;
    }
    .label-success { background: #2d9d5e !important; }
    .label-primary { background: #2c7abc !important; }
    .label-danger { background: #d9534f !important; }
    .label-default { background: #7a8a9a !important; }
    .label-info { background: #3ba0c8 !important; }
    .label-warning { background: #e08e0b !important; }
    .text-muted { color: #8a9aa8 !important; }

    #mitabla {
        border-radius: 8px;
        overflow: hidden;
    }
    #mitabla thead th {
        background: linear-gradient(135deg, #f6f8fa 0%, #eef2f6 100%) !important;
        border-bottom: 2px solid var(--border-soft) !important;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #3a4a5a;
        padding: 12px 10px !important;
    }
    #mitabla td {
        padding: 10px !important;
        border-bottom: 1px solid #f0f2f5 !important;
        font-size: 13px;
        vertical-align: middle !important;
    }
    #mitabla tr { cursor: pointer; transition: background .15s ease; }
    #mitabla tr:hover td { background-color: #f0f6ff; }
    #mitabla tr:last-child td { border-bottom: none !important; }

    #tabla_total thead th {
        background: linear-gradient(135deg, #fafbfc 0%, #f4f6f8 100%) !important;
        border-bottom: 2px solid var(--border-soft) !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #3a4a5a;
        padding: 10px !important;
    }
    #tabla_total td {
        padding: 9px 10px !important;
        font-size: 13px;
        border-bottom: 1px solid #f0f2f5 !important;
        vertical-align: middle !important;
    }

    .daterange-btn {
        border-radius: 7px !important;
        border: 1.5px solid var(--border-soft) !important;
        background: #fff !important;
        padding: 7px 12px !important;
        font-size: 13px;
        text-align: left;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .daterange-btn:hover {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px rgba(44,95,138,.08);
    }
    .daterange-btn i { color: var(--accent); }

    #morris-area-chart {
        border-radius: 8px;
        background: #fafbfc;
        padding: 10px 5px 0;
        min-height: 260px;
    }
    .morris-hover {
        border-radius: 8px !important;
        box-shadow: var(--card-shadow-hover);
        border: none !important;
        padding: 10px 14px !important;
        background: #fff !important;
    }
    .morris-hover-row-label { font-weight: 600 !important; color: #1a2a3a !important; }

    .mas-filtros-divider {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 10px;
        color: var(--accent);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        width: 100%;
        min-width: 0;
        padding: 0;
        margin: 0;
        transition: opacity .2s;
    }
    .mas-filtros-divider:hover { opacity: .8; text-decoration: none; }
    .mas-filtros-divider .line {
        height: 1.5px;
        background: linear-gradient(90deg, transparent, var(--border-soft), transparent);
    }
    .divider-center {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .mas-filtros-divider .arrow {
        font-size: 10px;
        transition: transform .3s ease;
    }
    .mas-filtros-divider[aria-expanded="true"] .arrow {
        transform: rotate(180deg);
    }
    .mas-filtros-divider .divider-label-contracted,
    .mas-filtros-divider[aria-expanded="true"] .divider-label-expanded { display: inline; }
    .mas-filtros-divider .divider-label-expanded,
    .mas-filtros-divider[aria-expanded="true"] .divider-label-contracted { display: none; }

    .collapse.in { padding-top: 8px; }

    table.bootgrid-table { border-radius: 8px; }

    .input-group .form-control:last-child {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .input-group .form-control:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    @media (min-width: 769px) {
        .panel-body .col-sm-1,
        .panel-body .col-sm-2,
        .panel-body .col-sm-3,
        .panel-body .col-sm-4 {
            margin-bottom: 0;
        }
    }

    .mas-filtros-divider[aria-expanded="true"] .line {
        display: block;
    }

    @media (max-width: 768px) {
        .panel-body { padding: 14px !important; }
        #mitabla td, #mitabla th { font-size: 12px; padding: 6px !important; }
    }

    .fixed-table-container { border-radius: 8px !important; border: 1px solid var(--border-soft) !important; }
    .pagination { margin: 12px 0; }
    .pagination > li > a,
    .pagination > li > span {
        border-radius: 5px !important;
        margin: 0 2px;
        border: 1px solid var(--border-soft) !important;
        color: var(--accent) !important;
        padding: 6px 12px;
        font-size: 13px;
        transition: all .15s ease;
    }
    .pagination > li.active > a {
        background: var(--accent) !important;
        border-color: var(--accent) !important;
        color: #fff !important;
    }

</style>

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
            data: { tipo_operacion: $tipo_operacion, sucursal: $suc, fecha: $fecha, fechafin: $fechafin, familia: $('#filtro_flia').val(),
                idprod: $('#id_producto').val(),
                cod_cero: $('#cod_cero').val(),
                desc_producto: $('#desc_producto').val() },
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
