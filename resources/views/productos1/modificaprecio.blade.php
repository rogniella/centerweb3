@extends('template.main')
@section('titulo','Modificación de Precios')
   
@section('contenido')

 <form role="form" >
 
      <div class="row">
        <div class="col-sm-4">
         <div class="panel panel-success">         
            <div class="panel-heading">
              <h3 class="panel-title">Modificacion de Precios</h3>
            </div>
         <div class="panel-body">
        <div class="form-group">
            <label class="control-label"></label> 
            <input type="radio" name="familia" id="familia1" value="REC" checked> RECETA
        <label class="control-label"></label> 
          <input type="radio" name="familia" id="familia2" value="SOL"> SOL
          </div>
          <div class="form-group">
          <label class="control-label">Código:</label>
            <input type="number" class="form-control" id="codigo" maxlength="5"
                    placeholder="codigo producto" required/>
            <input type="hidden"  id="auxcodigo">
            <input type="hidden"  id="auxprecio">
        </div>
          <button type="button" id="btnconsulta" onClick="consultar()" class="btn btn-primary pull-right">Consultar</button>
        <div id="cargando" style="display:none; color: green;">Procesando.. </div>


      </div>
         </div>
        </div>
       </div>
       <div class="row">
        <div class="col-sm-4">
            <div id="destino"> </div>
      </div>
       </div>

   </form>


<!-- Pantalla de Modificaciones de Montos -->
<div class="modal fade" id="ventanaPrecio" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="panel panel-warning">         
        <div class="panel-heading">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 id="lbldescripcion" class="panel-title">Precios:...</h4>
        </div>

        <form class="form-inline" id="formconfirma">            
         <div class="panel-body">
          <div class="form-group has-success has-feedback">
          <label class="control-label">Precio 1:</label>
        <input type="number" class="form-control" id="monto" >
        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
        </div>
        <div class="form-group has-success has-feedback">
          <label class="control-label">Precio 2:</label>
        <input type="number" class="form-control" id="monto2" >
        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
        </div>
        </div>
      </form> 
        <div class="modal-footer">
        <button type="button" class="btn btn-success" onClick = "graboModif()">Aceptar</button>
          <button type="button" class="btn btn-default" onClick = "cancelaModif()">Cancelar</button>
        </div>
    </div> <!-- /Panel -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection <!-- Fin Contenido -->


@section('scrip')


    <script>
    
     $(document).ready(function(){
     
     });      
  
   function consultar() {

      $("#auxcodigo").val($("#codigo").val());
      if($("#codigo").val() == 0 ) {
          msgerror("Complete Código Producto",5000);    
      return;
      } 

    $("#cargando").css("display", "inline");
        var action_name = "lee_precio";
        var formdata = {
              familia: $('input[name=familia]:checked').attr('value'), 
              codigo: $('#codigo').val()
        };
        // Solicitud Ajax de tipo GET.
        $.get('modificaprecio2?action=' + action_name, formdata, "json")
                .done(function(data) {
                    var error_msg = "";
                    var row;
                    if (data.success) {
              $("#auxprecio").val(data.precio); // resguardo el precio original
              $("#lbldescripcion").html( formdata.familia  + "&nbsp;" + formdata.codigo  +":&nbsp;" + data.descripcion)
              $("#monto").val(data.precio)
              $("#monto2").val(data.precio2)
              $("#cargando").css("display", "none");
              $("#ventanaPrecio").modal("show")
                    // Cuando termina de mostrarse.
                    .on("shown.bs.modal", function(e) {
                      $("#monto").select(); // Selec todo el texto.
                });
                    } else {
                        // Si incluyera un mensaje con el detalle del error.
                        if (typeof data.error_msg !== "undefined") {
                            error_msg = data.error_msg;
                        }
                        $("#cargando").css("display", "none");
                        msgerror(error_msg);
                    }
                })
                .fail(function() {
                    $("#cargando").css("display", "none");
                    msgerror("Ocurrio un error en la conexion Ajax.");
        });
         
   } // end consultar



   function graboModif () {
       $('#ventanaPrecio').modal('hide')  //Oculto la ventana de Modificacion de Precios

       if (parseInt($("#monto").val()) == 0 ) {
            msgerror("NO se permite  precios en cero");  
        return true;    
       }   

        var action_name = "modifica_precio";
        var formdata = {
              familia: $('input[name=familia]:checked').attr('value'), 
              codigo: $('#codigo').val(),
              monto: $("#monto").val(),
              monto2: $("#monto2").val()
        };

        // Solicitud Ajax de tipo GET.
        $.get('modificaprecio2?action=' + action_name, formdata, "json")
                .done(function(data) {
                    var error_msg = "";
                    var row;
                    if (data.success) {
                       muestroMsg("<b>Ok: </b>Actualizado",500);
                       $("#codigo").focus(); 
                       $("#codigo").val("");
                    } else {
                        // Si incluyera un mensaje con el detalle del error.
                        if (typeof data.error_msg !== "undefined") {
                            error_msg = data.error_msg;
                        }
                        msgerror(error_msg);
                    }
                })
                .fail(function() {
                    msgerror("Ocurrio un error en la conexion Ajax Al Actualizar.");
        });

    return true;  
   }

  function cancelaModif () {
      $('#ventanaPrecio').modal('hide')  //Oculto la ventana de Modificacion de Precios
      $("#codigo").focus(); 
        $("#codigo").val("");
    return true;  
  }


     </script>

   


@endsection <!-- Fin scrip -->
