@extends('template.informes')
@section('titulo','Informe Consolidado')
   
@section('contenido')


<form class="form-inline" role="form" >
    <!-- Panel Del Titulo y Filtros -->
    <div class="panel panel-info">         
        <div class="panel-heading">
              <h3 class="panel-title">Informe Consolidado</h3>
        </div>
        <div class="panel-body">
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
                    <label class="control-label">Sucursal:</label>
                    <select  name="Sucursal" id="Sucursal"  class="form-control">
                      @include('common.combo_sucursal')
                    </select>
              </div>    

              <div class="form-group">
                    <label class="control-label">Expresado en:</label>
                    <select  name="moneda" id="moneda"  class="form-control">
                        <option value="1" >Pesos</option>
                        <option value="2" >Dolar Blue</option>
                    </select>
              </div>    

              
              <div class="form-group">
       		       <button type="button" onClick="consultar()" class="btn btn-primary pull-right">Consultar</button>
              </div>  
            </div>
        </div> <!-- Fin Panel Info -->
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
      <div class="col-md-7">
          <table id="mitabla"
           data-toggle="table"
           data-show-export="true"
           data-show-print="true"
           data-cache = "false"
           data-page-list=""
           class="table table-striped"
           >
           <thead>
              <tr>
                <th data-field="periodo" data-halign="center"  data-align="center">Periodo</th>
                <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor1" data-halign="center" data-align="right">Ventas</th>
                <th data-field="valor2" data-halign="center" data-align="right">Salidas</th>
                <th data-field="valor3" data-halign="center" data-align="center">Saldo</th>
                <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor4" data-halign="center" data-align="right">Tarjetas</th>
                <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor5" data-halign="center" data-align="right">Facturación</th>
                <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor6" data-halign="center" data-align="right">Rendimiento</th>
                <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor7" data-halign="center" data-align="right">Gastos</th>
                <th data-field="valor8" data-halign="center" data-align="right">Saldo</th>
              </tr>
            </thead>
    	 </table>
       <br>
     </div>   <!-- /.Col -->        
     <div class="col-md-5">
        <div id="morris-line"></div>
        <div id="morris-line2"></div>
     </div>   <!-- /.Col -->        
  
  </div> <!-- fin Panel Tabla -->
  </div>   <!-- /.Row -->

</form> 


@endsection <!-- Fin Contenido -->

@section('scrip')

<script>

  var $fecha;
  var $fechafin;
  var $table = $('#mitabla');


  var grafico = new Morris.Line({
    element          : 'morris-line',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a', 'b'],
    labels           : ['Tarjetas','Facturación'],
    lineColors: ['#1E25B2','red'], // Azul y Rojo
    lineWidth        : 2,
    parseTime : false,
    preUnits    : '$',
    smooth: false,
    gridTextSize     : 10
  });

  var grafico2 = new Morris.Line({
    element          : 'morris-line2',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a', 'b' , 'c'],
    labels           : ['Rendimiento','Gastos' ,'Saldo'],
    lineColors: ['#1E25B2','red','green'], // Azul y Rojo Verde
    lineWidth        : 2,
    parseTime : false,
    preUnits    : '$',
    smooth: false,
    gridTextSize     : 10
  });

 // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {
    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla
      const mes = args [2].mes.toString()
      fec1 = $fecha.substr(0,4) + '-' + mes.padStart( 2,'0') + '-01' 
      fec2 = $fecha.substr(0,4) + '-' + mes.padStart( 2,'0') + '-31' 
      fecIni = $('#daterange-btn').data('daterangepicker').startDate.format('YYYY-MM-DD')
      fecFin = $('#daterange-btn').data('daterangepicker').endDate.format('YYYY-MM-DD')
      if ( fecIni > fec1 ) {
        console.log('Tomo Fecha Gral Ini',  fecIni)
        fec1 = fecIni
      }
      if ( fecFin < fec2 ) {
        console.log('Tomo Fecha Gral Fin',  fecFin)
        fec2 = fecFin
      }
      //console.log(mes,   fecIni ,fec1, fecFin , fec2  )

      switch ( args [0] ) { // Nombre Columna
          case  "valor1":  //  Ventas                          
            ruta = 'infmov_detalle?sucursal=' + $('#Sucursal').val() +'&fecha=' + fec1 + '&fechafin=' + fec2 + '&tipoinf=4&codigos=PorTipoinf'
            break;  
          case  "valor2":  //  Salidas - Gastos 
            // POR AHORA NO LO USO, NO TENGO ESTE TIPO DE INFO, VER SI ES NECESARIO  
            ruta = 'infmov_detalle?sucursal=' + $('#Sucursal').val() +'&fecha=' + fec1 + '&fechafin=' + fec2 + '&tipoinf=5&codigos=PorTipoinf'
            break;  
          case  "valor4":  //  Ventas con Tarjetas   
            ruta = '../ventas/forma_pago?sucursal=' + $('#Sucursal').val() +'&fecha=' + fec1 + '&fechafin=' + fec2
            break;  
          case  "valor5":  //  Facturacion                          
            ruta = '../facturas/index?fecha=' + fec1 + '&fechafin=' + fec2
            break;  
          case  "valor6":  //  Rendimiento   
            ruta = 'infmov_detalle?sucursal=' + $('#Sucursal').val() +'&fecha=' + fec1 + '&fechafin=' + fec2 + '&tipoinf=9&codigos=PorTipoinf'
            break;  
          case  "valor7":  //  Gastos Tot  
            ruta = 'infmov_detalle?sucursal=' + $('#Sucursal').val() +'&fecha=' + fec1 + '&fechafin=' + fec2 + '&tipoinf=5&codigos=PorTipoinf'
            break;  
          default:
            return     // Salir de la funcion
        }; // Fin Columna
        window.open(ruta, '', '_blanck');
    } // Clik de La tabla    
   }); // Fin Todos los Eventos de la Tabla
 
	
    function consultar()  {
        // LLama a 2da pagina con la logica de la busqueda 
        // ------------------------------------------------ 
        $.ajax({
            dataType: "json",
            data: {  fecha: $fecha , fechafin: $fechafin, sucursal: $('#Sucursal').val(), moneda: $('#moneda').val() },
            url:   'consolidado_proceso',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.tabla);
                grafico.setData(data.grafico);
                grafico2.setData(data.grafico2);
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
         // Tomo los datos de entrada
         $fecha = '<?= date("Y-m-d"); ?>';
         $fechafin = '<?= date("Y-m-d"); ?>';
         consultar();
});      

$('#daterange-btn').daterangepicker(
  {
    ranges   : {
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
      'Este año'  : [moment().startOf('year'), moment().endOf('year')],
      'Último año'  : [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
      '2do Ult. año'  : [moment().subtract(2, 'year').startOf('year'), moment().subtract(2, 'year').endOf('year')],
      'Hoy'       : [moment(), moment()],
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

</script>

@endsection <!-- Fin scrip -->