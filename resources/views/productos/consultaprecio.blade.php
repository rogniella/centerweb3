@extends('template.main')
@section('titulo','Consulta de Precios')
   
@section('contenido')

<!-- Primera Pantalla - Pido Codigo -->
<form role="form" autocomplete="off" onkeypress="pulsar(event)">
      {{ csrf_field() }}
    <div class="row">
        <div class="col-sm-4 col-md-offset-4">
          <div class="panel panel-info">         
              <div class="panel-heading">
                <h3 class="panel-title">Consulta de Precios  </h3>
              </div>
              <div class="panel-body">
                <div class="form-group">
                  <input type="text" class="form-control" id="codigosearch" placeholder="Codigo/Descripción Producto" required/>
                  <input type="hidden"  id="codigo">
                  <input type="hidden"  id="auxcodigo">
                  <input type="hidden"  id="auxprecio">
                </div>
                <div class="form-check">
                    <input class="form-check-input" name="familia" type="radio" id="familia1"  value="REC" checked="checked">
                    <label class="form-check-label" for="familia1">RECETA  </label>
                    
                    <input class="form-check-input" name="familia" type="radio" id="familia2" value="SOL" >
                    <label class="form-check-label" for="familia2">SOL  </label>
                    
                    <input class="form-check-input" name="familia" type="radio" id="familia3" value="CEL" >
                    <label class="form-check-label" for="familia3">CELU</label>
                </div>    
                <div id="cargando" style="display:none; color: green;">Procesando.. </div>
              </div>
          </div> <!-- Fin Panel -->
        </div> <!-- Fin col -->
    </div> <!-- Fin row -->
    <div class="row">
        <div class="col-sm-4 col-md-offset-4">
            <div id="destino"> </div>
        </div>
    </div>
</form>

<!-- Segunda Pantalla - Confirma Venta -->
<div class="modal fade" id="ventanaConfirma" tabindex="-1" role="dialog">
  <div class="modal-dialog">
         <div class="panel panel-warning">         
            <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="panel-title">Confirmar Venta</h4>

            </div>
        <form class="form-inline" id="formconfirma">
            
         <div class="panel-body">
          <div class="form-group has-success has-feedback">
          <label class="control-label">Importe : &nbsp; &nbsp;&nbsp;</label>
                       
      <input type="number" class="form-control" id="monto" >
                        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
                        
        </div>
          <div class="form-group has-success has-feedback">
          <label class="control-label">Descuento :</label>
      <input type="number" class="form-control" id="monto_descuento" >
                        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
        </div>
          <div class="form-group has-success has-feedback">
          <label class="control-label">T O T A L : &nbsp;&nbsp;</label>
      <input type="number" class="form-control" id="monto_total" >
                        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
        </div>
        <br>
          <div class="form-group">
          <label class="control-label"> </label>
        </div>

      </form> 
    

      <div class="modal-footer">
        <button type="button" class="btn btn-success" onClick = "graboVenta()">Aceptar</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
      </div>

      </div>
    </div>

  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@endsection <!-- Fin Contenido -->


@section('scrip')

<script>
 
  $(document).ready(function(){

    document.getElementById("codigosearch").focus(); 

    $('#codigosearch').typeahead({
        items: 15,
        minLength: 3,
        highlight: true,
        emptyTemplate: "NO",
        // Traer de un ajax.
        source: function(query, process) {
          familia = $('input[name=familia]:checked').attr('value');
          $('#destino').html("");
          $.ajax({
              global: false,
              dataType: "json",
              data: {},
              url:   'buscaproducto?terms='+query+'&familia='+familia,
              type:  'get',
             success: function(data){
                if(data.length<=0){
                  // Si no encuentra nada
                  $('#codigo').val('');      
                  $('#destino').html("<div class='alert alert-danger'>No se encuentra coincidencia</div>");
                }
                return process(data);
              },
              error:  function(xhr,err){ 
                msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
              }
          });
        },
        // Al seleccionar.
        afterSelect: function(item) {
            $('#codigo').val(item.id);
            consultar();
        }
    }); 
  });  // Fin Ready    
  
 
  function pulsar(e) {
    if (e.keyCode === 13 && !e.shiftKey) {
        e.preventDefault();
        consultar();
    }
  }


  // Es llamado cuando selecciona en el auto busqueda
  function consultar() {

      $('#destino').html("");
      $("#auxcodigo").val($("#codigo").val());
      if($("#codigo").val() == 0 ) {
          msgerror("Complete Código/Descripción Producto",5000);    
          return;
      } 

      $("#cargando").css("display", "inline");
      familia = $('input[name=familia]:checked').attr('value');
      codigo = $('#codigo').val();
      return $.get('consultaprecio2?codigo=' + codigo +'&familia='+familia, {}, 'json')
          .done(function(data) {
              $("#cargando").css("display", "none");
              document.getElementById("codigosearch").value="";
              if(data.length<=0){
                  // Si no encuentra nada
                  $('#destino').html('error');
              }
            $('#destino').html(data);
          });
                                
          if  (navigator.userAgent.indexOf( "Android") == -1) {   
            $('codigosearch').focus(); // Si no es Celular
          }else{
            //$('#famila1').focus();
            document.getElementById("familia1").focus(); // Para los celu
          }

  } // end consultar


  function vender(precio, descuento) {
       // Pide la confirmacion del usuario    
       $("#auxprecio").val(precio); // resguardo el precio original
       $("#monto").val(precio)
       $("#monto_descuento").val(descuento)
       total =  precio - descuento
       $("#monto_total").val(total)
       $('#ventanaConfirma').modal('show')    
       return true; 
  }

  function graboVenta () {
       $('#ventanaConfirma').modal('hide')  //Oculto la ventana de confirmaci�n 

       if (parseInt($("#monto").val()) < parseInt($("#auxprecio").val()) ) {
         // console.log ($("#monto").val());
            msgerror("NO se permite vender por menor precio");  
        return true;    
       }   
         
     $familia = $('input[name=familia]:checked').attr('value');
     $codigo = $("#auxcodigo").val();
     $monto = $("#monto").val();
     $descuento = $("#monto_descuento").val();
     $.ajax({
          dataType: "json",
          data: { familia: $familia,codigo: $codigo,monto: $monto,descuento: $descuento},
          url:   'consultaprecioventa',
          type:  'get',
          beforeSend: function(){
            //Lo que se hace antes de enviar el formulario
            },
          success: function(respuesta){
            //lo que se si el destino devuelve algo
            // console.log(respuesta.html);
              muestroMsg("<b>Venta Ok: </b>Pase por Caja",2000);    
                //     muestroMsg("Mensage de prueba Cuadro Chico",3000, 'chico');    
              $('#destino').html("");
            //  document.location.href='consultaprecio.php';
          },
          error:  function(xhr,err){ 
              msgerror( xhr.responseText);
          } // Fin si hay error
       });
      return true;  
  }

   
</script>

@endsection <!-- Fin scrip -->
