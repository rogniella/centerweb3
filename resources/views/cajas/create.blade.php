@extends('template.main')
@section('titulo','Alta de Movimientos Detallados')
   
@section('contenido')

<link rel="stylesheet" href="{{ asset('plugins/css/formvalidation.min.css')}}"> 

<div class="container"> 
  <form  id="principalForm" class="form-horizontal"   role="form" >
    <div class="row">
      <div class="col-sm-12 col-md-8">
        <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" onclick="history.back()">&times;</button>
              <h4 id="modal-title">Alta de Movimientos Detallados</h4>
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
                    <label class="control-label col-md-3">Sucursal:</label>
                    <div class="col-md-6">
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    </div>   
              </div>  
              <div class="form-group">
                  <label class="control-label col-md-3">Cuenta:</label>
                  <div class="col-md-6">
                    <select id="cuenta" name="cuenta"  class="form-control"  data-live-search="true"> </select>
                  </div>   
              </div>  
              <div class="form-group">
                  <label class="control-label col-md-3">Cod.Movimiento:</label>
                  <div class="col-md-8">
                    <select id="codmov" name="codmov"  class="selectpicker form-control"  data-live-search="true">     @include('common.combo_codmov')
                    </select>
                  </div>  
              </div>       
              <div class="form-group">
                  <label class="control-label col-md-3">Importe:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control text-right" id="monto" name="monto" required/>
                  </div>  
              </div>  
              <div class="form-group">
                  <label class="col-md-3 control-label">Moneda:</label>
                  <div class="col-md-6">
                    <select id="moneda" name="moneda"  class="form-control"  data-live-search="true"> </select>
                  </div>
              </div>  
              <div class="form-group">
                  <label class="control-label col-md-3">Nota:</label>
                  <div class="col-md-8">
                    <input type="text" class="form-control" id="nota" name="nota" placeholder="Introduce detalle del movimiento"required> 
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
                 montoaux: {
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



  // Si cambia la Sucursal
  $("#sucursal").on("change", buscarCuentas);

  // Si cambia la cuenta, busca las monedas asociadas
  $("#cuenta").on("change", buscarMonedas);

  /*
  $('#monto').keyup(function() {
     $(this).val($(this).val().replace(',', '.'));
  });
  */

  function iniciopantalla()  {
    //  $("#monto").val("");
      auxmonto.clear();
      $("#nota").val("");
      buscarCuentas(); // Para que cargue l combo 
      $("#sucursal").focus();
  }


  function buscarCuentas(){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      $sucursal = $("#sucursal").val();
      $.ajax({
            global: false,
            dataType: "json",
            data: {"sucursal": $sucursal,"cod_cuenta": "01"},
            url:   'combo_cuenta_sucursal',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              $("#cuenta").html(respuesta.html);
              buscarMonedas(); // Para que cargue l combo de monedas
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

  function buscarMonedas(){
      // Carga combo moneda segun cuenta seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      $cuenta = $("#cuenta").val();
      $.ajax({
            global: false,
            dataType: "json",
            data: {"cuenta": $cuenta,"moneda": "P"},
            url:   'combo_moneda_cuenta',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              $("#moneda").html(respuesta.html);
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

    monto = numberFormatBd ( $("#monto").val() ) 
    
    $.ajax({
              dataType: "json",
              data: { operacion: 'D', 
                     sucursal: $("#sucursal").val(), 
                     cuenta: $("#cuenta").val(),
                     feccaja: $("#feccaja").val(),
                     codmov: $("#codmov").val(),
                     monto: monto,
                     moneda: $("#moneda").val(),
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
