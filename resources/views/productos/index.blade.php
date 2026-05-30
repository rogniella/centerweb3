@extends('template.main_alta_modal')
@section('titulo','Administración de Productos')
   
@section('contenido')

<form class="form-inline" role="form" onsubmit="return false;">
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Administración de Productos</h3>
            </div>
            <div class="panel-body">

              <div class="form-group">
                <select name="filtro_flia" id="filtro_flia" class="form-control">
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == '' ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                </select>    
                <input type="text" class="form-control" name="filtro1" id="filtro1"  placeholder="Descripción" value="">

                <select id="filtroMarca" name="filtroMarca" class="form-control">
                    <option value="T">[Todas Marcas]</option>
                </select>

              </div>

              <div class="form-group">
                <button type="button" class="btn btn-default pull-right" id="form-search-btn" onclick="consultar()">Buscar</button>
              </div>

              <br> <!-- Salto linea--> 

              <div class="form-group">
                 <a class="mas-filtros-divider" data-toggle="collapse" href="#collapseFiltro" role="button" aria-expanded="false" aria-controls="collapseFiltro">
                    <span class="line"></span>
                    <span class="arrow">▼</span>
                    <span class="divider-label divider-label-contracted">Más Opciones...</span>
                    <span class="divider-label divider-label-expanded">Menos Opciones</span>
                    <span class="line"></span>
                 </a>

                 <div class="collapse" id="collapseFiltro">

                    <div class="form-group">
                        <select id="filtroEstado" name="filtroEstado" class="form-control">
                          <option value="T">[Todos]</option>
                          <option value="A" selected>Activos</option>
                          <option value="I">Inactivos</option>
                        </select>
                        <select id="filtroStock" name="filtroStock" class="form-control">
                          <option value="T" selected>[Stock Todos]</option>
                          <option value="C">Con Stock</option>
                          <option value="S">Sin Stock</option>
                        </select>
                        <input type="number" class="form-control" name="mes_ventas" id="mes_ventas" placeholder="Meses de Ventas (Ej: 12)" title="Cantidad de Meses de Ventas"  value="">
                        <input type="text" class="form-control" name="filtro2" id="filtro2" placeholder="Buscar por precio" title="Solo aquellos que superan el precio especificado
                        "  value="">
                        <input type="date" class="form-control" name="filtro3" id="filtro3"  title="Solo aquellos con Fecha Última Actualización menores a la especificada"  value="">
                    </div>

                    <hr style="margin: 15px 0;">

                    <div class="row">
                      <div class="col-sm-12">
                        <button type="button" class="btn btn-info btn-sm" onclick="genera_pedido()">
                          <i class="fa fa-truck"></i> Generar Pedido
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="accion1()">
                          <i class="fa fa-compress"></i> Consolida Códigos
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="precios_masivo()">
                          <i class="fa fa-usd"></i> Modificar Precios
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="estadisticas()">
                          <i class="fa fa-bar-chart"></i> Estadisticas
                        </button>
                      </div>
                    </div>

                  </div> <!-- Fin mas filtro -->

              </div> <!-- From Group -->

            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

  
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">     
            <div id="toolbar">

              <div class="form-group">
              <label>&nbsp</label>
              <button type="button" class="pull-right btn btn-success"
                id="form-search-btn" onclick="showEditModalProducto(false, 0)">
                <i class="glyphicon glyphicon-plus"></i> Nuevo 
              </button>
              </div>
              <label>&nbsp</label>
            <!-- 
            <div class="form-group">
                <button type="button" class="btn btn-primary pull-right" id="form-search-btn" onclick="cambia_codigo()">Cambiar Codigo/ Act Precio
                </button>
            </div>
        
            -->
            </div>
          <table id="mitabla"
           data-toggle="table"
           data-toolbar="#toolbar"
           data-toolbar-align="right"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""
           data-show-footer="true"     
           data-click-to-select="true" 
           data-maintain-selected="true"
           data-id-field="prod_idweb"      
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th data-field="state" data-checkbox="true"></th>
            <th data-field="prod_id" data-halign="center" data-align="center" data-sortable="true"
            >C&oacute;digo </th>
            <th data-field="prod_categoria"  data-sortable="true">Categoria</th>
            <th data-field="marca"  data-sortable="true">Marca</th>
            <th data-field="prod_descripcion" data-footer-formatter="idTotal" data-sortable="true"> Detalle</th>
            <th data-field="prod_precio" data-halign="center" data-align="right" data-sortable="true" data-sorter="priceSorter"
            data-footer-formatter="mtoFormatter" >Precio</th>
            <th data-field="stock01" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Stock Suc 1</th>
            <th data-field="venta01" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Ventas Suc 1</th>
            <th data-field="stock02" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Stock Suc 2</th>
            <th data-field="venta02" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Ventas Suc 2</th>
            <th data-field="prod_fecultman" data-halign="center" data-sortable="true">Ult.Mant</th>
            <th data-field="prod_idweb" data-halign="center"  data-align="center" data-formatter="opcionesFormatter">Opciones</th>
            <!--  <th>Hab/Desc</th> Col 6 Oculta  Cod_Haber_Descuento -->
          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->


  <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <select name="groupby" id="groupby" class="form-control">
                        <option value="" >Agrupar por..</option>
                        <option value="marca">Marca</option>
                        <option value="Prod_Categoria">Categoria</option>
                    </select>
                </div>      
        </div>      

    </div>   <!-- /.Row -->
      <div class="row">

            <div class="col-md-12">
              <table id="tabla_tot"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="success" data-field="label" data-halign="center"  data-align="left" data-sortable="true">AGRUPADO</th>
                <th data-field="suc1" data-sortable="true" data-halign="center" data-align="right">Stock Suc 1</th>
                <th data-field="suc2" data-sortable="true" data-halign="center" data-align="right">Stock Suc 2</th>
                <th data-field="total" data-sortable="true" data-halign="center" data-align="right">Stock Total</th>
                <th data-field="value" data-sortable="true" data-halign="center" data-align="right">%</th>

                <th data-field="vta1" data-sortable="true" data-sorter="priceSorter" data-halign="center" data-align="right">Ventas Suc 1</th>
                <th data-field="vta2" data-sortable="true" data-sorter="priceSorter" data-halign="center" data-align="right">Ventas Suc 2</th>
                <th data-field="total_vta" data-sortable="true" data-sorter="priceSorter" data-halign="center" data-align="right">Ventas Total</th>
                <th data-field="value_vta" data-sortable="true" data-halign="center" data-align="right">%</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
  </div>   <!-- /.Row -->
  <br>
  <br>

