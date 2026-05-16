
@extends('template.main_alta_modal')
@section('titulo','Administración de Publicaciones OnLine')
   
@section('contenido')

<form class="form-inline" role="form" onsubmit="return false;>
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Administración de Publicaciones OnLine</h3>
            </div>
            <div class="panel-body">
              <div class="form-group">

                <select id="filtroEstado" name="filtroEstado" class="form-control">
                  <option value="T">[Todas]</option>
                  <option value="A" selected>Activas</option>
                  <option value="V">Vendidas</option>
                  <option value="P">Pausadas</option>
                </select>

                <input type="text" class="form-control" name="filtroDescri" id="filtroDescri"  placeholder="Descripción" value="">

              </div>

              <div class="form-group">
                <button type="button" class="btn btn-default pull-right" id="form-search-btn" onclick="consultar()">Buscar</button>
              </div>
            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

        <!-- Panel De la Tabla -->
        <div class="panel panel-success">     
            <div id="toolbar">

              <div class="form-group">
              <label>&nbsp</label>
              <button type="button" class="pull-right btn btn-success"
                id="form-search-btn" onclick="showAltaItem()">
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
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th data-field="prod_idweb" data-halign="center"  data-align="center" data-formatter="opcionesFormatter">Opciones</th>
            <th data-field="prod_familia" data-halign="center" data-align="center" data-sortable="true">Familia </th>
            <th data-field="prod_id" data-halign="center" data-align="center" data-sortable="true"
            >C&oacute;digo </th>
            <th data-field="prod_marca2"  data-sortable="true">Marca</th>
            <th data-field="prod_descripcion" data-sortable="true"> Detalle</th>
            <th data-field="prod_precio" data-halign="center" data-align="right" data-sortable="true"
             >Precio Vta Suc</th>
            <th data-field="precio_venta" data-halign="center" data-align="right" data-sortable="true"
             >Precio Publicado</th>
            <th data-field="prod_costo" data-halign="center" data-align="right" data-sortable="true"
            >Costo</th>
            <th data-field="stock01" data-halign="center" data-align="right" data-sortable="true"
             >Stock Suc 1</th>
            <th data-field="stock02" data-halign="center" data-align="right" data-sortable="true"
             >Stock Suc 2</th>
            <th data-field="tienda_name"  data-sortable="true">En Tienda</th>
            <th data-field="tienda_precio"  data-halign="center" data-align="right" data-sortable="true">Precio</th>
            <th data-field="observ"  data-sortable="true">Observación</th>
            <th data-field="id"  data-visible="false" ></th>
            <th data-field="estado"  data-visible="false" ></th>

          </tr>
          </thead>
       </table>

       <h3> Articulos Publicados en Tienda OnLine </h3>

       <table id="tabla_tienda"
           data-toggle="table"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""  
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th data-field="sku" data-halign="center" data-align="center" data-sortable="true"
            >Sku </th>
            <th data-field="name" data-sortable="true"> Detalle</th>
            <th data-field="regular_price" data-halign="center" data-align="right" data-sortable="true"
             >Precio Vta</th>
            <th data-field="stock" data-halign="center" data-align="right" data-sortable="true"
            >Notas</th>
            <th data-field="stock01" data-halign="center" data-align="right" data-sortable="true"
             >Stock Suc 1</th>
            <th data-field="stock02" data-halign="center" data-align="right" data-sortable="true"
             >Stock Suc 2</th>
            <th data-field="tienda_name"  data-sortable="true">En Tienda</th>
            <th data-field="tienda_precio"  data-sortable="true">Precio</th>

          </tr>
          </thead>
       </table>

      </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

</form> 


<!-- Formulario AltaItem -->
<div id="userModal_AltaItem" class="modal fade">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Agregar Articulo </h4>
        </div>
        <div class="modal-body">
            <input type="hidden" name="" id="add_ind_alta" value="">
          <div class="row">
            <div class="col-lg-2 col-md-2">
                <label>Cantidad</label>
                <br>
                <input class="form-control text-right" type="number" name="" id="cantidad" value="1" >
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Familia</label>
                <br>
                <select class="form-control" name="id_familia" id="id_familia">
                    <?php $FLIA_ID = "SOL"; ?>
                    @include('common.combo_familia')
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Articulo</label>
                <br>
                <div class="input-group"> 
                <input class="form-control" type="text" name="" id="id_producto" placeholder="Buscar Articulo">
               </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Descripción </label>
                <br>
                <input class="form-control" type="text" name="" id="descrip_producto" value="" disabled>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Categoria</label>
                <br>
                <input class="form-control" type="text" name="" id="add_categoria" value="" disabled>
            </div>
          </div> <!-- /Fin Row 1 Seleccion de Articulo -->
          <br>
          <div class="row">
            <div class="col-lg-2 col-md-2">
                <label>Costo </label>
                <br>
                <input class="form-control text-right" type="number"  id="add_costo" value="" readOnly>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Precio Venta Suc </label>
                <br>
                <input class="form-control text-right" type="number"  id="add_preciosuc" value="" readOnly>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Precio Venta</label>
                <br>
                <input class="form-control text-right" type="number"  id="add_precio" value="" required/>
            </div>
            <div class="col-lg-4 col-md-4">
                <label>Observación</label>
                <br>
                <input class="form-control" type="text"  id="add_observ" value="">
            </div>
            <input type="hidden" id="add_idweb" name="add_idweb" >

          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgVentanaAddItem1" class="alert alert-warning" align="left" > </div>
            <div id="msgVentanaAddItem" class="alert alert-success" align="left" > </div>
            <div id="msgErrorVentanaAddItem" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  class="btn btn-success" onclick="ingresar_articulo()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de AltaItem -->


