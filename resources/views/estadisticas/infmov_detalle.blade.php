@extends('template.main_alta_modal')
@section('titulo','Movimientos Detallados por Códigos')
   
@section('contenido')

<?php 
    $sucursal = "0";
    if ($_GET) {
        $sucursal = $_GET["sucursal"];
        $INF_ID = $_GET["tipoinf"];
        $param_codigos = $_GET["codigos"];
       // $param_codigos = explode(",", $codigos);
        if ( isset($_GET["fecha"])) {
          // Lo llamaron pasando las fechas directamente
          $fecha = $_GET["fecha"];
          $fechafin = $_GET["fechafin"];
        }else{  
          $anio = $_GET["anio"];
          $mes = $_GET["mes"];
          $diafin = date("d",(mktime(0,0,0,$mes+1,1,$anio)-1));
          if ( $mes > 12 )  {
            $fecha = date("Y-m-d",(mktime(0,0,0,1,1,$anio)));
            $fechafin = date("Y-m-d",(mktime(0,0,0,12,31,$anio)));
          } else {
            $fecha =   date("Y-m-d",(mktime(0,0,0,$mes,1,$anio)));
            $fechafin =  date("Y-m-d",(mktime(0,0,0,$mes,$diafin,$anio)));
          }
        }    
    }        
?>


<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title" id="titulo" >Consulta por movimientos Detallados</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <select  name="Ot_Sucursal" id="Ot_Sucursal"  class="form-control">
                      @include('common.combo_sucursal')
                    </select>
                </div>    

                <div class="form-group">
                        <label class="control-label">Tipo Informe:</label>
                        <select id="tipo_inf1" name="tipo_inf1" class="form-control">
                           <?PHP  
                                $INF_TIPO =1;     
                           ?>
                           @include('common.combo_informe')

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
                    <select name="dropdown" id="groupby" class="form-control">
                        <option value="" >Agrupar por..</option>
      			<option value="codigo" >Codigo</option>
      			<option value="moneda">Moneda</option>
                    </select>
                </div>               
 
            </div> <!-- Fin Panel BodyInfo -->
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
           data-page-size="40"
           data-page-list=""      
           class="table table-striped"
          >
          <thead>
          <tr>
            <th data-field="sucursal"  data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="fecha" data-halign="center" data-align="center" data-sortable="true">Fecha</th>
            <th data-field="codigo" data-halign="center" data-align="left" data-sortable="true">Código </th>
            <th data-field="monto" data-halign="center" data-align="right" data-sortable="true">Monto</th>
            <th data-field="moneda" data-halign="center" data-align="center" data-sortable="true">Moneda</th>
            <th data-field="mtopesos" data-halign="center" data-align="right" data-sortable="true">Monto $</th>
            <th data-field="descri" data-sortable="true">Detalle</th>
            <th data-field="tipoOT" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="idfac" data-formatter="fotmatoColSel"  data-halign="center" data-align="center" data-sortable="true">OT Nro </th>
            <th data-field="id" data-halign="center"  data-align="center" data-formatter="opcionesFormatter">Opciones</th>
          </tr>
          </thead>
    	 </table>
	</div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

  <!-- Segunda Fila de Informes -->
  <div class="row">
    <!-- Panel De Totales -->
    <div class="col-sm-12">
        <div class="panel panel-warning">
            <div class="panel-heading">
                Totalizados
            </div>   <!-- /.panel-heading -->
            <div class="panel-body">
            <div class="col-lg-6">
              <table id="tabla_inf"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th data-field="label" data-halign="center"  data-align="left" data-sortable="true">Concepto </th>
                <th data-field="mtos" data-halign="center" data-align="right" data-sortable="true">Acumulado</th>
                <th data-field="value" data-halign="center" data-align="right" data-sortable="true">%</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
            <div class="col-lg-6">
                <div id="morris-area-chart"></div>
            </div>  <!-- Fin .col-lg-6 -->
            </div>    <!-- /.panel-body -->
        </div>   <!-- / Fin.panel -->
    </div>  <!-- Fin .col-lg-12 -->

  </div>   <!-- /.Row -->

</form> 



<?php

  $modulo_abm='cajas';  
  $campos_pantalla = [
      [ 'name' => 'MCaj_FecMov'],
      [ 'name' => 'MCaj_Sucursal'],
      [ 'name' => 'MCaj_CtaOri'],
      [ 'name' => 'MCaj_Codigo'],
      [ 'name' => 'MCaj_Monto'],
      [ 'name' => 'MCaj_Moneda'],
      [ 'name' => 'MDes_Descripcion']
  ];

?>

@include('cajas.alta_modif')

@include('common.modal_consulta')

@endsection <!-- Fin Contenido -->

@section('scrip')

