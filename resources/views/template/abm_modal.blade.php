@include('template.partials.cabecera_general')
<!-- Menu principal -->
@include('template.partials.menu_center')
  
@include('flash::message')
    
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<div class="container-fluid">

<!-- Pantalla Principal del ABM Modal -->
<form id="search-form" action="" class='form-inline' onsubmit="return false;">

    <!-- Panel Del Titulo y Filtros -->
    <div class="panel panel-info">         
          <div class="panel-heading">
            <h3 class="panel-title">{{$titulo}}</h3>
          </div>
    @if(count($campos_busqueda)>0)
          <div class="panel-body">
            <div class="form-group">
              @foreach($campos_busqueda as $campo)
                  <input type="text" class="form-control" id={{ $campo['name']}}  placeholder={{ $campo['placeholder']}} value="">
              @endforeach 
           </div>
            <div class="form-group">
              <button type="button" class="btn btn-default pull-right" id="form-search-btn" onclick="searchByFormdata()">Buscar</button>
            </div>
        </div>
    @endif
   </div> <!-- Fin Panel Info -->

   <!-- Panel De la Tabla -->
    <div class="panel panel-success">      
      <div id="toolbar">
          <div class="form-group">
            <label>&nbsp</label>
            <button type="button" class="pull-right btn btn-success"
                id="form-search-btn" onclick="showEditModal(false, 0)">
                <i class="glyphicon glyphicon-plus"></i> Nuevo
            </button>
          </div>
          <label>&nbsp</label>
      </div>
      <table id="tabla-principal"
        class="table table-condensed table-hover table-bordered table-striped"
        data-toggle="table"
        data-search="true"
        data-pagination="true"
        data-page-size="50"
        data-show-print="true"
        data-show-export="true"
        data-toolbar="#toolbar"
        data-toolbar-align="right"
        >
        <thead>
            <tr>
              @foreach($columnas_tabla as $col)
                <th {!! html_entity_decode ($col['tipo']) !!}> {{ $col['titulo']}} </th>      
              @endforeach 
            </tr>
        </thead>
      </table>
    </div> <!-- fin de panel Grilla -->

</form>

<!-- Formulario Comun Confirma Baja -->
<div id="userModal_confirmaBaja" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <form method="post"  id="confirma-baja-form">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4> Confirmar Baja </h4>
            </div>
            <div class="modal-body">
                <label>De:</label>
                <input type="text" class="form-control" name="confbaja_nombre" id="confbaja_nombre" required>
                <br>
                <div name="confbaja_reasig" id="confbaja_reasig">
                <div name="confbaja_mensaje" id="confbaja_mensaje"  class="alert alert-danger" align="left" >completa por programa</div>
                
                <label>ReAsignar a:</label>
                <input type="text" class="form-control" name="confbaja_nombre_traspaso" id="confbaja_nombre_traspaso" autocomplete="off" required>
                </div> <!-- FIN Modal body -->
            </div> <!-- FIN Modal body -->
            <div class="modal-footer">
                <input type="hidden"  name="confbaja_id" id="confbaja_id" value="0">
                <input type="hidden"  name="confbaja_id_traspaso" id="confbaja_id_traspaso" value="0">
                <input type="button"  class="btn btn-success" onclick="deleteReg_confirmado()"  value="Aceptar">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>

          </div> <!-- Fin Modal Content -->
        </form>
    </div>
</div> <!-- FIN Formulario de Confirma Baja -->

</div> <!-- Fin.container del Template -->


@include('template.alta_modif_modal')

@include('common.modal_consulta')

@include('template.partials.pie_general')