<!-- Formulario registraVenta -->
<div id="userModal_registraVenta" class="modal fade">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Registro Venta OnLine de Articulo </h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-3 col-md-3">
                <label>Sucursal Origen:</label>
                <br>
                <select name="venta_sucursal" id="venta_sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == '' ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Precio Venta</label>
                <br>
                <input class="form-control text-right" type="number"  id="venta_precio" value="">
            </div>
            <div class="col-lg-4 col-md-4">
                <label>Observación</label>
                <br>
                <input class="form-control" type="text"  id="venta_observ" value="">
            </div>
            <input type="hidden" id="venta_idweb" name="venta_idweb" >

          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgErrorVentanaVenta" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  class="btn btn-success" onclick="registrar_venta()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de registraVenta -->


<!-- Formulario pausaPublicacion -->
<div id="userModal_pausaPublicacion" class="modal fade">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Pausa Publicacion OnLine de Articulo </h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-5 col-md-5">
                <label>Observación</label>
                <br>
                <select id="pausa_observ" name="pausa_observ" class="form-control">
                  <option value="Se Vendio en Sucursal" selected>Se Vendio en Sucursal</option>
                  <option value="Mal Ingresado" >Mal Ingresado</option>
                  <option value="Otros..">Otros..</option>
                </select>
            </div>
            <input type="hidden" id="pausa_idweb" name="pausa_idweb" >

          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <input type="button"  class="btn btn-success" onclick="pausar_publicacion()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de pausaPublicacion -->

<!-- Formulario de Alta/ Modificacion de Productos-->
<?php include( base_path() . "/resources/views/productos/campos.php");?>
@include('productos.alta_modif')

@endsection <!-- Fin Contenido -->

@section('scrip')