</form> 

<!-- Formulario precios para Productos con listas en u$s -->
<div id="userModal_precios" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Mantenimieto de Precios de Productos </h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-3 col-md-3">
                <label>Moneda:</label>
                <br>
                <select name="listaMoneda" id="listaMoneda" class="form-control" required>
                        @foreach($listaMoneda as $key => $value)
                            <option value="{{ $key }}" {{ $key == '' ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Precio Venta</label>
                <br>
                <input class="form-control text-right" type="number"  id="precio" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Precio Venta Min</label>
                <br>
                <input class="form-control text-right" type="number"  id="precio2" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Costo</label>
                <br>
                <input class="form-control text-right" type="number"  id="costo" value="">
            </div>

            <input type="hidden" id="precio_id" name="precio_id" >

          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgErrorVentanaPrecio" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  class="btn btn-success" onclick="registrar_precio()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de Precios en u$s -->

<!-- Formulario precios Masivos -->
<div id="userModal_precios_masivo" class="modal fade" data-backdrop="static">
  <div class="modal-dialog" role="document">
    <form method="post" autocomplete="off" class="form-horizontal"   role="form" >
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Modificación Masiva de Precios </h4>
        </div>
        <div class="modal-body">
           <div class="form-group">
                <label class="control-label col-md-3 text-right">Aplica:</label>
                <div class="col-md-4">
                 <select id="precio_aplica" name="precio_aplica" class="form-control">
                  <option value="1">Precios</option>
                  <option value="2">Costos</option>
                  <option value="3">Ambos</option>
                 </select>
                </div>
           </div>
           <div class="form-group">
            <label class="control-label col-md-3 text-right">Tipo:</label>
                <div class="col-md-5">
                 <select id="precio_tipo" name="precio_tipo" class="form-control">
                  <option value="1">Incremento Porcentual</option>
                  <option value="2">Incremento Fijo</option>
                  <option value="3">Monto Fijo</option>
                  <option value="4">Monto Mínimo</option>
                 </select>
                </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3">Recargo/Descuento:</label>
            <div class="col-md-4">
                <input class="form-control text-right" type="number" id="precio_recargo" name="precio_recargo"  value="">
            </div>
          </div>
          <div class="form-group">
                <label class="control-label col-md-3 text-right">Redondeo:</label>
                <div class="col-md-4">
                 <select id="precio_redondeo" name="precio_redondeo" class="form-control">
                  <option value="1">nnnn,n0</option>
                  <option value="0">nnnn,00</option>
                  <option value="-1">nnn0,00</option>
                  <option value="-2" selected>nn00,00</option>
                  <option value="5">nn00/50</option>
                 </select>
                </div>
          </div>

 
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgErrorVentanaPrecio" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  class="btn btn-success" onclick="registrar_precio_masivo()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de Precios -->



<!-- Formulario de Alta/ Modificacion -->
<?php include( base_path() . "/resources/views/productos/campos.php");?>
@include('productos.alta_modif')

@endsection <!-- Fin Contenido -->

@section('scrip')

<script>
   

  var $table = $('#mitabla'); // Tabla principal

  $("#userModal_precios").draggable({
      handle: ".modal-header"
  });

  $("#userModal_precios_masivo").draggable({
      handle: ".modal-header"
  });

  // Formatea linea de Totales de la Grilla
  function idTotal() {
    return 'T O T A L E S'
  }

  function nameFormatter(data) {
    // Catidad de filas
    return data.length
  }

  function mtoFormatter(data) {
    // Calculo el todal 
    var field = this.field
    return '$' +  numberFormat(  data.map(function (row) {
        cantidad = Number(row['stock01']) + Number(row['stock02'])
        //console.log  ( row[field] ,row['stock01'] , row['stock02'] ,cantidad )
        mto = numberFormatBd ( row[field] )
        return +  ( mto * cantidad )
      }).reduce(function (sum, i) {
        return sum + i
      }, 0)  )
  }

  function cantidadFormatter(data) {
      var field = this.field
      return  numberFormat( data.map( function (row) {    
          return  + row[field] 
        }).reduce(function (sum, i) {
          return sum + i
      }, 0)  )
  }

    $(document).ready(function(){
        buscarMarcas( $("#filtro_flia").val() , 'S' , '');

        $('select[name="groupby"]').change(function () {
            console.log($(this).val())
            generarTotales( $('#groupby').val() , $('#mitabla') , $('#tabla_tot')   )  // Genera totales por Codigo Col =0 Moneda Col =2
        });    
       
  
    });      
  
function generarTotales(columna , $table , $tableTotales  )  {

  // En columna tiene que ir como se llama en la grilla
  // Recorro la tabla para actualizar los Totales
  // var $table = $('#tabla_ingresados'); 
  //  var $tableTotales = $('#tabla_tot_ingresos');

  $table.bootstrapTable('refreshOptions', {
            groupBy: true,
            groupByField: columna
          });

  var tablajson = $table.bootstrapTable('getData');

  var cant  = 0 ;
  var cant2  = 0 ;
  var cant_total = 0;
  var cant_total2 = 0;
  var vta  = 0 ;
  var vta2  = 0 ;
  var vta_total = 0;
  var vta_total2 = 0;

  var TotalesCodigo = [];
  var TotalesCantidad = [];
  var TotalesCantidad2 = [];
  var TotalesVta = [];
  var TotalesVta2 = [];
  var pos=0;

for (var fila in tablajson) {
  cant = Number(tablajson[fila]['stock01']) 
  cant_total = cant_total + cant 
  cant2 = Number(tablajson[fila]['stock02']) 
  cant_total2 = cant_total2 + cant2 
  vta = Number(tablajson[fila]['venta01']) 
  vta_total = vta_total + vta 
  vta2 = Number(tablajson[fila]['venta02']) 
  vta_total2 = vta_total2 + vta2 
  // vectores acumuladores
  pos = TotalesCodigo.indexOf(tablajson[fila][columna]) 
  if (  pos == -1 ) {
      pos = TotalesCodigo.length
      TotalesCodigo.push (tablajson[fila][columna])
      TotalesCantidad.push(0)
      TotalesCantidad2.push(0)
      TotalesVta.push(0)
      TotalesVta2.push(0)
      TotalesCantidad[pos] = 0
      TotalesCantidad2[pos] = 0
  } 
  TotalesCantidad[pos] =  TotalesCantidad [pos] + parseFloat( cant)
  TotalesCantidad2[pos] =  TotalesCantidad2 [pos] + parseFloat( cant2)
  TotalesVta[pos] =  TotalesVta [pos] + parseFloat( vta)
  TotalesVta2[pos] =  TotalesVta2 [pos] + parseFloat( vta2)
}

var DataTabla = [];
var indiceT = 0 
var tot  = 0 ;
var acumulado_tot = cant_total + cant_total2  
var acumulado_vta = vta_total + vta_total2  
 
TotalesCodigo.forEach(function (elemento, indice, array) {
   indiceT = indiceT + 1
   procentaje = 0
   tot =   TotalesCantidad[indice] + TotalesCantidad2[indice]
   if (acumulado_tot != 0) {
     procentaje = tot / acumulado_tot * 100
   }
   procentaje_vta = 0
   tot_vta =   TotalesVta[indice] + TotalesVta2[indice]
   if (acumulado_vta != 0) {
     procentaje_vta = tot_vta / acumulado_vta * 100
   }
   DataTabla[indice] = {
     suc1:  formatearNumeroConSeparadorDeMiles(TotalesCantidad[indice] ,0) ,
     suc2:  formatearNumeroConSeparadorDeMiles(TotalesCantidad2[indice] ,0) ,
     total:  formatearNumeroConSeparadorDeMiles(tot ,0) ,
     value:  procentaje.toFixed(2) ,
     vta1:  formatearNumeroConSeparadorDeMiles(TotalesVta[indice] ,0) ,
     vta2:  formatearNumeroConSeparadorDeMiles(TotalesVta2[indice],0) ,
     total_vta:  formatearNumeroConSeparadorDeMiles(tot_vta ,0) ,
     value_vta:  procentaje_vta.toFixed(2) ,
     label:   TotalesCodigo[indice] 
    }
}) // Fin recorro Vec Acumulador
if (acumulado_tot != 0) {
  DataTabla[indiceT] = {
     suc1:  formatearNumeroConSeparadorDeMiles(cant_total ,0) ,
     suc2:  formatearNumeroConSeparadorDeMiles(cant_total2 ,0),
     total:  formatearNumeroConSeparadorDeMiles(acumulado_tot ,0),
     value:  "100" ,
     vta1:  formatearNumeroConSeparadorDeMiles(vta_total,0),
     vta2:  formatearNumeroConSeparadorDeMiles(vta_total2,0) ,
     total_vta:  formatearNumeroConSeparadorDeMiles(acumulado_vta ,0),
     value_vta: "100",
     label: "<b> T O T A L E S </b>"
  }
}
  $tableTotales.bootstrapTable('load', DataTabla);

} // Fin GenerarTotales                                  




    function searchByFormdata(){
      consultar()
    }
  



    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro_flia: $('#filtro_flia').val(), filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val() , filtro3: $('#filtro3').val() , filtroEstado: $('#filtroEstado').val() , filtroStock: $('#filtroStock').val() , filtroMarca: $('#filtroMarca').val() , mes_ventas: $('#mes_ventas').val()  },
            url:   'buscar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales( $('#groupby').val() , $('#mitabla') , $('#tabla_tot')   )  // Genera totales por Codigo Col =0 Moneda Col =2
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
    

    // Funcion de carga de Tablas
    function registrar_precio_masivo()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro_flia: $('#filtro_flia').val(), filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val() , filtro3: $('#filtro3').val() , filtroEstado: $('#filtroEstado').val() , filtroStock: $('#filtroStock').val() , filtroMarca: $('#filtroMarca').val() ,
                    precio_aplica:$('#precio_aplica').val(),precio_tipo:$('#precio_tipo').val() , precio_recargo:$('#precio_recargo').val(), precio_redondeo:$('#precio_redondeo').val()    },
            url:   'registrar_precio_masivo',
            type:  'get',
            success: function(data){
                // Mens OK , y cerrar ventana modal
                $("#userModal_precios_masivo").modal("hide");
                Swal.fire({
                  type: 'info',
                  title: 'Ok  Actualización de los Productos '  ,
                  text: 'Cantidad de Productos Actualizados:' + data.cantidad,
                  footer: ''
                })       
                consultar() //refresca
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()

    function accion1()  {
        //Para Juntar codigos que ya no se utilizan

        var selectedRows = $('#mitabla').bootstrapTable('getSelections');
        // Obtener solo la columna "id"
        var selectedIds = selectedRows.map(row => row.prod_id);
        //console.log("Seleccionados:", selectedRows);
        let cod_destino = prompt("Ingrese Código Destino:");

 $.ajax({
      dataType: "json",
      data: { familia: $('#filtro_flia').val() , selectedRows: selectedIds , cod_destino: cod_destino },
      url:   'consolida_codigo',
      type:  'get',
      success: function(data){
          msgerror( data.results);
      },
      error: function(xhr,err){ 
          if (xhr.readyState == 401) { // Si se desconecto
             document.location.reload(); // Para que recargue y pida login
          }else{
             msgerror( xhr.responseText);
          }    
      } // Fin si hay error
  }); // Fin llamado Ajax
} // Fin consultar()

    function genera_pedido()  {



      // LLama Generacion Pedido de Pedidos
       // ------------------------------------ 
       //if ( $('#filtro_flia').val() != "CRI") {
       //   msgerror( "Esta Opción es solo para el rubro Cristales")
       //   return;
       //}     
       $.ajax({
            dataType: "json",
            data: { filtro_flia: $('#filtro_flia').val(), filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val() , filtro3: $('#filtro3').val() , filtroEstado: $('#filtroEstado').val() , filtroMarca: $('#filtroMarca').val() , mes_ventas: $('#mes_ventas').val()  },
            url:   'genera_pedido',
            type:  'get',
            success: function(data){
                window.open( data.redirec, 'Archivo Generado Correctamente', '_blanck' );
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
 
    function estadisticas()  {

      ruta = '../estadisticas/rubro?sucursal=0&id_producto=""&tipoinf=' + $('#filtro_flia').val() + '&desc_producto=' +  $('#filtro1').val(); 

      window.open(ruta, '', '_blanck');

    } // Fin estaditicas()
 
    // Funcion de carga de Tablas
    function cambia_codigo()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro_flia: $('#filtro_flia').val(),filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val() , filtro3: $('#filtro3').val() , filtroEstado: $('#filtroEstado').val() },
            url:   'cambia_codigo',
            type:  'get',
            success: function(data){
               msgerror( data.results);
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
   } 

   function opcionesFormatter(value,columnas) {
      var Id = value;
      var familia = columnas.prod_familia.substring(0,3);
      var codigo = columnas.prod_id;

      var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar registro" onclick="showEditModalProducto(true,\''+ Id +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';

          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-primary  btn-xs"'+
                  'title="Estadisticas" onclick="editReg(\''+ familia +'\',\''+ codigo +'\')">'  +
                  '<i class="fa fa-info-circle" aria-hidden="true"></i>'+
              '</button>';

      // Opcion Eliminar  Solo si esta conectado y es ADM -->
      @if(Auth::user())
        @if(Auth::user()->perfil_id == 'ADM')
          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-success btn-xs"'+
                  'title="ADM de Precios" onclick="precios(\''+ Id +'\')">'+
                  '<i class="fa fa-usd" aria-hidden="true"></i>'+
              '</button>';
        @endif
        @if(Auth::user()->perfil_id == 'ADMFALTA')
          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-danger btn-xs"'+
                  'title="Eliminar registro" onclick="deleteReg(\''+ Id +'\')">'+
                  '<i class="glyphicon glyphicon-trash" aria-hidden="true"></i>'+
              '</button>';
        @endif
      @endif

      return opciones;
  }
  

  function editReg($familia,$id_producto) {

//      ruta = '../productos/edit?id=' + $id 

//      $familia = 'REC';
//      $id_producto = '2020';
      ruta = '../estadisticas/rubro?sucursal=0&id_producto=' + $id_producto + '&tipoinf=' + $familia + '&desc_producto='; 

      window.open(ruta, '', '_blanck');

  }


    // Cambio algun Select 
    $(function () {            
        $('select[name="Prod_Familia"]').change(function () {
           buscarMarcas( $("#Prod_Familia").val() , 'N' , '');
        });    
        $('select[name="filtro_flia"]').change(function () {
           buscarMarcas( $("#filtro_flia").val() , 'S' , '');
        });    
    });
    //me dejo de funcionar ??  $("#Prod_Familia11").on("change", buscarMarcas(''));


  function precios($id) {

      // Busca los valores que ya tiene
      $.ajax({
          dataType: "json",type:  'get', data: { idprod: $id},
          url:  'lee_precio',            
          success: function(data){
              $('#listaMoneda').val(data.idLista);
              $('#precio').val(data.precio);
              $('#precio2').val(data.precio2);
              $('#costo').val(data.costo);
              $('#precio_id').val($id);
              $("#userModal_precios").modal("show")
                // Cuando termina de mostrarse, selecciono el 1re campo.
                .on("shown.bs.modal", function(e) {
                  $("#listaMoneda").select(); 
              });                    
          },
          error:  function(xhr,err){ 
                        if (xhr.status == 401) { // Si se desconecto
                            document.location.reload(); // Para que recargue y pida login
                        }else{
                            msgerror( xhr.responseText);
                        }    
          } // Fin si hay error
      }); // Fin llamado Ajax

  }

  function precios_masivo(  )  {

      $("#userModal_precios_masivo").modal("show")
                // Cuando termina de mostrarse, selecciono el 1re campo.
                .on("shown.bs.modal", function(e) {
                  $("#listaMoneda").select(); 
              });                    
  }


    function registrar_precio(  )  {

        // Boton Aceptar de la Ventana Ventas

        $.ajax({
            global: false,
            dataType: "json",
            data: { 
                     idprod: $("#precio_id").val()
                    , idlista: $("#listaMoneda").val()
                    , precio: $("#precio").val()
                    , precio2: $("#precio2").val()
                    , costo: $("#costo").val()
                },
            url:   'graba_precio',
            type:  'get',
            success: function(data){
                  // Mens OK , y cerrar ventana modal
                    $("#userModal_precios").modal("hide");
                    muestroMsg(data.msg,1000);
                    consultar() //refresca

            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin 

</script>
@endsection <!-- Fin scrip -->