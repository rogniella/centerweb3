@extends('template.main')
@section('titulo','Administración de Productos')
   
@section('contenido')

<form class="form-inline" role="form" onsubmit="return false;>
 
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

                <select id="filtroEstado" name="filtroEstado" class="form-control">
                  <option value="T">[Todos]</option>
                  <option value="A" selected>Activos</option>
                  <option value="I">Inactivos</option>
                </select>


                <input type="text" class="form-control" name="filtro2" id="filtro2"  placeholder="Precio" value="">              
                <input type="date" class="form-control" name="filtro3" id="filtro3"  placeholder="Fecha Ult Act" value="">              
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
                id="form-search-btn" onclick="showAlta()">
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
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th data-field="prod_familia" data-halign="center" data-align="center" data-sortable="true">Familia </th>
            <th data-field="prod_id" data-halign="center" data-align="center" data-sortable="true"
            >C&oacute;digo </th>
            <th data-field="prod_descripcion" data-footer-formatter="idTotal" data-sortable="true"> Detalle</th>
            <th data-field="prod_precio" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="mtoFormatter" >Monto</th>
            <th data-field="stock01" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Stock Suc 1</th>
            <th data-field="venta01" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Ventas Suc 1</th>
            <th data-field="stock02" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Stock Suc 2</th>
            <th data-field="venta02" data-halign="center" data-align="right" data-sortable="true"
            data-footer-formatter="cantidadFormatter" >Ventas Suc 2</th>
            <th data-field="prod_fecultman" data-halign="center" data-sortable="true">Ult.Mant</th>
            <th data-field="prod_usuultman" data-sortable="true" >Ult.Usuario</th>
            <th data-field="prod_idweb" data-halign="center"  data-align="center" data-formatter="opcionesFormatter">Opciones</th>
            <!--  <th>Hab/Desc</th> Col 6 Oculta  Cod_Haber_Descuento -->
          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

</form> 

<?php
  // Formulario de Alta/ Modificacion 
  // ** NO ME DEJA SACAR DE AQUI, SI USO EN OTRO LADO LAS PANTALLA DE ALTA REPETIR ESTE SEGMENTO 

  $modulo_abm='productos';  

  $campos_pantalla = [
      [ 'name' => 'Prod_CodBarra'],
      [ 'name' => 'Prod_Familia'],
      [ 'name' => 'Prod_Id'],
      [ 'name' => 'Prod_Descripcion'],
      [ 'name' => 'Prod_Costo'],
      [ 'name' => 'Prod_Precio'],
      [ 'name' => 'Prod_Precio2'],
      [ 'name' => 'Prod_Estado']
  ];

?>

@include('productos.alta_modif')
@include('productos.create')

@endsection <!-- Fin Contenido -->

@section('scrip')