<script>
   

  var $table = $('#mitabla'); // Tabla principal
  var $table_tienda = $('#tabla_tienda');


    function showAltaItem() {
       //$("#add_factor").val( $("#factor").val() )
       $("#msgVentanaAddItem1").hide();
       $("#msgVentanaAddItem").hide();
       $("#msgErrorVentanaAddItem").hide();
       $('#id_producto').val('');
       $('#descrip_producto').val('');
       $('#add_categoria').val('');
       $('#add_observ').val('');
       $("#userModal_AltaItem").modal("show")
            // Cuando termina de mostrarse, selecciono el 1re campo.
            .on("shown.bs.modal", function(e) {
              $("#cantidad").select(); 
        });                    
    }   

    // El ultimo campo automatico el bton aceptar
    $('#add_observ').keyup(function(e) {
            if (e.which == 13) {
                ingresar_articulo();
            }
    });

        // Busqueda Automatica de Productos
        $('#id_producto').typeahead({
            items: 15,
            minLength: 2,
            highlight: true,
            source: function(query, process) {
              var familia = $('#id_familia').val();
              $.ajax({
                  global: false,
                  dataType: "json",
                  data: {},
                  url:   '../productos/buscaproducto?terms='+query+'&familia='+familia+'&estado=T',
                  type:  'get',
                  success: function(data){
                     return process(data);
                  },
                  error:  function(xhr,err){ 
                    msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
                  }
              });
            },
            // Al seleccionar.
            afterSelect: function(item) {
                $('#id_producto').val(item.id);
                $('#add_idweb').val(item.idweb);
                $("#descrip_producto").attr('disabled',true);
                $("#add_categoria").attr('disabled',true);
                $('#descrip_producto').val(item.descripcion);
                $('#add_categoria').val(item.categoria);
                $('#add_costo').val( item.costo ) ;
                $('#add_preciosuc').val(item.precio );
                $('#add_precio').focus();
            }
    }); //Fin Busqueda Producto

    function ingresar_articulo(  )  {

        // Tomo los datos de entrada
          if ($("#id_producto").val() == '') {
            $('#msgErrorVentanaAddItem').html("Debe ingresar el Código del Producto");            
            $("#msgErrorVentanaAddItem").show();
            $('#id_producto').focus();
            return
          }
          if ($("#descrip_producto").val() == '') {
            $('#msgErrorVentanaAddItem').html("Debe ingresar la Descripción");            
            $("#msgErrorVentanaAddItem").show();
            $('#descrip_producto').focus();
            return
          }

        $.ajax({
            global: false,
            dataType: "json",
            data: { 
                     idwebProd: $("#add_idweb").val()
                    , cantidad: $("#cantidad").val()
                    , precio: $("#add_precio").val()
                    , observ: $("#add_observ").val()
                },
            url:   'add_publicaciones',
            type:  'get',
            success: function(data){
                $("#msgVentanaAddItem1").html( '<b>Úlimo Item ingresado: </b>' + $("#id_familia").val() + '-' + $("#id_producto").val()  + '  ' + $("#descrip_producto").val());
                $("#msgVentanaAddItem1").show();

              //  $("#jsTabla").jsGrid("render").done(function() {
              //      console.log("rendering completed and data loaded");
              //  });
                    consultar() //refresca

                    $("#msgErrorVentanaAddItem").hide();
                    $('#id_producto').val('');
                    $('#descrip_producto').val('');
                    $('#add_categoria').val('');
                    $('#add_precio_lista').val('');
                    $('#add_costo').val('');
                    $('#add_precio').val('');
                    $('#add_observ').val('');
                    $('#id_producto').focus();
            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin CargaItems()




    function pausar_publicacion(  )  {

        // Boton Aceptar de la Pausa Publicaciones

        $.ajax({
            global: false,
            dataType: "json",
            data: { 
                     accion: 'P'
                    , idweb: $("#pausa_idweb").val()
                    , observ: $("#pausa_observ").val()
                },
            url:   'regitrar_ventaOnline',
            type:  'get',
            success: function(data){
                  // Mens OK , y cerrar ventana modal
                    $("#userModal_pausaPublicacion").modal("hide");
                    muestroMsg(data.msg,1000);
                    consultar() //refresca

            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin 

    function registrar_venta(  )  {

        // Boton Aceptar de la Ventana Ventas

        $.ajax({
            global: false,
            dataType: "json",
            data: { 
                     accion: 'V'
                    , idweb: $("#venta_idweb").val()
                    , sucursal: $("#venta_sucursal").val()
                    , precio: $("#venta_precio").val()
                    , observ: $("#venta_observ").val()
                },
            url:   'regitrar_ventaOnline',
            type:  'get',
            success: function(data){
                  // Mens OK , y cerrar ventana modal
                    $("#userModal_registraVenta").modal("hide");
                    muestroMsg(data.msg,1000);
                    consultar() //refresca

            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin 


    $(document).ready(function(){
        // Se ejecuta al iniciar
        consultar() // carga los datos 

    });      
  
    function searchByFormdata(){
      consultar()
    }
  
    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: {  filtroDescri: $('#filtroDescri').val() , filtroEstado: $('#filtroEstado').val() },
            url:   'publicaciones2',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                $table_tienda.bootstrapTable('load', data.tienda);

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

   function opcionesFormatter(value,columnas) {

     // console.log ( value,columnas,columnas.id ); 
      var Id = value;
      // Dependiendo del tipo de consulta los botones
    switch ( $('#filtroEstado').val() ) {
    case 'T': // Todas
       switch ( columnas.estado ) {
         case 'A': 
            return 'Activa';
         case 'V': 
            return 'Vendida';
         case 'P': 
            return 'Pausada';
       }     
    case 'P': // Pausadas
      var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar Producto" onclick="showEditModalProducto(true,\''+ columnas.id  +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';
      return opciones;

    case 'A': // Activas
      var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar Producto" onclick="showEditModalProducto(true,\''+ Id +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';

          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-success  btn-xs"'+
                  'title="Registrar Venta" onclick="registraVenta(\''+ columnas.id +'\',\''+ columnas.precio_venta +'\',\'' +  columnas.observ  +  '\')">'+
                  '<i class="fa fa-usd" aria-hidden="true"></i>'+
              '</button>';

      // Pausar  Solo si esta conectado y es ADM -->
      @if(Auth::user())
        @if(Auth::user()->perfil_id == 'ADM')
          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-danger btn-xs"'+
                  'title="Pausar Publicación" onclick="pausarPublicacion(\''+ columnas.id  +'\')">'+
                  '<i class="fa fa-thumbs-down" aria-hidden="true"></i>'+
              '</button>';
        @endif
      @endif
      return opciones;

    } // End switch
  }
  

  function pausarPublicacion($id) {

      $("#pausa_idweb").val($id)
      $("#pausa_observ").val('')

      $("#userModal_pausaPublicacion").modal("show")
            // Cuando termina de mostrarse, selecciono el 1re campo.
            .on("shown.bs.modal", function(e) {
              $("#pausa_observ").select(); 
      });                    

  }

  function registraVenta($id,$precio,$observ) {

      console.log($id,$observ)
      $("#venta_idweb").val($id)
      $("#venta_precio").val($precio)
      if ($observ == 'null') { $observ =''}
      $("#venta_observ").val($observ)

      $("#userModal_registraVenta").modal("show")
            // Cuando termina de mostrarse, selecciono el 1re campo.
            .on("shown.bs.modal", function(e) {
              $("#venta_sucursal").select(); 
      });                    

  }


</script>
@endsection <!-- Fin scrip -->