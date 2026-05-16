@extends('template.informes')
@section('titulo','Movimientos Detallados Por Rubro')
   
@section('contenido')

<?php 

    if ($_GET) {
        $sucursal = $_GET["sucursal"];
        $INF_ID = $_GET["tipoinf"];
        $operacion = $_GET["operacion"];
        $id_producto = $_GET["id_producto"];
        $desc_producto = $_GET["desc_producto"];
       // $param_codigos = explode(",", $codigos);
        $anio = $_GET["anio"];
        $mes = $_GET["mes"];
        $diafin = date("d",(mktime(0,0,0,$mes+1,1,$anio)-1));
    }else{
        $sucursal = "";
        $id_producto = "";
        $desc_producto = "";
    }    
       
    
?>

<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title" id="titulo" >Consulta por movimientos Detallados segun Rubro</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                      <label class="control-label">Sucursal:</label>
                <select name="sucursal_inf1" id="sucursal_inf1" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    

                </div>
                <div class="form-group">
                      <label class="control-label">Rubro:</label>
                      <select id="tipo_inf1" name="tipo_inf1" class="form-control">                        
                          <?php $FLIA_ID = $INF_ID; ?>
                          @include('common.combo_familia')
                      </select>
                </div>  
                <div class="form-group">
                        <label class="control-label">Operación:</label>
                        <select id="operacion_inf1" name="operacion_inf1" class="form-control"> 
                            <option value= 'V' selected> Ventas</option>
                            <option value= 'C'> Compras</option>
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
                      <label class="control-label">Descripción Articulo:</label>
                      <input class="form-control" type="text" value="<?= $desc_producto ; ?>"  id="desc_producto" placeholder="Buscar Descripcion Articulo">
                </div>  

                <div class="form-group">
                      <label class="control-label">Articulo:</label>
                      <input class="form-control" type="text" value="<?= $id_producto ; ?>"  id="id_producto" placeholder="Buscar Articulo">
                      <label id="descrip_producto"> </label>
                </div>  
          
                <button type="button" id="btnconsulta" onClick="btn_consulta()" class="btn btn-primary pull-right">Consultar</button>



 
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
            <th data-field="mov_sucursal" data-halign="center"  data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="prod_descripcion" data-halign="center" data-align="left" data-sortable="true">Producto</th>
            <th data-field="mov_fecmov" data-halign="center" data-align="center" data-sortable="true">Fecha</th>
            <th data-field="mov_operacion" data-halign="center" data-align="center" data-sortable="true">Operación</th>
            <th data-field="mov_cantidad" data-halign="center" data-align="right" data-sortable="true">Cantidad</th>
            <th data-field="mov_precio" data-halign="center" data-align="right" data-sortable="true">Monto $</th>
            <th data-field="mov_motivo" data-sortable="true">Detalle</th>
            <!--  <th>Hab/Desc</th> Col 6 Oculta  Cod_Haber_Descuento -->
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
                <th data-field="label" data-halign="center"  data-align="left" data-sortable="true">Producto </th>
                <th data-field="cantidad" data-halign="center" data-align="right" data-sortable="true">Cantidades</th>
                <th data-field="mtos" data-halign="center" data-align="right" data-sortable="true">Montos</th>
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

@endsection <!-- Fin Contenido -->

@section('scrip')

