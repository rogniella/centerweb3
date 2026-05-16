@extends('template.main')
@section('titulo','Alta de Transferencias entre Cuentas')
   
@section('contenido')

<link rel="stylesheet" href="{{ asset('plugins/css/formvalidation.min.css')}}"> 

<div class="container"> 
  <form  id="principalForm" class="form-horizontal"   role="form" >
    <div class="row">
      <div class="col-sm-12 col-md-8">
        <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" onclick="history.back()">&times;</button>
              <h4 id="modal-title">Alta de Transferencias entre Cuentas</h4>
              <!-- <a href='javascript:history.back(1)'>Regresar</a> -->
          </div>
          <div class="modal-body">
              <div class="form-group">
                    <label class="control-label col-md-3">Fecha Caja:</label>
                    <div class="col-md-6">
                    <input type="date" class="form-control" name="feccaja" id="feccaja" value="<?= date("Y-m-d"); ?>" required/>
                    </div>  
              </div>

              <div class="form-group">
                  <label class="control-label col-md-3">Origen:</label>
                  <div class="col-md-4">
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                  </div>   
                  <div class="col-md-3">
                    <select id="cuenta" name="cuenta"  class="form-control"  data-live-search="true"> </select>
                  </div>   
                  <div class="col-md-2">
                    <select id="moneda" name="moneda"  class="form-control"  data-live-search="true"> </select>
                  </div>
              </div>  

              <div class="form-group">
                  <label class="control-label col-md-3">Destino:</label>
                  <div class="col-md-4">
                    <select name="sucursalDes" id="sucursalDes" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                  </div>   

                  <div class="col-md-3">
                    <select id="cuentaDes" name="cuentaDes"  class="form-control"  data-live-search="true"> </select>
                  </div>   
                  <div class="col-md-2">
                    <select id="monedaDes" name="monedaDes"  class="form-control"  data-live-search="true"> </select>
                  </div>
              </div>  

              <div class="form-group">
                  <label class="control-label col-md-3">Importe:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control text-right" id="monto" name="monto"  required/>
                  </div>  
              </div>  

              <div class="form-group">
                  <label class="control-label col-md-3">Nota:</label>
                  <div class="col-md-8">
                    <input type="text"   class="form-control" id="nota" name="nota" placeholder="Introduce detalle del movimiento"> 
                  </div>
                </div>

              <div class="form-group">
                  <label class="control-label col-md-3">Importe Convertido:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control text-right" id="montoDes" name="montoDes">
                  </div>  
              </div>  

          </div> <!-- FIN Modal body -->
          <div class="modal-footer">
              <div id="cargando" style="display:none; color: green;">Procesando...</div>               
              <button type="submit"  class="btn btn-success " >Aceptar</button>
              <button type="button" class="btn btn-default" onclick="history.back()" >Regresar</button>
          </div> <!-- modal-footer -->
        </div> <!-- Fin Modal Content -->
      </div> <!-- fin col -->
    </div> <!-- fin row -->
      </form> <!-- Fin Form --> 
</div> <!-- Fin container -->

@endsection <!-- Fin Contenido -->

@section('scrip')

<script src="{{ asset('plugins/js/formvalidation.min.js')}}"></script>
<script src="{{ asset('plugins/js/validation/bootstrap.min.js')}}"></script>
<script src="{{ asset('plugins/js/language/es_ES.js')}}"></script>  <!-- Para traducir los mensajes al español -->