<script>
   

  var $table = $('#mitabla'); // Tabla principal

  // Cuando cambia la Pestaña seleccionada  
  $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {

    var $table_auditoria = $('#tabla_auditoria'); 
    var $table_movi = $('#tabla_movi'); 
    var target = $(e.target).attr("href") // activated tab
    switch (target) {
      case '#movi': 
        $.ajax({
            dataType: "json",
            data: { familia: $('#Prod_Familia').val() , idprod: $('#Prod_Id').val()  },
            url:   'lista_movimientos',
            type:  'get',
            success: function(data){
                $table_movi.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
        return;
      case '#auditoria': 
        $.ajax({
            dataType: "json",
            data: { familia: $('#Prod_Familia').val() , idprod: $('#Prod_Id').val()  },
            url:   'lista_auditoria',
            type:  'get',
            success: function(data){
                $table_auditoria.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
        return;
      default:
        return;
    }
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
        cantidad = parseInt(row['stock01']) + parseInt(row['stock02'])
        //console.log  ( row[field] ,row['stock01'] , row['stock02'] ,cantidad )
        return +  ( row[field] * cantidad )
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
  // Fin Totales


   // $(document).ready(function(){
         // Tomo los datos de entrada
    //     consultar();
   // });      
  
    function searchByFormdata(){
      consultar()
    }
  
    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a Controlador con la logica de la busqueda para cargar tabla
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro_flia: $('#filtro_flia').val(), filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val() , filtro3: $('#filtro3').val() , filtroEstado: $('#filtroEstado').val()  },
            url:   'buscar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
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
    
    function cambia_codigo()  {
       // AUXILIAR TEMPORAL LLama FUNCION PARA CAMBIAR CODIGOS DE PRODUCTOS
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro_flia: $('#filtro_flia').val(),filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val() , filtro3: $('#filtro3').val() },
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

   function opcionesFormatter(value) {
      var Id = value;
      var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar registro" onclick="showEditModal(true,\''+ Id +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';

      // Opcion Eliminar  Solo si esta conectado y es ADM -->
      @if(Auth::user())
        @if(Auth::user()->perfil_id == 'ADM')
          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-danger btn-xs"'+
                  'title="Eliminar registro" onclick="deleteReg(\''+ Id +'\')">'+
                  '<i class="glyphicon glyphicon-trash" aria-hidden="true"></i>'+
              '</button>';
        @endif
      @endif

      return opciones;
  }
  
  function NuevoProducto() {

      // Boton de buscar proximo cod de Producto
      var familia = $('#Prod_Familia').val();
      // Calcular Automaticamente proximo Id del Producto
      $.get('../productos/GeneroNvoCodigo?familia='+familia, {}, 'json')
            .done(function(data) {
              $('#Prod_Id').val(data.NvoCodigo);
              $('#Prod_Descripcion').focus();
      });

  }

//  Ventanas de Alta y Modificacion
//  ---------------------------------

    function showAlta() {

        var form = $("#alta-form")[0];

        // Vaciar el formulario del modal.
        form.reset();

        $("#operation").val("store"); //Indica es un alta
        $("#modal-title").text("Nuevo");
        $("#msgErrModal").hide();

        $("#altaModal").modal("show")
            // Cuando termina de mostrarse.
            .on("shown.bs.modal", function(e) {
            $("#Prod_CodBarra").select(); // Selecciona todo el texto del 1er campo.
        });
    }


    //  JS de Formulario de Alta/ Modificacion 
      /**
     * Muestra el form de carga para el alta, ó Modificacion presentando los datos
     * en caso de que se proporcione por param. el id del registro a editar.
     * Para ambos casos se prepara según el tipo de operación a efectuar.
     * @param   is_modif        boolean     True si es modificar, false si es alta.
     * @param   id_registro     int         Id del registro para el que mostrar datos.
     */
    function showEditModal(is_modif, id_registro) {
        var form = $("#save-modify-form")[0];

        // Vaciar el formulario del modal.
        form.reset();

        $("#operation").val("store"); //Indica es un alta
        $("#modal-title").text("Nuevo");
        $("#msgErrModal").hide();

        if (is_modif) {
            // Si es Modificacion lee el registro para completar los campos
            var formdata = {
                id:         id_registro
            };
            $("#operation").val("update");
            $("#modal-title").text("Modificar");

            $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url:  '../{{$modulo_abm}}/show',            
                success: function(data){
                        var row;
                        row = data.result;
                        // Rellenar los datos sobre el registro a editar.
                        @foreach($campos_pantalla as $campoaux)
                            $("#{{$campoaux['name']}}").val(row.{{$campoaux['name']}});
                        @endforeach 
                        $("#id").val(data.id);                
                },
                error:  function(xhr,err){ 
                        // Como estamos en el "callback" del done(), el modal para entonces ya
                        // estaría abierto. Lo cerramos.
                        $("#userModal").modal("hide");
                        if (xhr.status == 401) { // Si se desconecto
                            document.location.reload(); // Para que recargue y pida login
                        }else{
                            msgerror( xhr.responseText);
                        }    
                } // Fin si hay error
            }); // Fin llamado Ajax
        }

        $("#userModal").modal("show")
            // Cuando termina de mostrarse.
            .on("shown.bs.modal", function(e) {
            $("#@yield('primer_campo')").select(); // Selecciona todo el texto del 1er campo.
        });
    }

    // Uno de los usos importantes del ready() es de declarar allí los "listeners"
    // de eventos como submit, click, etc.
    // También se utiliza para inicializar componentes de javascript.
    $(document).ready(function() {

        // Al Aceptar el formulario de nuevo/modif.
        $("#save-modify-form").on("submit", function(event) {

            // Cancelar. Dado que vamos a manejar nosotros el envío.
            event.preventDefault();

            // En el form está definido el tipo de operación.
            var action_name = '../{{$modulo_abm}}/' +  $("#operation").val();

            // Obtener todos los datos cargados.
            var form = $("#save-modify-form")[0];
            var formdata = $(form).serialize();

            $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url: action_name,            
                success: function(data){
                        $("#userModal").modal("hide");
                        muestroMsg(data.ret,1000);
                        $("#id").val(data.id);                
                        searchByFormdata(); // Recargar la busq. o actualiza lo seleccionado
                },
                error:  function(xhr,err){ 
                    var mensaje= xhr.responseText;

                    //console.log (xhr.responseJSON.message);
                    //console.log (xhr.responseText);

                    if (typeof xhr.responseJSON.errors != "undefined"){ //las validaciones de input   
                        mensaje="";
                        $.each(xhr.responseJSON.errors, function(key, value){   
                          mensaje = mensaje + '<p>'+value+'</p>'
                        });
                    }else{
                      if (typeof xhr.responseJSON.message != "undefined"){ //los abort generados   
                          mensaje =  '<p>'+xhr.responseJSON.message+'</p>'
                        }
                    };  
                    $("#msgErrModal").html(mensaje);
                    $("#msgErrModal").show();
                } // Fin si hay error
            }); // Fin llamado Ajax

        }); // Fin de Aceptar el formulario de nuevo/modif.

    }); // Fin de document).ready






</script>
@endsection <!-- Fin scrip -->