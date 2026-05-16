@extends('template.main')
   
@section('contenido')

  <form     role="form" >

    <div class="row">
      <div class="col-sm-8 col-md-8">
        <div class="modal-content ">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 id="modal-title">{{ $titulo }}  </h4>
            </div>
            <div class="modal-body">

              @isset($mensaje)
              <div   class="alert alert-success" align="left" >
                <b> {{ $mensaje }} </b>
              </div>
              @endisset

              @isset($detalles)
              @foreach($detalles as $detalle)
                    <b> {{ $detalle }} <br> </b>    
              @endforeach
              @endisset


              <div id="mensaje2" style="display:none; color: green;">Enviando Mail...</div>
              <div id="error" style="display:none; color: red;">Error...</div>

            </div>
            <br>      

            </div> <!-- FIN Modal body -->
            <div class="modal-footer">

            </div> <!-- modal-footer -->
        </div> <!-- Fin Modal Content -->
      </div> <!-- fin col -->
    </div> <!-- fin row -->
      </form> <!-- Fin Form --> 


@endsection <!-- Fin Contenido -->

@section('scrip')

   <script> 

    $(document).ready(function(){

      @isset($pdf)
       ruta = '{{ $pdf }}' 
       if (ruta != "" ) {
          envia_email();
          window.open( ruta, '', '_blanck' );
       }
      @endisset
    

    });

    function envia_email()  {

      @isset($id)
        id = '{{ $id }}';
        $("#mensaje2").css("display", "inline");
        $.ajax({
            dataType: "json",
            data: { id: id },
            url:   'envia_email',
            type:  'get',
            success: function(data){
                if (data.msgError != "") {
                  msgerror( data.msgError);
                  $("#error").css("display", "inline");
                  $("#error").html(data.msgError);
                }else{
                  $("#mensaje2").html("Se envío correctamente mail a Sucursal");                  
                }  
            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
      @endisset
  
    } // Fin envia_email()


   </script>

@endsection <!-- Fin Contenido -->
