@extends('template.informes')
@section('titulo','Listado de Pagos')
   
@section('contenido')

<form class="form-inline" role="form" >
    <!-- Panel Del Titulo y Filtros -->
    <div class="panel panel-info">         
        <div class="panel-heading">
              <h3 class="panel-title">Listado de Pagos por su Forma</h3>
        </div>
        <div class="panel-body">
                <div class="form-group">
                    <label class="control-label">Forma Pago:</label>
                    <select  name="tipo_informe" id="tipo_informe"  class="form-control">
                      <option value= "">[Todas]</option>
                      <option value= "T" selected>Tarjetas </option>
                      <option value= "P">Efectivo </option>
                    </select>
                </div>    
                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == "" ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>    
                <div class="form-group">
                    <label class="control-label">Fechas:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right daterange-btn" id="daterange-btn">
                      <span>
                        <i class="fa fa-calendar"></i> Rango de fecha
                      </span>
                        <i class="fa fa-caret-down"></i>
                    </button>              
                    </div>
              </div>

              <div class="form-group">
                    <label class="control-label">Tipo Tarjeta:</label>
                    <select name="tipoot" id="tipoot" class="form-control">
                        <option value="" >[Todas]</option>
                        <option value="DBMA" >Débito</option>
                        <option value="VI">Crédito</option>
                        <option value="MP">Mercado Pago</option>
                    </select>
                    <label class="control-label">Estado:</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="" >[Todos]</option>
                        <option value="A" >Anulado</option>
                        <option value="V">Vigente</option>
                    </select>
              </div>  
              <div class="form-group">
       		       <button type="button" onClick="consultar()" class="btn btn-primary pull-right">Consultar</button>
              </div>  
            </div>
        </div> <!-- Fin Panel Info -->

        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <table id="mitabla"
           data-toggle="table"
           data-search="true"
           data-show-export="true"
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""
           class="table table-striped"
           >
          <thead>
          <tr>
            <th data-field="Caj_SucursalOri" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="fecha" data-halign="center" data-align="center" data-sortable="true">Fecha</th>
            <th data-field="Caj_Moneda" data-halign="center" data-align="center" data-sortable="true">Tipo</th>

            <th data-field="Caj_Detalle" data-align="left"  data-sortable="true">Detalle</th>

            <th data-field="Caj_Monto" data-halign="center" data-align="right" data-sortable="true">Monto</th>

            <th data-field="Caj_Tarjeta" data-halign="center" data-align="center" data-sortable="true">Tarjeta</th>
            <th data-field="Caj_Cuotas" data-halign="center" data-align="center" data-sortable="true">Cuotas</th>
            <th data-field="Caj_Autoriza" data-halign="center" data-align="center" data-sortable="true">Lote/Cup</th>

            <th data-field="Caj_TipoOT" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="Caj_IdOT" data-formatter="fotmatoColSel"  data-halign="center" data-align="center" data-sortable="true">OT Nro </th>
            <th data-field="Caj_Responsable" data-halign="center" data-align="center" data-sortable="true">Vendedor</th>
            <th data-field="factura" data-formatter="fotmatoColSel" data-halign="center" data-align="center" data-sortable="true">Factura</th>
            <th data-field="Caj_idWEB" data-visible="false" ></th>
          </tr>
          </thead>
    	 </table>
	</div> <!-- fin Panel Tabla -->
  
  <!-- Panel De Totales -->              	
  <h4><b>T O T A L I Z A D A S</b></h4>
  <!-- Fila  Panel De Totales -->
    <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <select name="groupby" id="groupby" class="form-control">
                        <option value="" >Agrupar por..</option>
                        <option value="Caj_SucursalOri" >Sucursal</option>
                        <option value="Caj_Moneda" >Tipo</option>
                        <option value="Caj_Tarjeta">Tarjeta</option>
                    </select>
                </div>      
        </div>      
    </div>   <!-- /.Row -->
    <div class="row">
            <div class="col-md-6">
              <table id="tabla_total"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="success" data-field="label" data-halign="center"  data-align="left" data-sortable="true">Descripción</th>
                <th data-field="cantidad" data-sortable="true" data-halign="center" data-align="right">Cantidad</th>
                <th data-field="mtos" data-sortable="true" data-halign="center" data-align="right">Importes</th>
                <th data-field="valueCantidad" data-sortable="true" data-halign="center" data-align="right">% Cant</th>
                <th data-field="value" data-sortable="true" data-halign="center" data-align="right">% Montos</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
            <div class="col-md-6">
                <div id="morris-area-chart"></div>
            </div>  <!-- Fin .col-lg-6 -->
  </div>   <!-- /.Row -->

</form> 

@include('common.modal_consulta')

@endsection <!-- Fin Contenido -->