<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>

    var $table = $('#mitabla'); // Tabla principal
    var $fecha;
    var $fechafin;


  // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {

  if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla

    if ( args [0] == 'idfac'){  // Nombre Columna                          
       // Busco los datos de la OT o Comprobante y despliega pantall Modal
       consulta_comprobante(args [2].tipoOT, args [2].idfac, args [2].sucursal)
    }; // Fin Clik Id OT

  } // Clik de La tabla    

  }); // Fin Todos los Eventos de la Tabla


	
     function opcionesFormatter(value,columnas) {
      var Id = value;

      @if(Auth::user())
        @if(Auth::user()->perfil_id == 'ADM')
        console.log(columnas.codigo.substring(0,4) )
        if ( columnas.codigo.substring(0,4) != '0900') { // Si no es trasferencia

          var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar registro" onclick="showEditModalCaja(true,\''+ Id +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';
        }             
        @endif
      @endif

      return opciones;
  }

    // Cambio el Combo de Agrupacion    
    $(function () {
        $('select[name="dropdown"]').change(function () {
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            //console.log ($('select[name="dropdown"]').val())
            if ($('select[name="dropdown"]').val() == 'codigo') { 
                generarTotales(0) // Genera totales por Codigo Col =0 Moneda Col =2
            } else {
                generarTotales(2) // Genera totales por Codigo Col =0 Moneda Col =2                
            }
        });    
    });
	

    // Funcion de carga de Tablas
    function consultar()  {
       	// LLama a 2da pagina con la logica de la busqueda
	      // ------------------------------------------------      
        $sucursal  = $('#Ot_Sucursal').val();   
        codigos = '<?= $param_codigos; ?>';
        var vec_codigos = codigos.split(',')

        $('#titulo').html('Consulta movimientos Detallado del ' + $fecha + ' al ' + $fechafin);
        $tipoinf = $('#tipo_inf1').val()
	      $.ajax({
            dataType: "json",
            data: { sucursal: $sucursal,tipo_inf: $tipoinf, fecha: $fecha, fechafin: $fechafin, codigos: vec_codigos},
            url:   'infmov_detalle_proceso',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales(0) // Genera totales por Codigo
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
    
    
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

        // Recorro la tabla para actualizar los Totales
        //alert(JSON.stringify($table.bootstrapTable('getData')));
        var tablajson = $table.bootstrapTable('getData');
        var mto;
        var mtototal = 0;
        var procentaje=0;

        var DataGrafico = [];
        var TotalesCodigo = [];
        var TotalesDescri = [];
        var TotalesMontos = [];
        var pos=0;

        for (var fila in tablajson) {
            // Si queremos recorrer por columnas
            //  for (var col in tablajson[fila]) {
            //    console.log( tablajson[sfila][col])                    
            //  }
            mto = tablajson[fila].mtopesos // Los importes estan en la col 
            mto = mto.replace(".","") //primer paso: fuera puntos
            // vectores acumuladores
            if (columna == 0 ) {
                elemento = tablajson[fila].codigo // Si es por Codigo va la descri del codigo
                descripcion = tablajson[fila].codigo // Es por moneda
            }else{
                elemento = tablajson[fila].moneda // Es por moneda 
                descripcion = descripcionMoneda( tablajson[fila].moneda ) // Es por moneda
            }

            pos = TotalesCodigo.indexOf(elemento )
            if (  pos == -1 ) {
                pos = TotalesCodigo.length
                TotalesCodigo.push (elemento)
                TotalesMontos.push(0)
                TotalesDescri.push(descripcion) 
                // Si es por Codigo va la descri del codigo
   console.log(tablajson[fila].codigo)
   console.log('ele' + elemento)
   console.log(tablajson[fila].moneda)
   console.log(tablajson)
   console.log(columna)
   
   
            } 
            TotalesMontos[pos] =  TotalesMontos [pos] + parseFloat(mto)
            mtototal = mtototal +  parseFloat(mto)

        }

        var $tableTotales = $('#tabla_inf'); 
        var filas = 0

        TotalesCodigo.forEach(function (elemento, indice, array) {
            procentaje = TotalesMontos[indice].toFixed(0) / mtototal * 100
            DataGrafico[indice] = {
               mtos:  TotalesMontos[indice].toFixed(0) ,
               value:  procentaje.toFixed(2) ,
               label:  TotalesDescri[indice]
            }
            filas = indice
        })
        areachart1.setData(DataGrafico);
        if (mtototal > 0) {
            DataGrafico[filas+1] = {
               mtos:  "<b>" + mtototal.toFixed(0) + "</b>" ,
               value:  "100" ,
               label:  "<b> T O T A L E S </b>"
            }
        };    
        $tableTotales.bootstrapTable('load', DataGrafico);
    } // Fin GenerarTotales
	
        
/*=============================================
DATE RANGE
=============================================*/
    $(document).ready(function(){
         // Tomo los datos de entrada
         document.getElementById("Ot_Sucursal").value = '<?= $sucursal; ?>';
      //   $fecha = '<?= date("Y-m-d"); ?>';


          // Tomo los datos de entrada
          $fecha = '<?= $fecha; ?>';
          $fechafin = '<?= $fechafin; ?>';
          consultar();
    });      

$('#daterange-btn').daterangepicker(
  {
    ranges   : {
      'Hoy'       : [moment(), moment()],
      'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Últimos 7 días' : [moment().subtract(6, 'days'), moment()],
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment(),
    endDate  : moment()
    },
    function (start, end) {
        $('#daterange-btn span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))
   //     var fechaInicial = start.format('YYYY-M-D');
   //     var fechaFinal = end.format('YYYY-M-D');
  //  	consultar(fechaInicial,fechaFinal);
        $fecha    = start.format('YYYY-M-D');
        $fechafin = end.format('YYYY-M-D');

        consultar();

    }
  )  // Fin $('#daterange-btn').daterangepicker(
  

</script>

@endsection <!-- Fin scrip -->
