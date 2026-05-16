@extends('template.informes')
@section('titulo','Movimientos de Productos')
   
@section('contenido')

<?php 
    // Inicio los parametros de Filtrado
    $sucursal = "0";
    $familia = "";
    $operacion = "";
    $fecha = date("Y-m-d");
    $fecha_fin = date("Y-m-d");
    $id_producto = "";
    $desc_producto = "";
    $cod_cero = ""; // Casos de los los armasones de los clientes
    $mes = 0;
    $anio = 0;
    $diafin =0;

    if ($_GET) {
        $sucursal = $_GET["sucursal"];
        $familia = $_GET["familia"];
        $operacion = $_GET["operacion"];
        $cod_cero = $_GET["cod_cero"];
        $id_producto = $_GET["id_producto"];
        $desc_producto = $_GET["desc_producto"];
        $anio = $_GET["anio"];
        $mes = $_GET["mes"];
        $diafin = date("d",(mktime(0,0,0,$mes+1,1,$anio)-1));

    }    
?>

<form class="form-inline" role="form" >
    <!-- Panel Del Titulo y Filtros -->
    <div class="panel panel-info">         
        <div class="panel-heading">
              <h3 class="panel-title">Consulta de Movimientos de Productos</h3>
        </div>
        <div class="panel-body">
             <div class="form-group">
                    <label class="control-label">Rubro:</label>
                    <select name="filtro_flia" id="filtro_flia" class="form-control">
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == $familia ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    

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
                    <label class="control-label">Sucursal:</label>
                    <select name="Sucursal" id="Sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>    
                <div class="form-group">
                    <label class="control-label">Operación:</label>
                    <select  name="tipo_operacion" id="tipo_operacion"  class="form-control">
                      <option value= "">[Todas] </option>
                      <option value= "V">Ventas </option>
                      <option value= "C">Compras </option>
                    </select>
                </div>    

                <div class="form-group">
                      <label class="control-label">Descripción Articulo:</label>
                      <input class="form-control" type="text" value="<?= $desc_producto ; ?>"  id="desc_producto" placeholder="Buscar Descripcion Articulo">
                </div>  

                <div class="form-group">
                      <label class="control-label">Articulo:</label>
                      <input class="form-control" type="text" value="<?= $id_producto ; ?>"  id="id_producto" placeholder="Buscar Articulo">
                </div>  
                <div class="form-group">
                    <label class="control-label">Ignorar Cod 0:</label>
                    <select  name="cod_cero" id="cod_cero"  class="form-control">
                      <option value= "">NO</option>
                      <option value= "S">SI </option>
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
            <th data-field="Mov_Sucursal" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="Mov_FecMov" data-footer-formatter="idTotal" data-sortable="true">Fecha-Hora </th>
            <th data-field="Mov_Familia" data-halign="center" data-align="center" data-sortable="true"
             >Familia</th>
            <th data-field="Mov_IdProd" data-halign="center" data-align="center" data-sortable="true"
             >Producto</th>
            <th data-field="marca"  data-sortable="true">Marca</th>
            <th data-field="prod_descripcion" data-halign="center"  data-sortable="true"
             >Desc.Producto</th>
            <th data-field="Mov_Operacion" data-halign="center" data-align="center" data-sortable="true"
             >Operacion</th>
            <th data-field="Mov_Cantidad" data-halign="center" data-align="center" data-sortable="true"
             >Cantidad</th>
            <th data-field="Mov_PrecioUnitario" data-halign="center" data-align="right" data-sortable="true"
             >Precio Unit.</th>
            <th data-field="Mov_Precio" data-halign="center" data-align="right" data-sortable="true"
             >Total</th>
            <th data-field="Mov_TipoOT" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="Mov_IdOT" data-formatter="fotmatoColSel"  data-halign="center" data-align="center" data-sortable="true">OT Nro </th>
            <th data-field="Mov_Motivo" data-sortable="true" >Observación</th>
            <th data-field="Mov_Responsable" data-sortable="true" data-align="center" >Vendedor</th>
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
                        <option value="Mov_Sucursal" >Sucursal</option>
                        <option value="Mov_Operacion" >Tipo Operacion</option>
                        <option value="marca">Marca</option>
                        <option value="Mov_Familia">Familia</option>
                        <option value="prod_descripcion">Producto</option>                        
                        <option value="Mov_Responsable">Vendedor</option>
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