<script>



    
    $(document).ready(function() {

      iniciopantalla()
        
      $('#principalForm').formValidation({
             framework: 'bootstrap',
             icon: {
              //   valid: 'glyphicon glyphicon-ok',
                 invalid: 'glyphicon glyphicon-remove',
                 validating: 'glyphicon glyphicon-refresh'
             },
             locale: 'es_ES',
             fields: {
                 montoWW: {
                     validators: {
                         numeric: {
                             message: 'Tiene que ser un Importe numérico',
                             // The default separators
                             thousandsSeparator: '',
                             decimalSeparator: '.'
                         }
                     }
                 },
                 montoDesWW: {
                     validators: {
                         numeric: {
                             message: 'Tiene que ser un Importe numérico',
                             // The default separators
                             thousandsSeparator: '',
                             decimalSeparator: '.'
                         }
                     }
                 },
                 feccaja: {
                     validators: {
                         notEmpty: {
                         },
                         date: {
                             format: 'YYYY/MM/DD'
                         }
                     }
                 }
             }
    
      })
        .on('success.form.fv', function(e) {
             // Prevent form submission
             e.preventDefault(); //Tiene que ir para que no se vaya de la pagina
             grabar();

      }); // Fin formValidation

        
  });  // Fin ready



  console.log ("general")

  const numeroInput = document.getElementById('monto');
  const auxmonto = new AutoNumeric(numeroInput, 'commaDecimalCharDotSeparator');

  const numeroInput2 = document.getElementById('montoDes');
  const auxmontoDes = new AutoNumeric(numeroInput2, 'commaDecimalCharDotSeparator');

        /*
  // http://autonumeric.org/
  document.addEventListener('DOMContentLoaded', function () {
        // Configurar AutoNumeric en el campo de entrada
        console.log ("pasa por addevent")
 //       const numeroInput = document.getElementById('monto');
//        new AutoNumeric(numeroInput, 'commaDecimalCharDotSeparator');
      const numeroInput = document.getElementById('monto');
        new AutoNumeric(numeroInput, {
                    digitGroupSeparator: '.',  // Separador de miles
             decimalCharacter: ',',     // Separador decimal
             decimalPlaces: 2,          // Número de decimales
             allowDecimalPadding: "floats",
             createLocalList: false,
        });





  });

*/

  // Si cambia la Sucursal
  $("#sucursal").on("change", cambioSucursal );

  $("#sucursalDes").on("change", cambioSucursalDes );

  function cambioSucursal (){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      buscarCuentas('N')
  }

  function cambioSucursalDes (){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      buscarCuentas('S')
  }

  // Si cambia la cuenta, busca las monedas asociadas
  $("#cuenta").on("change", cambioCuenta);

  $("#cuentaDes").on("change", cambioCuentaDes);

  function cambioCuenta (){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      buscarMonedas ("N")
  }

  function cambioCuentaDes (){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      buscarMonedas ("S")
  }


  function iniciopantalla()  {
      console.log ("pasa por inicio")
  //    $("#monto").val("");
      auxmonto.clear();
      auxmontoDes.clear();

      $("#nota").val("");
      cambioSucursal (); // Para que cargue l combo 
      cambioSucursalDes ();
      $("#sucursal").focus();
  }


  function buscarCuentas( $destino ){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      //console.log('buscacta' , $destino)
      if($destino == 'S'){
        $sucursal = $("#sucursalDes").val();
      }else{
        $sucursal = $("#sucursal").val();
      }

      $.ajax({
            global: false,
            dataType: "json",
            data: {"sucursal": $sucursal,"cod_cuenta": "01"},
            url:   'combo_cuenta_sucursal',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              if($destino == 'S'){
                 $("#cuentaDes").html(respuesta.html);
              }else{
                 $("#cuenta").html(respuesta.html);
              }

              buscarMonedas($destino); // Para que cargue l combo de monedas
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }   
            }
      });
   }

  function buscarMonedas($destino){
      // Carga combo moneda segun cuenta seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      // console.log('buscamoneda' , $destino)
      if($destino == 'S'){
        $cuenta = $("#cuentaDes").val();
      }else{
        $cuenta = $("#cuenta").val();
      }
      $.ajax({
            global: false,
            dataType: "json",
            data: {"cuenta": $cuenta,"moneda": "P"},
            url:   'combo_moneda_cuenta',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              if($destino == 'S'){
                $("#monedaDes").html(respuesta.html);
              }else{
                $("#moneda").html(respuesta.html);
              }
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }   
            }
      });
  }


  function grabar()  {

    //Validaciones Logicas
    if ( $("#sucursal").val() == $("#sucursalDes").val() &&  
         $("#cuenta").val() == $("#cuentaDes").val() &&  
         $("#moneda").val() == $("#monedaDes").val()   ) {
            Swal.fire({
                type: 'error',
                title: 'Error: El Origen tiene que ser diferente al Destino',
                text: 'Controle Sucursales/Cuentas/Monedas !',
                footer: ''
            })
            $('#sucursal').focus();
            return false;
    }

    if ( $("#moneda").val() == $("#monedaDes").val() ) {
        // Si son la misma Moneda , tiene que ser los mismos los montos
        $("#montoDes").val(  $("#monto").val()   )
    }else {
       if ( $("#montoDes").val() == 0 ) {
            Swal.fire({
                type: 'error',
                title: 'Error: Falta Monto Convertido',
                text: 'Debe ingresar el Monto de la Moneda Destino !',
                footer: ''
            })
            $('#montoDes').focus();
            return false;
       }
    }

    // Lo formatea para bd, saca separador de miles
    monto =  numberFormatBd ( $("#monto").val() )
    montodes =  numberFormatBd ( $("#montoDes").val() )

    console.log( monto, montodes)
    $.ajax({
              dataType: "json",
              data: { operacion: 'T',
                     sucursal: $("#sucursal").val(), 
                     sucursalDes: $("#sucursalDes").val(), 
                     cuenta: $("#cuenta").val(),
                     cuentaDes: $("#cuentaDes").val(),
                     feccaja: $("#feccaja").val(),
                     monto: monto,
                     montoDes: montodes,
                     moneda: $("#moneda").val(),
                     monedaDes: $("#monedaDes").val(),
                     nota: $("#nota").val()},
              url:   'store',
              type:  'get',
              beforeSend: function(){
                //Lo que se hace antes de enviar el formulario
                   $("#cargando").css("display", "inline");
                },
              success: function(respuesta){
                //lo que se si el destino devuelve algo
                // console.log(respuesta.html);
                 iniciopantalla();
                 
                 muestroMsg("<b>Ok: </b>Movimiento Ingresado",2000);    
                   $("#cargando").css("display", "none");
              },
              error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }   
  
                  $("#cargando").css("display", "none");
              } // Fin si hay error
                
    });
  } // Fin grabar()


</script> 

@endsection <!-- Fin scrip -->
