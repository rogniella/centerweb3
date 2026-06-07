@extends('template.informes')
@section('titulo','Consulta de Operaciones con Tarjetas')
   
@section('contenido')


<?php 
    $idliq = "";
    if ($_GET) {
        $idliq = $_GET["idLiq"];
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
            <th data-field="id" data-sortable="true" >Id</th>
            <th data-field="fecha_operacion" data-halign="center" data-align="center" data-sortable="true" >Operación</th>
            <th data-field="dia_semana" data-halign="center" data-align="center" data-formatter="formatoDiaSemana" data-sortable="false">Día</th>

            <th data-field="descripcion"  data-sortable="true"data-halign="center" data-align="left" >Tarjeta</th>
            <th data-field="cuotas" data-sortable="true" data-align="right">Cuotas</th>

            <th data-field="mto_bruto" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales" data-sortable="true">Mto.Ventas</th>
            <th data-field="mto_final" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales" data-sortable="true">Mto.Acreditar</th>

            <th data-field="observacion"  data-sortable="true"data-halign="center" data-align="left" >Observación</th>

            <th data-field="mto_arancel" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales" data-sortable="true">Arancel</th>
            <th data-field="iva_arancel" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales"  data-sortable="true">Iva Arancel(21)</th>
            <th data-field="mto_financiero" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales"  data-sortable="true">Cost.Financiero</th>
            <th data-field="iva_financiero" data-halign="center" data-align="right" data-formatter="formatoMoneda2Dec" data-footer-formatter="montoTotales"  data-sortable="true">Iva Cost.Finan.(10.5)</th>
            <th data-field="ret_ib" data-sortable="true" data-align="right" data-footer-formatter="montoTotales" data-align="right"> Ret.IB</th>

            <th data-field="fecha_clearing" data-halign="center" data-align="center" data-footer-formatter="idTotal" data-sortable="true" >Fecha Acred.</th>
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

    .gasto-arancel { background-color: rgba(239,68,68,.08) !important; }
    .gasto-iva-arancel { background-color: rgba(251,146,60,.08) !important; }
    .gasto-financiero { background-color: rgba(16,185,129,.08) !important; }
    .gasto-iva-financiero { background-color: rgba(99,102,241,.08) !important; }
    .gasto-ret-ib { background-color: rgba(168,85,247,.08) !important; }

    #detalleCuerpo tr.info td { background-color: rgba(37,99,235,.08) !important; font-weight: 600; }
    #detalleCuerpo tr.success td { background-color: rgba(5,150,105,.08) !important; font-weight: 600; }
    #detalleCuerpo tr td:first-child { border-right: 1px solid var(--border-light); }
    #detalleCuerpo tr td:last-child { font-family: 'Segoe UI', system-ui, sans-serif; }

    #cajaCuerpo tr.seleccionada td { background-color: rgba(5,150,105,.12) !important; }
    .caja-seleccionada { background-color: rgba(37,99,235,.1) !important; }
</style>

<!-- Modal Detalle Operacion -->
<div class="modal fade" id="modalDetalleOpe" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-credit-card"></i> Detalle de Operación</h4>
            </div>
            <div class="modal-body">
                <div class="well well-sm">
                    <div class="row" id="detalleCabecera">
                        <div class="col-xs-4"><b>Nro.Liquidación:</b> <span id="detIdLiquidacion"></span></div>
                        <div class="col-xs-4"><b>Cupón:</b> <span id="detCupon"></span></div>
                        <div class="col-xs-4"><b>Tarjeta:</b> <span id="detTarjeta"></span></div>
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
                            <th width="60%">Concepto</th>
                            <th width="20%" class="text-right">Monto</th>
                            <th width="20%" class="text-right">% s/Venta</th>
                        </tr>
                    </thead>
                    <tbody id="detalleCuerpo">
                    </tbody>
                </table>
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

                <table class="table table-condensed table-bordered table-hover" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th width="8%">ID</th>
                            <th width="12%">Fecha</th>
                            <th width="12%">Monto</th>
                            <th width="10%">Tarjeta</th>
                            <th width="6%">Cuotas</th>
                            <th width="14%">Autorización</th>
                            <th width="10%">Sucursal</th>
                            <th width="8%">Sel.</th>
                        </tr>
                    </thead>
                    <tbody id="cajaCuerpo">
                        <tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>
                    </tbody>
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
        return '<a href="javascript:void(0)" class="btn-detalle-ope" data-row=\'' +
               JSON.stringify(row).replace(/'/g, "&#39;") +
               '\' title="Ver detalle"><i class="fa fa-search-plus" style="color:#337ab7;font-size:1.1em;"></i></a>';
    }

    // ─── Click handler  para abrir modal ───
    $(document).on('click', '.btn-detalle-ope', function () {
        var row = $(this).data('row');

        // ── Cabecera ──
        $('#detIdLiquidacion').text(row.idliquidacion || '-');
        $('#detCupon').text(row.cupon || '-');
        $('#detTarjeta').text(row.descripcion || '-');
        $('#detFechaOpe').text(row.fecha_operacion || '-');
        $('#detTerminal').text(row.terminal || '-');
        $('#detLote').text(row.lote || '-');
        $('#detPlazo').text(row.plazo_pago || '-');
        $('#detCuotas').text(row.cuotas || '-');

        // ── Cálculos ──
        var mtoBruto    = parseFloat(row.mto_bruto) || 0;
        var mtoArancel  = parseFloat(row.mto_arancel) || 0;
        var ivaArancel  = parseFloat(row.iva_arancel) || 0;
        var mtoFinanc   = parseFloat(row.mto_financiero) || 0;
        var ivaFinanc   = parseFloat(row.iva_financiero) || 0;
        var retIb       = parseFloat(row.ret_ib) || 0;

        var totalGastos = mtoArancel + ivaArancel + mtoFinanc + ivaFinanc + retIb;
        var mtoAcreditar = mtoBruto - totalGastos;

        function pct(valor) {
            // Porcentaje relativo al monto bruto de venta
            return mtoBruto > 0 ? (valor / mtoBruto * 100).toFixed(2) : '0.00';
        }

        function fila(concepto, monto, clase) {
            var cls = clase || '';
            return '<tr class="' + cls + '">' +
                '<td>' + concepto + '</td>' +
                '<td class="text-right">$ ' + Number(monto).toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>' +
                '<td class="text-right">' + pct(monto) + '%</td>' +
                '</tr>';
        }

        var html = '';
        html += fila('Mto.Ventas', mtoBruto, 'success');
        html += '<tr style="border-top:2px solid #ccc;"><td colspan="3"></td></tr>';

        if (mtoArancel)  html += fila('Arancel', mtoArancel, 'gasto-arancel');
        if (ivaArancel)  html += fila('Iva Arancel (21%)', ivaArancel, 'gasto-iva-arancel');
        if (mtoFinanc)   html += fila('Cost.Financiero', mtoFinanc, 'gasto-financiero');
        if (ivaFinanc)   html += fila('Iva Cost.Finan. (10.5%)', ivaFinanc, 'gasto-iva-financiero');
        if (retIb)       html += fila('Ret.IB', retIb, 'gasto-ret-ib');

        html += '<tr style="border-top:2px solid #ccc;"><td><b>Total Gastos</b></td>' +
            '<td class="text-right"><b>$ ' + Number(totalGastos).toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</b></td>' +
            '<td class="text-right"><b>' + pct(totalGastos) + '%</b></td></tr>';

        html += fila('Mto. Neto Acreditado', mtoAcreditar, 'info');

        $('#detalleCuerpo').html(html);
        $('#modalDetalleOpe').modal('show');
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
        $('#cajaCuerpo').html('<tr><td colspan="8" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
        $('#modalCajaOpe').modal('show');

        $.ajax({
            dataType: "json",
            data: { fecha: row.fecha_operacion },
            url: 'buscar_caja',
            type: 'get',
            success: function (data) {
                $('#cajaSpinner').hide();
                if (!data.results || data.results.length === 0) {
                    $('#cajaCuerpo').html('<tr><td colspan="8" class="text-center text-muted">Sin registros de caja para esta fecha</td></tr>');
                    $('#cajaMsg').text('No se encontraron operaciones de caja con forma de pago T');
                    return;
                }
                $('#cajaMsg').text('Seleccione la operación de caja a asociar (' + data.results.length + ' registro(s))');
                var html = '';
                $.each(data.results, function (i, c) {
                    var fecha = c.Caj_FecMov ? c.Caj_FecMov.substring(0, 10) : '-';
                    var monto = Number(c.Caj_Monto || 0).toLocaleString('es-AR', {minimumFractionDigits:2});
                    html += '<tr data-id="' + c.Caj_IdWEB + '">' +
                        '<td>' + c.Caj_IdWEB + '</td>' +
                        '<td>' + fecha + '</td>' +
                        '<td class="text-right">$ ' + monto + '</td>' +
                        '<td>' + (c.Caj_Tarjeta || '-') + '</td>' +
                        '<td class="text-right">' + (c.Caj_Cuotas || 0) + '</td>' +
                        '<td>' + (c.Caj_Autoriza || '-') + '</td>' +
                        '<td>' + (c.Caj_Sucursal || '-') + '</td>' +
                        '<td class="text-center"><button class="btn btn-success btn-xs btn-asociar-caja">Seleccionar</button></td>' +
                        '</tr>';
                });
                $('#cajaCuerpo').html(html);
            },
            error: function (xhr) {
                $('#cajaSpinner').hide();
                $('#cajaCuerpo').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>');
                $('#cajaMsg').text('Error al buscar operaciones de caja');
                msgerror(xhr.responseText);
            }
        });
    });

    // ─── Asociar caja seleccionada ───
    $(document).on('click', '.btn-asociar-caja', function () {
        var idCaja = $(this).closest('tr').data('id');
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