<!-- Formulario Consulta -->
<div class="modal fade" id="consultaModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="formconfirma">
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <div id="titulo_consulta"> </div>
          </div>
          <div class="modal-body">
            <div id="destino"> </div>
          </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Salir</button>
        </div>

      </div>

    </form>
  </div>
</div> <!-- FIN Formulario  -->

@include('common.modal_consulta')



@endsection <!-- Fin Contenido -->

@section('scrip')

<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>

  
    var $fecha;
    var $fechafin;

    $(function() {

        // Si cambia el cmb de Agrupacion
        $('select[name="groupby"]').change(function () {
            console.log($(this).val())
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            generarTotales(  $('select[name="groupby"]').val()  ) // Genera totales segun 
        });    

    });

 	
  var $table = $('#mitabla');


 // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {

    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla

      if ( args [0] == 'Mov_IdOT'){  // Nombre Columna                          
         // Busco los datos de la OT o Comprobante y despliega pantall Modal
         consulta_comprobante(args [2].Mov_TipoOT, args [2].Mov_IdOT, args [2].Mov_Sucursal)
      }; // Fin Clik Id OT

    } // Clik de La tabla    
   }); // Fin Todos los Eventos de la Tabla
 

    $(function () {
        // Si cambia el cmb de Agrupacion
        $('select[name="dropdown"]').change(function () {
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            generarTotales(  $('select[name="dropdown"]').val()  ) // Genera totales segun agrupacion            
        });

        // Si Cambia el cmb de Tipo de Ot
        $('select[name="estado"]').change(function () {
            consultar();
        });
    });
	
    function consultar()  {
        // LLama a 2da pagina con la logica de la busqueda 
        // ------------------------------------------------ 
        $tipo_operacion  = $('#tipo_operacion').val();   
        $suc  = $('#Sucursal').val();   

        $.ajax({
            dataType: "json",
            data: { tipo_operacion:$tipo_operacion, sucursal: $suc,  fecha: $fecha , fechafin: $fechafin,familia: $('#filtro_flia').val(),
              idprod: $('#id_producto').val(),
              cod_cero: $('#cod_cero').val(),
              desc_producto: $('#desc_producto').val() },
            url:   'buscar_movimientos',
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
         $mes = '<?= $mes; ?>';

        if ( $mes > 0 )  {
         if ( $mes > 12 )  {
          // totales, todo el año
          $fecha = '<?= date("Y-m-d",(mktime(0,0,0,1,1,$anio)));?>';
          $fechafin = '<?= date("Y-m-d",(mktime(0,0,0,12,31,$anio)));?>';
         } else {
          $fecha = '<?= date("Y-m-d",(mktime(0,0,0,$mes,1,$anio)));?>';
          $fechafin = '<?= date("Y-m-d",(mktime(0,0,0,$mes,$diafin,$anio)));?>';
         }
        }

        $('#tipo_operacion').val('<?= $operacion;?>');
        $('#cod_cero').val('<?= $cod_cero;?>');

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
            factor = 1
            if(tablajson[fila]['Mov_Operacion'] == 'R'){
               factor = -1 // Si es anulacion
            }
            mto = tablajson[fila]['Mov_Precio']  // Define col Montos
            cantidad = Math.abs( tablajson[fila]['Mov_Cantidad'] ) * factor // Define col Montos
//            cantidad =  tablajson[fila]['Mov_Cantidad']  // Define col Cantidad
            // vectores acumuladores
            pos = TotalesCodigo.indexOf(tablajson[fila][columna]) 
            if (  pos == -1 ) {
                pos = TotalesCodigo.length
                TotalesCodigo.push (tablajson[fila][columna])
                TotalesCantidad.push(0)
                TotalesMontos.push(0)
            } 
            TotalesCantidad[pos] =  TotalesCantidad [pos] + cantidad 
            TotalesMontos[pos] =  TotalesMontos [pos] +  parseFloat(mto) 
            mtototal = mtototal +  parseFloat(mto)
            cantTotal = cantTotal + cantidad
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