<script>

  // Para que permita mover    
  $("#userModal_confirmaBaja").draggable({
      handle: ".modal-header"
  });

  /*
      * Busca registros y los muestra en la tabla, teniendo en cuenta
      * el formulario de búsqueda.
  */  
  function searchByFormdata() {

    // Criterios de Busquedas  y action al que responder nuestra solicitud por ultimo
    var formdata = {
        @foreach($campos_busqueda as $campo)
            {{ $campo['name']}}:  $("#{{ $campo['name']}}").val(),
        @endforeach 
        //filtro1:  $("#filtro1").val(),
    };

    $.ajax({
        dataType: "json",type:  'get', data: formdata, 
        url:   'buscar',            
        success: function(data){
//           console.log(data);
           $("#tabla-principal").bootstrapTable('load', data.results);
        },
        error:  function(xhr,err){ 
             console.log("Errro: readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
            // readyState values are 1:loading, 2:loaded, 3:interactive, 4:complete.
            // status is the HTTP status number, i.e. 404: not found, 500: server error, 200: ok. 
             msgerror( xhr.responseText);
        } // Fin si hay error
    }); // Fin llamado Ajax

  } //  fin searchByFormdata
  


  @if(count($campos_busqueda)==0)

     searchByFormdata() ; // Recargar la busq.   

  @endif

    /**
     * Pregunta si borrar el registro pasado por param. Si se acepta,
     * lo elimina vía ajax.
     */
    function deleteReg(id) {
 
        // Primero valido si tiene reg relacionados    
        // Despues Muestra Ventana de Confirmacion de Eliminacion

        var formdata = {
            id: id
        };
        $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url:   'validate_delete',            
                success: function(data){
                    $("#confbaja_id").val( id ) ;
                    $("#confbaja_nombre").val( data.nombre ) ;
                    $("#confbaja_id_traspaso").val( "" ) ;
                    $("#confbaja_nombre_traspaso").val( "" ) ;
                    // Si Tiene reg asociados, tengo que pedir otro id de reemplazo, sino lo oculta
                    if (data.mensaje != '') {
                        $("#confbaja_mensaje").html(data.mensaje);
                        $("#confbaja_reasig").show();
                    }else{
                        $("#confbaja_mensaje").html("");
                        $("#confbaja_reasig").hide();
                    }
                    $("#userModal_confirmaBaja").modal("show")
                        // Cuando termina de mostrarse.
                        .on("shown.bs.modal", function(e) {
                            $("#confbaja_nombre_traspaso").select(); // Selecciona todo el texto del 1er campo.
                    });    
                  },
                error:  function(xhr,err){ 
                    msgerror( xhr.responseText);
                    return;
                } // Fin si hay error
        }); // Fin llamado Ajax
    }

    function consultaReg(id) {
 
 // Primero valido si tiene reg relacionados    
 // Despues Muestra Ventana de Confirmacion de Eliminacion

 var formdata = {
     id: id
 };
 window.open('consulta?id=' + id , '', '_blanck');
 /*
 $.ajax({
         dataType: "html",type:  'get', data: formdata,
         url:   'consulta',            
         success: function(data){
              $('#titulo_consulta').html('Consulta Detallada');
              $('#destino').html(data);
              $("#consultaModal").modal("show")
              window.open(data, '', '_blanck');
           },
         error:  function(xhr,err){ 
             msgerror( xhr.responseText);
             return;
         } // Fin si hay error
 }); // Fin llamado Ajax
 */
}


    // Busqueda Automatica se utiliza en la confirmacion de Baja para re-asignar
    $('#confbaja_nombre_traspaso').typeahead({
            items: 15,
            minLength: 3,
            highlight: true,
            // Traer de un ajax.
            source: function(query, process) {
              $.ajax({
                dataType: "json", type:'get', global: false, // Para no mostrar cuadro Procesando
                url:  'busca_autocompletar?terms='+query,
                success: function(data){
                    if(data.length<=0){
                        // Si no encuentra nada
                       // no sobre el mismo campo   $('#confbaja_nombre_traspaso').val("No se encuentra coincidencia");
                    }
                    return process(data);
                }
              });
            },
            // Al seleccionar.
            afterSelect: function(item) {
                    $('#confbaja_id_traspaso').val(item.id);
                    $('#confbaja_nombre_traspaso').val(item.name);
               //     $('#id_familia').focus();
            }
    }); //Fin Busqueda Confirma Baja 



    function deleteReg_confirmado() {

        // Se ejecuta cuando Acepta la ventana de Confirma baja
        // Valido si re asigno correctamente

        var id = $('#confbaja_id').val();
        var id_nvo = $('#confbaja_id_traspaso').val();

        $("#userModal_confirmaBaja").modal("hide");

        //console.log ($("#confbaja_mensaje").html());
        //console.log ("deleteReg_confirmado:");
        console.log (id + "  " + id_nvo);

        if ( $("#confbaja_mensaje").html() != ""   ) {
            if ( id_nvo == 0  && id_nvo == "" ) {
                msgerror( "Error: Existen datos vinculados. Tiene que ReAsignar a otro");
                return;
            }
            if (id==id_nvo) {
                msgerror( "Error: No puede ReAsignar a si mismo");
                return;
            }
        }

        var id = $('#confbaja_id').val();
        var id_nvo = $('#confbaja_id_traspaso').val();
 

        // Primero valido si tiene reg relacionados    Con la opcion anterior

        var formdata = {
            id: id ,
            id_nvo: id_nvo
        };

        $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url:   'delete',            
                success: function(data){
                    muestroMsg("Registro eliminado con éxito.",1000);
                    searchByFormdata(); // Recargar la busq.                    
                  },
                error:  function(xhr,err){ 
                    msgerror( xhr.responseText);
                } // Fin si hay error
        }); // Fin llamado Ajax
        
    }



  /*
   * Da el formato requerido al valor presente en la columna de opciones.
   * @param value     valor que contiene la columna.
  */  
  function opcionesFormatter(value,columnas) {
      var Id = value;
      var opciones =  '';
          
      @if(  $conf_crud['boton_opcion_extra']  !=  '' ) 
          opciones = opciones + '&nbsp;' +  {!!$conf_crud['boton_opcion_extra']!!}
      @endif       
      @if(  $conf_crud['boton_opcion_extra2']  !=  '' ) 
          opciones = opciones + '&nbsp;' +  {!!$conf_crud['boton_opcion_extra2']!!}
      @endif       
      @if(  $conf_crud['boton_consulta']  ==  'S' ) 
              opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-primary btn-xs"'+
                  'title="Consultar" onclick="consultaReg(\''+ Id +'\')">'+
                  '<i class="glyphicon glyphicon-eye-open" aria-hidden="true"></i>'+
              '</button>';
      @endif       
              opciones = opciones + '&nbsp;'+  '<button type="button" class="btn btn-warning btn-xs"'+
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

</script>

@include('template.alta_modif_modal_js')


</body>
</html>
