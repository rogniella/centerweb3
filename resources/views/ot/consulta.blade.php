@extends('template.main')
@section('titulo','Consulta de Ordenes de Trabajo')
   
@section('contenido')

<!-- Primera Pantalla - Pido Codigo -->
<form role="form" autocomplete="off" onkeypress="pulsar(event)" >
    <div class="row">
        <div class="col-sm-5 col-md-offset-3">
          <div class="panel panel-info">         
              <div class="panel-heading">
                <h3 class="panel-title">Consulta de OT  </h3>
              </div>
              <div class="panel-body">
                <div class="form-group">
                  <input type="number" class="form-control" id="ot" name="ot" placeholder="Nro. de Orden" required/>
                </div>
                <div id="cargando" style="display:none; color: green;">Procesando.. </div>
                <button type="button" id="btnconsulta" onClick="consultar()" class="btn btn-primary pull-right">Consultar</button>              
              </div>
          </div> <!-- Fin Panel -->
        </div> <!-- Fin col -->
    </div> <!-- Fin row -->


    <div class="row">
      <div class="col-sm-5  col-md-offset-3">
        <div class="panel panel-info">
          <div class="panel-body">         
            <div id="destino"> </div>
          </div>
        </div>
      </div>
    </div>

    <input type="text" class="form-control" id="numero" name="numero" placeholder="Ejemplo: 1234567890" required>
    <a href="javascript:send_handle2();">Prueba WA</a> 

</form>
    
@endsection <!-- Fin Contenido -->


@section('scrip')

<script>

    let num_telefono= "+543772631212";  



     $(document).ready(function(){
          document.getElementById("ot").focus(); 
     });      
 
    // Función para validar el número de WhatsApp
    function validarNumeroWhatsApp(codigoPais, numero) {
            // Expresión regular para verificar el formato del número (solo dígitos)
            var numeroRegex = /^\d{6,15}$/;
            if (!numeroRegex.test(numero)) {
                return false;
            }

            // Verificar que el código de país comience con '+'
            if (!codigoPais.startsWith("+")) {
                return false;
            }

            return true;
     }    


     function send_handle2(){

        //  let num=document.getElementById("number").value;
        //  let num= "+543772409048";
        //  let num= "+543456517677";
        let num= "+543772631212";  
        let msg= "Hola+me+estoy+contactando+desde+CenterOptica+para+informarle";
        codigoPais = "+54"
        console.log (num_telefono)


        let numeroWhatsApp =document.getElementById("numero").value;
        numeroWhatsApp= "3772631212";  

        if (validarNumeroWhatsApp(codigoPais, numeroWhatsApp)) {
                // Aquí puedes enviar el número a tu servidor o realizar otras acciones
                var win = window.open(`https://api.whatsapp.com/send/?phone=${num}&text=${msg}&type=phone_number&app_absent=0`, '_blank', "width=200,height=100");

        } else {
                alert('Número de WhatsApp no válido. Por favor, ingrese un número válido.');
        }

        //  https://api.whatsapp.com/send/?phone=543772409047&text=Hola+me+estoy+contactando+desde+su+sitio+web&type=phone_number&app_absent=0
       // var win = window.open(`https://wa.me/${num}?text=I%27m%20api%20msg%20hello%20${name}%20friend%20${msg}`, '_blank');
       // var win = window.open(`https://api.whatsapp.com/send/?phone=${num}&text=${msg}&type=phone_number&app_absent=0`, '_blank');
        // win.focus();
     }

  function pulsar(e) {
    if (e.keyCode === 13 && !e.shiftKey) {
        e.preventDefault();
        consultar();
    }
  }
  
  function consultar() {

      $('#destino').html("");
      if($("#ot").val() == 0 ) {
          msgerror("Complete Nro. OT",5000);    
          return;
      } 

      $("#cargando").css("display", "inline");
      ot = $('#ot').val();

      $.ajax({
            dataType: "json",
            data: { ot: ot },
            url:   'show',
            type:  'get',
            success: function(data){
             //   $('#telefono').val("+54 3772409048")
             console.log ( " HOla:"  )

//             console.log ( " HOla:" +  $('#telefono').val() )
  //              num_telefono =  "+543772409048"
                $("#cargando").css("display", "none");
                $('#destino').html(data);
            },
            error:  function(xhr,err){ 
                $("#cargando").css("display", "none");
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   $('#destino').html(xhr.responseText);
                }
            } // Fin si hay error
      }); // Fin llamado Ajax

      if  (navigator.userAgent.indexOf( "Android") == -1) {
            $('#ot').val("");   
            $('ot').focus(); // Si no es Celular
      }else{
            //$('#famila1').focus();
            document.getElementById("ot").focus(); // Para los celu
      }                                
  } // end consultar

   
</script>

@endsection <!-- Fin scrip -->