<script>


    var $table = $('#mitabla'); // Tabla principal

    // Funcion de carga de Tablas
    function consultar($fecha,$fechafin)  {
     	 // LLama a 2da pagina con la logica de la busqueda
	     // ------------------------------------------------      
        $operacion = $('#operacion_inf1').val();
        $sucursal = $('#sucursal_inf1').val();
        $id_producto = $('#id_producto').val();
        $desc_producto = $('#desc_producto').val();

        $('#titulo').html('Consulta movimientos Detallado del ' + $fecha + ' al ' + $fechafin);
        $tipoinf = $('#tipo_inf1').val()
       	$.ajax({
            dataType: "json",
            data: { sucursal: $sucursal, tipo_inf: $tipoinf, operacion: $operacion, id_producto: $id_producto , desc_producto: $desc_producto ,  fecha: $fecha, fechafin: $fechafin},
            url:   'infrubro_detalle_proceso',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales() // Genera totales por Producto
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

    function btn_consulta() {
      consultar($fecha,$fechafin);    
    }        

    function generarTotales()  {

        // Recorro la tabla para actualizar los Totales
        //alert(JSON.stringify($table.bootstrapTable('getData')));
        var tablajson = $table.bootstrapTable('getData');
        var mto;
        var mtototal = 0;
        var cantidadtotal = 0;
        var procentaje=0;

        var DataGrafico = [];
        var TotalesCodigo = [];
        var TotalesDescri = [];
        var TotalesMontos = [];
        var TotalesCantidad = [];
        var pos=0;

        for (var fila in tablajson) {
            // Si queremos recorrer por columnas
            //  for (var col in tablajson[fila]) {
            //    console.log( tablajson[sfila][col])                    
            //  }
            mto = tablajson[fila].mov_precio // Los importes estan en la col 
            mto = mto.replace(".","") //primer paso: fuera puntos
            // vectores acumuladores
            elemento = tablajson[fila].prod_descripcion // Si es por Codigo va la descri del codigo
            descripcion = tablajson[fila].prod_descripcion // Es por moneda

            pos = TotalesCodigo.indexOf(elemento )
            if (  pos == -1 ) {
                pos = TotalesCodigo.length
                TotalesCodigo.push (elemento)
                TotalesMontos.push(0)
                TotalesCantidad.push(0)
                TotalesDescri.push(descripcion) 

                //console.log('ele' + elemento)
                //console.log(tablajson[fila].mov_precio)
            } 
            TotalesMontos[pos] =  TotalesMontos [pos] + parseFloat(mto)
            TotalesCantidad[pos] =  TotalesCantidad [pos] + parseFloat(tablajson[fila].mov_cantidad )
            mtototal = mtototal +  parseFloat(mto)
            cantidadtotal = cantidadtotal +  parseFloat(tablajson[fila].mov_cantidad)

        }

        var $tableTotales = $('#tabla_inf'); 
        var filas = 0

        TotalesCodigo.forEach(function (elemento, indice, array) {
            procentaje = TotalesMontos[indice].toFixed(0) / mtototal * 100
            DataGrafico[indice] = {
               mtos:  TotalesMontos[indice].toFixed(0) ,
               cantidad:  TotalesCantidad[indice].toFixed(0) ,
               value:  procentaje.toFixed(2) ,
               label:  TotalesDescri[indice]
            }
            filas = indice
        })
        areachart1.setData(DataGrafico);
        if (mtototal > 0) {
            DataGrafico[filas+1] = {
               mtos:  "<b>" + mtototal.toFixed(0) + "</b>" ,
               cantidad:  "<b>" + cantidadtotal.toFixed(0) + "</b>" ,
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
         $fecha = '<?= date("Y-m-d"); ?>';
         $fechafin = '<?= date("Y-m-d"); ?>';
         $mes = '<?= $mes; ?>';

         if ( $mes > 12 )  {
          $fecha = '<?= date("Y-m-d",(mktime(0,0,0,1,1,$anio)));?>';
          $fechafin = '<?= date("Y-m-d",(mktime(0,0,0,12,31,$anio)));?>';
         } else {
          $fecha = '<?= date("Y-m-d",(mktime(0,0,0,$mes,1,$anio)));?>';
          $fechafin = '<?= date("Y-m-d",(mktime(0,0,0,$mes,$diafin,$anio)));?>';
         }

        $('#operacion_inf1').val('<?= $operacion;?>');
        
  
    	 consultar($fecha,$fechafin);
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
        var fechaInicial = start.format('YYYY-M-D');
        var fechaFinal = end.format('YYYY-M-D');
    	consultar(fechaInicial,fechaFinal);
    }
  )  // Fin $('#daterange-btn').daterangepicker(
  

</script>

@endsection <!-- Fin scrip -->