@section('scrip')
<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>


  var $fecha;
  var $fechafin;
	
  var $table = $('#mitabla');

 // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {

    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla
        if ( args [0] == 'Caj_IdOT'){  // Nombre Columna       
           // Busco los datos de la OT o Comprobante y despliega pantall Modal
          consulta_comprobante(args [2].Caj_TipoOT, args [2].Caj_IdOT, args [2].Caj_SucursalOri)
        }; // Fin Clik Id OT

      if ( args [0] == 'factura'){  // Nombre Columna                          
        // Re-Immprimo Factura
        $.ajax({
            dataType: "json",
            data: { sucursal: args [2].Caj_SucursalOri, tipo: args [2].Caj_TipoOT, id: args [2].Caj_IdOT, soloGenera:'si' },
            url:   '../ventas/imprimePDF',
            type:  'get',
            success: function(data){
              if(data.retError == "" || data.retError == null) { 
                  // ir a la pantalla del pdf
                  if(data.pdf != "" ) {
                      window.open(data.pdf, '', '_blanck');
                  }    
              }else{
                  msgerror( data.retError);
              }
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax

      }; // Fin Clik Factura

    } // Clik de La tabla    
   }); // Fin Todos los Eventos de la Tabla
 

    $(function () {
        // Si cambia el cmb de Agrupacion
        $('select[name="groupby"]').change(function () {
            console.log($(this).val())
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            generarTotales(  $('select[name="groupby"]').val()  ) // Genera totales segun 
        });    

        // Si Cambia el cmb de Tipo de Ot
        $('select[name="estado"]').change(function () {
            consultar();
        });
        $('select[name="tipo_informe"]').change(function () {
            consultar();
        });
        $('select[name="sucursal"]').change(function () {
            consultar();
        });
    });
	
    function consultar()  {
        // LLama a 2da pagina con la logica de la busqueda 
        // ------------------------------------------------ 
        $tipoinforme  = $('#tipo_informe').val();   
        $sucursal  = $('#sucursal').val();   
        $tipoot = $('#tipoot').val()  
        $estado = $('#estado').val()  
        $.ajax({
            dataType: "json",
            data: { tipoinforme:$tipoinforme, sucursal: $sucursal, tipoot: $tipoot, fecha: $fecha , fechafin: $fechafin,estado: $estado },
            url:   'forma_pago_carga',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales('Caj_Tarjeta') // Genera totales por Tipo Col =1
            },
            error:	function(xhr,err){ 
                    // console.log("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
                // readyState values are 1:loading, 2:loaded, 3:interactive, 4:complete.
                // status is the HTTP status number, i.e. 404: not found, 500: server error, 200: ok.
                if (xhr.readyState == 401) {
                   msgerror( "Se desconecto. Vuelva a Ingresar su Usuario");
                }else{
                   msgerror( xhr.responseText,err);
                }
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
				  
                                  
/*=============================================
DATE RANGE
=============================================*/
$(document).ready(function(){
         $fecha = '{{ $filtro_fecha }}';
         $fechafin = '{{ $filtro_fechafin }}';
         $('#sucursal').val('{{ $filtro_sucursal }}');
         consultar();
});      

$('#daterange-btn').daterangepicker(
  {
    ranges   : {
      'Hoy'       : [moment(), moment()],
      'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
      '2do Mes Anterior'  : [moment().subtract(3, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')]
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



    var areachart1 = new Morris.Donut({
        element          : 'morris-area-chart',
        data             : [
           {value: 100, label: 'Sin Datos'}
        ],
        formatter: function (x) { return x + "%"}
            }).on('click', function(i, row){
           // console.log(i, row);
    });

    function generarTotales(columna)  {

        // En columna tiene que ir como se llama en la grilla
        // Recorro la tabla para actualizar los Totales
        console.log('Columna:',columna)

        var tablajson = $table.bootstrapTable('getData');
        var mto;
        var mtototal = 0;
        var cantTotal = 0;

        var TotalesCodigo = [];
        var TotalesCantidad = [];
        var TotalesMontos = [];

        var pos=0;

        for (var fila in tablajson) {
            mto = tablajson[fila]['Caj_Monto'] // Los importes estan en la col 3
            // vectores acumuladores
            pos = TotalesCodigo.indexOf(tablajson[fila][columna]) 
            if (  pos == -1 ) {
                pos = TotalesCodigo.length
                TotalesCodigo.push (tablajson[fila][columna])
                TotalesCantidad.push(0)
                TotalesMontos.push(0)
            } 
            TotalesCantidad[pos] =  TotalesCantidad [pos] + 1
            TotalesMontos[pos] =  TotalesMontos [pos] + parseFloat(mto)
            mtototal = mtototal +  parseFloat(mto)
            cantTotal = cantTotal + 1
        }

        var $tableTotales = $('#tabla_total'); 
        var DataGrafico = [];

        var filas = 0
       
        TotalesCodigo.forEach(function (elemento, indice, array) {
              filas = filas + 1
              procentaje = TotalesMontos[indice] / mtototal * 100
              procentajeCantidad = TotalesCantidad[indice] / cantTotal * 100
              DataGrafico[indice] = {
               mtos:  TotalesMontos[indice].toFixed(0) ,
               cantidad:  TotalesCantidad[indice].toFixed(0) ,
               value:  procentaje.toFixed(2) ,
               valueCantidad:  procentajeCantidad.toFixed(2) ,
               label:  TotalesCodigo[indice]
              }
        })
        areachart1.setData(DataGrafico);
        if (mtototal > 0) {
            DataGrafico[ filas ] = {
               mtos:  "<b>" + mtototal.toFixed(0) + "</b>" ,
               cantidad:  "<b>" + cantTotal + "</b>" ,
               value:  "100" ,
               valueCantidad:  "100" ,
               label:  "<b> T O T A L E S </b>"
            }
        };    
        $tableTotales.bootstrapTable('load', DataGrafico);
    } // Fin GenerarTotales                                  

</script>

@endsection <!-- Fin scrip -->