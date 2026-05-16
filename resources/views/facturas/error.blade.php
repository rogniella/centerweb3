@extends('template.main')
@section('titulo','Error en Generación de Factura AFIP')
   
@section('contenido')

  <form     role="form" >

    <div class="row">
      <div class="col-sm-6 col-md-6">
        <div class="modal-content ">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 id="modal-title">Error al Generar Factura en AFIP  </h4>
            </div>
            <div class="modal-body">

              <div   class="alert alert-success" align="left" ><b>
                  Comprobante Generado Tipo: {{ $comprobante->comp_tipoot }}  
                      -  {{ $comprobante->comp_id }}   Por un Monto:  $  {{ $comprobante->comp_monto }}
              </b></div>


                <div class="form-group">
                    <div class="col-md-12">

                      <button type="button" class="btn btn-success pull-right" id="form-search-btn" onclick="generaComprobanteAFIP()">Reintentar AFIP</button>

                      </div>  
                </div>                


              </div>
          <br>      
          <br>      
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

  function generaComprobanteAFIP( )  {
 
       ruta = 'generaComprobanteAFIP'
       var Id = {{ $comprobante->comp_id }}
       var tipo = '{{ $comprobante->comp_tipoot }}'

       $.ajax({
            dataType: "json",
            data: { tipo: tipo, id: Id, soloGenera:'si' },
            url:   '../ventas/' + ruta,
            type:  'get',
            success: function(data){
              if(data.retError == "" || data.retError == null) { 
                  // ir a la pantalla del pdf
                  if(data.pdf != "" ) {
                      // Mostrar Factura generada y salir
                      window.open(data.pdf, '', '_blanck');
                      window.close();
                    //  location.href ="{{ route('home') }}"
                  }    
              }else{
                  msgerror( data.retError);
              }
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
        
  }

 
</script> 

@endsection <!-- Fin scrip -->
