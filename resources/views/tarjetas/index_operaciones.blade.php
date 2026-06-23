@extends('template.informes')
@section('titulo','Consulta de Operaciones con Tarjetas')
   
@section('contenido')


<?php 
    $idliq = "";
    if (request()) {
        $idliq = request()["idLiq"];
    }        
?>

<form role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de Operaciones con Tarjetas</h3>
            </div>
            <div class="panel-body">
                <div class="row" style="display:flex; flex-wrap:wrap; align-items:stretch; gap:10px 14px;">
                    <div class="filter-group" style="min-width:160px; flex:1 1 160px;">
                        <label class="control-label">Tarjeta</label>
                        <select name="filtro0" id="filtro0" class="form-control" required>
                            @foreach($productos as $key => $value)
                                <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group" style="min-width:160px; flex:1 1 160px;">
                        <label class="control-label">Comercio</label>
                        <select name="filtro_comercio" id="filtro_comercio" class="form-control">
                            <option value="">Todos</option>
                            @foreach($comercios as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group" style="min-width:180px; flex:1 1 180px;">
                        <label class="control-label">Acreditación</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-default daterange-btn" id="daterange-btn">
                                <span><i class="fa fa-calendar"></i> Rango de fecha</span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                    </div>

                    <div class="filter-group" style="min-width:180px; flex:1 1 180px;">
                        <label class="control-label">Fec. Operación</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-default daterange-btn" id="daterange-btn-ope">
                                <span><i class="fa fa-calendar"></i> Rango de fecha</span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                    </div>

                    <div class="filter-group" style="min-width:140px; flex:1 1 140px;">
                        <label class="control-label">Nro. Liquidación</label>
                        <input type="text" class="form-control" name="filtro2" id="filtro2" placeholder="Nro.Liquidación" value="<?= $idliq; ?>">
                    </div>

                    <div class="filter-group" style="min-width:140px; flex:1 1 140px;">
                        <label class="control-label">Terminal</label>
                        <select name="terminal" id="terminal" class="form-control" required>
                            @foreach($terminales as $key => $value)
                                <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group" style="min-width:100px; flex:1 1 100px; justify-content:flex-end;">
                        <button type="button" class="btn btn-primary" id="form-search-btn" onclick="consultar()">
                            <i class="fa fa-search" style="margin-right:4px;"></i> Consultar
                        </button>
                    </div>
                </div>
            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

        <!-- Panel De la Tabla -->
        <div class="panel panel-success">     
          <table id="mitabla"
           data-toggle="table"
           data-toolbar-align="right"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           data-show-footer="true"            
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="btn_caja" data-formatter="formatoCajaOpe" data-halign="center" data-align="center" data-sortable="false" width="30px">🔗</th>
            <th data-field="btn_detalle" data-formatter="formatoDetalleOpe" data-halign="center" data-align="center" data-sortable="false" width="30px">🔍</th>
            <th data-field="fecha_operacion"  data-footer-formatter="idTotal" data-halign="center" data-align="center" data-sortable="true" >Operación</th>
            <th data-field="dia_semana" data-halign="center" data-align="center" data-formatter="formatoDiaSemana" data-sortable="false">Día</th>

            <th data-field="descripcion"  data-sortable="true"data-halign="center" data-align="left" >Tarjeta</th>
            <th data-field="cuotas" data-sortable="true" data-align="right">Cuotas</th>

            <th data-field="mto_bruto" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales" data-sortable="true">Mto.Ventas</th>
            <th data-field="mto_final" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales" data-sortable="true">Mto.Acreditar</th>
            
            <th data-field="observacion"  data-sortable="true"data-halign="center" data-align="left" >Observación</th>

            <th data-field="mto_arancel" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales" data-sortable="true">Arancel</th>
            <th data-field="iva_arancel" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales"  data-sortable="true">Iva Arancel(21)</th>
            <th data-field="pct_arr_iva" data-formatter="formatoPctArrIva" data-halign="center" data-align="right" data-sortable="false">% Iva Aran</th>
            <th data-field="mto_financiero" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales"  data-sortable="true">Cost.Financiero</th>
            <th data-field="iva_financiero" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales"  data-sortable="true">Iva Cost.Finan.</th>
            <th data-field="pct_cost_iva" data-formatter="formatoPctCostIva" data-halign="center" data-align="right" data-sortable="false">% Iva Finan</th>
            <th data-field="ret_ib" data-sortable="true" data-align="right" data-footer-formatter="montoTotales" data-align="right"> Ret.IB</th>

            <th data-field="fecha_clearing" data-halign="center" data-align="center" data-sortable="true" >Fecha Acred.</th>
            <th data-field="fecha_presentacion" data-halign="center" data-align="center" data-sortable="true" >Presentación</th>

            <th data-field="idliquidacion"  data-sortable="true" data-halign="center" data-align="center" >Nro.Liquidación</th>
            <th data-field="terminal"  data-sortable="true" data-halign="center" data-align="center" >Terminal</th>
            <th data-field="lote"  data-sortable="true" data-halign="center" data-align="center" >Lote</th>            
            <th data-field="cupon"  data-sortable="true" data-halign="center" data-align="center" >Cupon</th>                                    
            <th data-field="plazo_pago" data-sortable="true" data-align="right">Plazo</th>

          </tr>
          </thead>
       </table>
      </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

</form> 

<style>
    #mitabla { border-radius: var(--radius); overflow: hidden; }
    #mitabla thead tr:first-child th { background: var(--bg-soft); color: var(--text-secondary); font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; border-bottom: 2px solid var(--border); padding: 10px 8px; }
    #mitabla thead tr:first-child th[data-field*="mto_"],
    #mitabla thead tr:first-child th[data-field*="ret_"],
    #mitabla thead tr:first-child th[data-field*="iva_"] { color: var(--text-primary); }
    #mitabla tbody td { font-size: 13px; padding: 8px; border-color: var(--border-light); }
    #mitabla tbody td[data-v-align="right"] { font-family: 'Segoe UI', system-ui, sans-serif; }
    #mitabla tbody tr:hover td { background-color: #f8fafc; }

    #detalleCuerpo tr:last-child td { background-color: var(--bg-soft) !important; }

    .col-ventas { background-color: rgba(5,150,105,.07) !important; }
    .col-acreditar { background-color: rgba(37,99,235,.05) !important; }
    .col-gasto { background-color: rgba(239,68,68,.05) !important; }
    .col-pct-gasto { background-color: rgba(168,85,247,.06) !important; }
    .col-total-gastos { background-color: rgba(217,119,6,.08) !important; font-weight: 600; }

    #cajaCuerpo tr.seleccionada td { background-color: rgba(5,150,105,.12) !important; }
    .caja-seleccionada { background-color: rgba(37,99,235,.1) !important; }
</style>

<!-- Modal Detalle Operacion -->
<div class="modal fade" id="modalDetalleOpe" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-credit-card"></i> Operaciones del Lote</h4>
            </div>
            <div class="modal-body">
                <div class="well well-sm">
                    <div class="row" id="detalleCabecera">
                        <div class="col-xs-4"><b>Nro.Liquidación:</b> <span id="detIdLiquidacion"></span></div>
                        <div class="col-xs-4"><b>Cupón:</b> <span id="detCupon"></span></div>
                      
                        <div class="col-xs-4" style="margin-top:5px;"><b>Fec.Opera.:</b> <span id="detFechaOpe"></span></div>
                        <div class="col-xs-4" style="margin-top:5px;"><b>Terminal:</b> <span id="detTerminal"></span></div>
                        <div class="col-xs-4" style="margin-top:5px;"><b>Lote:</b> <span id="detLote"></span></div>
                        <div class="col-xs-4" style="margin-top:5px;"><b>Plazo:</b> <span id="detPlazo"></span></div>
                        <div class="col-xs-4" style="margin-top:5px;"><b>Cuotas:</b> <span id="detCuotas"></span></div>
                    </div>
                </div>

                <table class="table table-condensed table-bordered" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th width="4%">#</th>
                            <th width="11%" class="col-ventas text-right">Mto.Ventas</th>
                            <th width="11%" class="col-acreditar text-right">Mto.Acreditar</th>
                            <th width="9%" class="col-total-gastos text-right">Total Gastos</th>
                            <th width="7%" class="text-right">% Desc</th>
                            <th width="8%" class="col-gasto text-right">Arancel</th>
                            <th width="8%" class="col-gasto text-right">Iva(21)</th>
                            <th width="8%" class="col-pct-gasto text-right">% Arr+Iva</th>
                            <th width="8%" class="col-gasto text-right">Cost.Fin</th>
                            <th width="8%" class="col-gasto text-right">Iva(10.5)</th>
                            <th width="8%" class="col-pct-gasto text-right">% Cost+Iva</th>
                            <th width="8%" class="col-gasto text-right">Ret.IB</th>
                        </tr>
                    </thead>
                    <tbody id="detalleCuerpo">
<tr><td colspan="12" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>
                    </tbody>
                </table>
                <div id="detalleInfo" style="margin-top:8px; font-size:12px; color:var(--text-muted);"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asociacion Caja -->
<div class="modal fade" id="modalCajaOpe" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-link"></i> Asociar Operación con Forma de Pago</h4>
            </div>
            <div class="modal-body">
                <div class="well well-sm" style="margin-bottom:15px;">
                    <div class="row">
                        <div class="col-xs-3"><b>Fecha Operación:</b> <span id="cajaFechaOpe"></span></div>
                        <div class="col-xs-3"><b>Mto.Ventas:</b> <span id="cajaMonto"></span></div>
                        <div class="col-xs-3"><b>Cupón:</b> <span id="cajaCupon"></span></div>
                        <div class="col-xs-3"><b>Liquidación:</b> <span id="cajaIdLiq"></span></div>
                    </div>
                </div>

                <div class="alert alert-info" style="padding:8px 14px; margin-bottom:12px;">
                    <i class="fa fa-spinner fa-spin" id="cajaSpinner" style="display:none;"></i>
                    <span id="cajaMsg">Seleccione una operación de caja para asociar</span>
                </div>

                <table id="tablaCaja"
                       data-toggle="table"
                       data-pagination="true"
                       data-page-size="5"
                       data-page-list="[5,10,20]"
                       class="table table-condensed table-bordered table-hover"
                       style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th data-field="Caj_IdWEB" data-sortable="true" data-halign="center" data-align="center" width="8%">ID</th>
                            <th data-field="Caj_Monto" data-formatter="cajaMontoFormatter" data-sortable="true" data-halign="center" data-align="right" width="12%">Monto</th>
                            <th data-field="Caj_Tarjeta" data-sortable="true" data-halign="center" data-align="center" width="10%">Tarjeta</th>
                            <th data-field="Caj_Cuotas" data-sortable="true" data-halign="center" data-align="right" width="6%">Cuotas</th>
                            <th data-field="Caj_Sucursal" data-halign="center" data-align="center" width="10%">Sucursal</th>
                            <th data-field="Caj_TipoOT" data-halign="center" data-align="center" width="10%">Tipo</th>
                            <th data-field="Caj_IdOT" data-halign="center" data-align="center" width="10%">Nro.Ope</th>
                            <th data-field="btn_sel" data-formatter="cajaSelFormatter" data-halign="center" data-align="center" width="8%">Sel.</th>
                        </tr>
                    </thead>
                </table>
                <input type="hidden" id="cajaIdOperacion" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection <!-- Fin Contenido -->

@section('scrip')


<script>

    $("#modalDetalleOpe").draggable({
      handle: ".modal-header"
    });
    $("#modalCajaOpe").draggable({
      handle: ".modal-header"
    });

// Formatea linea de Totales de la Grilla
   function idTotal() {
     return 'T O T A L E S'
   }

   function formatoDiaSemana(value, row, index) {
        if (!row.fecha_operacion) return '-';
        var dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        var fecha = new Date(row.fecha_operacion + 'T12:00:00');
        return dias[fecha.getDay()];
    }

    function formatoPctArrIva(value, row, index) {
        var arancel = parseFloat(row.mto_arancel) || 0;
        var iva21 = parseFloat(row.iva_arancel) || 0;
        if (arancel > 0) return ( iva21 / arancel * 100).toFixed(2) + '%';
        return '0.00%';
    }

    function formatoPctCostIva(value, row, index) {
        var costFin = parseFloat(row.mto_financiero) || 0;
        var iva105 = parseFloat(row.iva_financiero) || 0;
        if (costFin > 0) return (iva105 / costFin * 100).toFixed(2) + '%';
        return '0.00%';
    }


    var $fecha = '';
    var $fechafin ;
    var $fecha_ope = '';
    var $fechafin_ope ;

    $(document).ready(function(){
         // Tomo los datos de entrada , por si es llamado desde liquidaciones y trae un nro de liquidacion para mostrar sus operaciones
         idliq = '<?= $idliq; ?>';

         if ( idliq != '' )  {
           consultar();
         }
    });   

    $('#daterange-btn').daterangepicker(
     {
      ranges   : {
        'Hoy'       : [moment(), moment()],
        'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
        'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        '2do Último mes'  : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')]
     },
     startDate: moment(),
     endDate  : moment()
     },
     function (start, end) {
        $('#daterange-btn span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))
        $fecha = start.format('YYYY-M-D');
        $fechafin = end.format('YYYY-M-D');
        consultar();
     }
  
    );  // Fin $('#daterange-btn').daterangepicker(


    $('#daterange-btn-ope').daterangepicker(
     {
      ranges   : {
        'Hoy'       : [moment(), moment()],
        'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
        'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        '2do Último mes'  : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')]
     },
     startDate: moment(),
     endDate  : moment()
     },
     function (start, end) {

          $('#daterange-btn-ope span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))
          $fecha_ope = start.format('YYYY-M-D');
          $fechafin_ope = end.format('YYYY-M-D');
          consultar();

     }
  
    );  // Fin $('#daterange-btn').daterangepicker(

    var $table = $('#mitabla'); // Tabla principal

  
    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
             data: { filtro0: $('#filtro0').val(), terminal: $('#terminal').val(), filtro2: $('#filtro2').val()  ,fecha: $fecha , fechafin: $fechafin ,fechaope: $fecha_ope , fechafinope: $fechafin_ope, comercio: $('#filtro_comercio').val()  },
            url:   'buscar_operaciones',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()

    // ─── Formateador de columna Detalle ───
    function formatoDetalleOpe(value, row, index) {
        return '<a href="javascript:void(0)" class="btn-detalle-ope" data-lote="' + (row.lote || '') + '" data-cupon="' + (row.cupon || '') + '" title="Ver detalle"><i class="fa fa-search-plus" style="color:#337ab7;font-size:1.1em;"></i></a>';
    }

    // ─── Click handler para abrir modal con AJAX ───
    $(document).on('click', '.btn-detalle-ope', function () {
        var lote = $(this).data('lote');
        var cupon = $(this).data('cupon');

        $('#detalleCuerpo').html('<tr><td colspan="12" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
        $('#detalleInfo').text('');
        $('#modalDetalleOpe').modal('show');

        $.ajax({
            dataType: "json",
            data: { lote: lote, cupon: cupon },
            url: 'buscar_detalle_operacion',
            type: 'get',
            success: function (data) {
                var ops = data.results || [];
                if (ops.length === 0) {
                    $('#detalleCuerpo').html('<tr><td colspan="12" class="text-center text-muted">Sin resultados</td></tr>');
                    return;
                }

                var first = ops[0];
                $('#detIdLiquidacion').text(first.idliquidacion || '-');
                $('#detCupon').text(first.cupon || '-');

                $('#detFechaOpe').text(first.fecha_operacion || '-');
                $('#detTerminal').text(first.terminal || '-');
                $('#detLote').text(first.lote || '-');
                $('#detPlazo').text(first.plazo_pago || '-');
                $('#detCuotas').text(first.cuotas || '-');

                function fmtMon(val) {
                    return  Number(val || 0).toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
                }

                function pctDesc(row) {
                    var bruto = parseFloat(row.mto_bruto) || 0;
                    var neto = parseFloat(row.mto_final) || 0;
                    if (bruto > 0) return ((bruto - neto) / bruto * 100).toFixed(2) + '%';
                    return '0.00%';
                }

                var html = '';
                var sumBruto = 0, sumNeto = 0, sumArancel = 0, sumIva21 = 0;
                var sumCostFin = 0, sumIva105 = 0, sumRetIb = 0;

                $.each(ops, function (i, op) {
                    var bruto = parseFloat(op.mto_bruto) || 0;
                    var neto = parseFloat(op.mto_final) || 0;
                    var arancel = parseFloat(op.mto_arancel) || 0;
                    var iva21 = parseFloat(op.iva_arancel) || 0;
                    var costFin = parseFloat(op.mto_financiero) || 0;
                    var iva105 = parseFloat(op.iva_financiero) || 0;
                    var retIb = parseFloat(op.ret_ib) || 0;
                    var totalGastosFila = arancel + iva21 + costFin + iva105 + retIb;
                    var pctArrIva = arancel > 0 ? ( iva21 / arancel * 100).toFixed(2) + '%' : '0.00%';
                    var pctCostIva = costFin > 0 ? ( iva105 / costFin * 100).toFixed(2) + '%' : '0.00%';

                    if (bruto > 0) {
                         sumBruto += bruto;
                    }
                    sumNeto += neto;
                    sumArancel += arancel;
                    sumIva21 += iva21;
                    sumCostFin += costFin;
                    sumIva105 += iva105;
                    sumRetIb += retIb;

                    html += '<tr>' +
                        '<td class="text-center">' + (i + 1) + '</td>' +
                        '<td class="col-ventas text-right">' + fmtMon(bruto) + '</td>' +
                        '<td class="col-acreditar text-right">' + fmtMon(neto) + '</td>' +
                        '<td class="col-total-gastos text-right">' + fmtMon(totalGastosFila) + '</td>' +
                        '<td class="text-right">' + pctDesc(op) + '</td>' +
                        '<td class="col-gasto text-right">' + fmtMon(arancel) + '</td>' +
                        '<td class="col-gasto text-right">' + fmtMon(iva21) + '</td>' +
                        '<td class="col-pct-gasto text-right">' + pctArrIva + '</td>' +
                        '<td class="col-gasto text-right">' + fmtMon(costFin) + '</td>' +
                        '<td class="col-gasto text-right">' + fmtMon(iva105) + '</td>' +
                        '<td class="col-pct-gasto text-right">' + pctCostIva + '</td>' +
                        '<td class="col-gasto text-right">' + fmtMon(retIb) + '</td>' +
                        '</tr>';
                });

                var sumTotalGastos = sumArancel + sumIva21 + sumCostFin + sumIva105 + sumRetIb;
                var pctArrIvaTotal = sumBruto > 0 ? ((sumArancel + sumIva21) / sumBruto * 100).toFixed(2) + '%' : '0.00%';
                var pctCostIvaTotal = sumBruto > 0 ? ((sumCostFin + sumIva105) / sumBruto * 100).toFixed(2) + '%' : '0.00%';
                html += '<tr style="border-top:2px solid #ccc; font-weight:600;">' +
                    '<td class="text-center"><b>TOTAL</b></td>' +
                    '<td class="col-ventas text-right">' + fmtMon(sumBruto) + '</td>' +
                    '<td class="col-acreditar text-right">' + fmtMon(sumNeto) + '</td>' +
                    '<td class="col-total-gastos text-right">' + fmtMon(sumTotalGastos) + '</td>' +
                    '<td class="text-right">' + (sumBruto > 0 ? ((sumBruto - sumNeto) / sumBruto * 100).toFixed(2) + '%' : '0.00%') + '</td>' +
                    '<td class="col-gasto text-right">' + fmtMon(sumArancel) + '</td>' +
                    '<td class="col-gasto text-right">' + fmtMon(sumIva21) + '</td>' +
                    '<td class="col-pct-gasto text-right">' + pctArrIvaTotal + '</td>' +
                    '<td class="col-gasto text-right">' + fmtMon(sumCostFin) + '</td>' +
                    '<td class="col-gasto text-right">' + fmtMon(sumIva105) + '</td>' +
                    '<td class="col-pct-gasto text-right">' + pctCostIvaTotal + '</td>' +
                    '<td class="col-gasto text-right">' + fmtMon(sumRetIb) + '</td>' +
                    '</tr>';

                $('#detalleCuerpo').html(html);
                $('#detalleInfo').text(ops.length + ' operación(es) encontrada(s) con el mismo lote y cupón');
            },
            error: function (xhr) {
                $('#detalleCuerpo').html('<tr><td colspan="12" class="text-center text-danger">Error al cargar datos</td></tr>');
                msgerror(xhr.responseText);
            }
        });
    });

    // ─── Formateador de columna Caja ───
    function formatoCajaOpe(value, row, index) {
        var icono = row.tar_idCaja
            ? '<i class="fa fa-check-circle" style="color:#28a745;font-size:1.1em;" title="Asociado a caja"></i>'
            : '<i class="fa fa-link" style="color:#337ab7;font-size:1.1em;" title="Asociar con caja"></i>';
        return '<a href="javascript:void(0)" class="btn-caja-ope" data-row=\'' +
               JSON.stringify(row).replace(/'/g, "&#39;") +
               '\'>' + icono + '</a>';
    }

  

    function cajaMontoFormatter(value) {
        return '$ ' + Number(value || 0).toLocaleString('es-AR', {minimumFractionDigits:2});
    }

    function cajaSelFormatter(value, row) {
        return '<button type="button" class="btn btn-success btn-xs btn-asociar-caja" data-id="' + row.Caj_IdWEB + '">Seleccionar</button>';
    }

    // ─── Click handler modal Caja Busca Formas de Pago = T para esa fecha  ───
    $(document).on('click', '.btn-caja-ope', function () {
        var row = $(this).data('row');
        if (row.tar_idCaja) {
            msgerror('Esta operación ya está asociada a un registro de caja (ID: ' + row.tar_idCaja + ').');
            return;
        }

        $('#cajaFechaOpe').text(row.fecha_operacion || '-');
        $('#cajaMonto').text('$ ' + Number(row.mto_bruto || 0).toLocaleString('es-AR', {minimumFractionDigits:2}));
        $('#cajaCupon').text(row.cupon || '-');
        $('#cajaIdLiq').text(row.idliquidacion || '-');
        $('#cajaIdOperacion').val(row.id);
        $('#cajaMsg').text('Buscando operaciones de caja...');
        $('#cajaSpinner').show();
        $('#tablaCaja').bootstrapTable('removeAll');
        $('#modalCajaOpe').modal('show');

        $.ajax({
            dataType: "json",
            data: { fecha: row.fecha_operacion },
            url: 'buscar_caja',
            type: 'get',
            success: function (data) {
                $('#cajaSpinner').hide();
                if (!data.results || data.results.length === 0) {
                    $('#cajaMsg').text('No se encontraron operaciones de caja con forma de pago T');
                    return;
                }
                $('#cajaMsg').text('Seleccione la operación de caja a asociar (' + data.results.length + ' registro(s))');
                $('#tablaCaja').bootstrapTable('load', data.results);
            },
            error: function (xhr) {
                $('#cajaSpinner').hide();
                $('#cajaMsg').text('Error al buscar operaciones de caja');
                msgerror(xhr.responseText);
            }
        });
    });

    // ─── Asociar caja seleccionada ───
    $(document).on('click', '.btn-asociar-caja', function () {
        var idCaja = $(this).data('id');
        var idOperacion = $('#cajaIdOperacion').val();

        if (!idCaja || !idOperacion) return;

        var btn = $(this);
        btn.prop('disabled', true).text('Asociando...');

        $.ajax({
            dataType: "json",
            data: { tar_idCaja: idCaja, id_operacion: idOperacion },
            url: 'asociar_caja',
            type: 'post',
            success: function () {
                $('#modalCajaOpe').modal('hide');
                consultar();
                muestroMsg('Asociación guardada correctamente', 3000, true);
            },
            error: function (xhr) {
                btn.prop('disabled', false).text('Seleccionar');
                msgerror(xhr.responseText);
            }
        });
    });
       
</script>
 
@endsection <!-- Fin scrip -->
