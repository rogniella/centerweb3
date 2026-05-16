@extends('template.informes')
@section('titulo','Ordenes de Trabajo')
   
@section('contenido')

<?php 
    // Inicio los parametros de Filtrado
    $sucursal = "0";
    $tipo_ot = "";
    $fecha = date("Y-m-d");
    $fecha_fin = date("Y-m-d");
    if ($_GET) {
        $sucursal = $_GET["sucursal"];
        $tipo_ot = $_GET["tipoot"];
        $fecha = $_GET["fecha"];
        $fecha_fin = $_GET["fechafin"];
    }    
?>

<form class="form-inline" role="form" >
    <!-- Panel Del Titulo y Filtros -->
    <div class="panel panel-info">         
        <div class="panel-heading">
              <h3 class="panel-title">Consulta de Ordenes de Trabajo</h3>
        </div>
        <div class="panel-body">
                <div class="form-group">
                    <label class="control-label">Tipo Informe:</label>
                    <select  name="tipo_informe" id="tipo_informe"  class="form-control">
                      <option value= "">Completo </option>
                      <option value= "A">Atrasadas </option>
                      <option value= "P">Pendientes de Entrega </option>
                    </select>
                </div>    
                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <select name="Ot_Sucursal" id="Ot_Sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == 0 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>    
                <div class="form-group">
                    <label class="control-label">Fechas:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn">
                      <span>
                        <i class="fa fa-calendar"></i> Rango de fecha
                      </span>
                        <i class="fa fa-caret-down"></i>
                    </button>              
                    </div>
              </div>

              <div class="form-group">
                    <label class="control-label">Tipo OT:</label>
                    <select name="tipoot" id="tipoot" class="form-control">
                        <option value="" >[Todos]</option>
                        <option value="A" >Anteojos</option>
                        <option value="C">Celulares</option>
                        <option value="L">Lentes Contacto</option>
                        <option value="R">Reparaciones</option>
                        <option value="G">Garantías</option>
                    </select>

                    <label class="control-label">Estado:</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="" >[Todos]</option>
                        @include('common.combo_otestados')
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
            <th data-field="Ot_Sucursal" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="fecha" data-halign="center" data-align="center" data-sortable="true" data-sorter="dateSorter">Fecha</th>
            <th data-field="fechaprometida" data-halign="center" data-align="center" data-sortable="true" data-sorter="dateSorter">Prometido</th>
            <th data-field="Ot_Id" data-formatter="fotmatoColSel"  data-halign="center" data-align="center" data-sortable="true">OT Nro </th>
            <th data-field="Tipo" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="Descripcion" data-align="center"  data-sortable="true">Estado</th>
            <th data-field="Cli_ApeNom"  data-formatter="fotmatoColSel" data-halign="center" data-align="left" data-sortable="true">Cliente</th>
            <th data-field="Ot_Vendedor" data-halign="center" data-align="center" data-sortable="true">Vendedor</th>
            <th data-field="Ot_Precio" data-halign="center" data-align="right" data-sortable="true">Precio</th>
            <th data-field="Ot_Saldo" data-halign="center" data-align="right" data-sortable="true">Saldo</th>
            <th data-field="Ot_ObrId" data-sortable="true"> Obra Social</th>    
            <th data-field="Ot_Tipo"  data-visible="false" >aux</th>    
            <th data-field="Ot_IdCli" data-visible="false" >aux</th>    
            <th data-field="Ot_idWEB" data-visible="false" ></th>
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
                        <option value="Ot_Sucursal" >Sucursal</option>
                        <option value="Tipo" >Tipo</option>
                        <option value="Ot_Vendedor">Vendedor</option>
                        <option value="Ot_Estado">Estado</option>
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
      if ( args [0] == 'Ot_Id'){  // Nombre Columna                          
         // Busco los datos de la OT o Comprobante y despliega pantall Modal
         consulta_comprobante("ot_idweb", args [2].Ot_idWEB, 0)
      }; // Fin Clik OT

      if ( args [0] == 'Cli_ApeNom'){  // Nombre Columna                          
        // Busco los datos del Cliente
        //console.log( 'Otras Columnas:' , args [2]) // Valor de la columna
        $.ajax({
            dataType: "html",
            data: {  id: args [2].Ot_IdCli  },
            url:   '../clientes/consulta',
            type:  'get',
            success: function(data){
              $('#titulo_consulta').html('Cliente');
              $('#destino').html(data);
              $("#consultaModal").modal("show")
            },
            error:  function(xhr,err){ 
               msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax

      }; // Fin Clik Cliente

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
        $('select[name="tipoot"]').change(function () {
            consultar();
        });
        $('select[name="Ot_Sucursal"]').change(function () {
            consultar();
        });
    });
	
    function consultar()  {
        // LLama a 2da pagina con la logica de la busqueda 
        // ------------------------------------------------ 
        $tipoinforme  = $('#tipo_informe').val();   
        $sucursal  = $('#Ot_Sucursal').val();   
        $tipoot = $('#tipoot').val()  
        $estado = $('#estado').val()  
        $.ajax({
            dataType: "json",
            data: { tipoinforme:$tipoinforme, sucursal: $sucursal, tipoot: $tipoot, fecha: $fecha , fechafin: $fechafin,estado: $estado },
            url:   'buscar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales('Tipo') // Genera totales por Tipo Col =1
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
         $fecha = '<?= $fecha; ?>';
         $fechafin = '<?= $fecha_fin; ?>';
         document.getElementById("Ot_Sucursal").value = '<?= $sucursal; ?>';
         document.getElementById("tipoot").value = '<?= $tipo_ot; ?>';
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
            mto = tablajson[fila]['Ot_Precio'] // Los importes estan en la col 3
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