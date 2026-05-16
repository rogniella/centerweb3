
<!-- Formulario de Alta/Modificacion Movimientos Caja -->
<div id="userModal" class="modal fade"  data-backdrop="static">
 <div class="modal-dialog modal-lg " role="document">
  <form method="post" id="save-modify-form-caja" class="form-horizontal"   role="form" onkeypress="return event.keyCode != 13;" >

  <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 id="modal-title">&nbsp;</h4>
    </div>

    <div class="modal-body">      
              <div class="form-group">
                    <label class="control-label col-md-3">Fecha Caja:</label>
                    <div class="col-md-6">
                    <input type="date" class="form-control" name="MCaj_FecMov" id="MCaj_FecMov" value="<?= date("Y-m-d"); ?>" required/>
                    </div>  
              </div>                  
              <div class="form-group">
                    <label class="control-label col-md-3">Sucursal:</label>
                    <div class="col-md-6">
                    <select name="MCaj_Sucursal" id="MCaj_Sucursal" class="form-control" required>
                        @foreach($sucursalesModal as $key => $value)
                            <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                    </div>   
              </div>  
              <div class="form-group">
                  <label class="control-label col-md-3">Cuenta:</label>
                  <div class="col-md-6">
                    <select id="MCaj_CtaOri" name="MCaj_CtaOri"  class="form-control"  data-live-search="true"> </select>
                  </div>   
              </div>  
              <div class="form-group">
                  <label class="control-label col-md-3">Cod.Movimiento:</label>
                  <div class="col-md-6">
                    <select id="MCaj_Codigo" name="MCaj_Codigo"  class="selectpicker form-control"  data-live-search="true">   @include('common.combo_codmov')
                    </select>
                  </div>  
              </div>       
              <div class="form-group">
                  <label class="control-label col-md-3">Importe:</label>
                  <div class="col-md-6">
                    <input type="text"  class="form-control text-right" id="MCaj_Monto" name="MCaj_Monto">
                  </div>  
              </div>  
              <div class="form-group">
                  <label class="col-md-3 control-label">Moneda:</label>
                  <div class="col-md-6">
                    <select id="MCaj_Moneda" name="MCaj_Moneda"  class="form-control"  data-live-search="true"> </select>
                  </div>
              </div>  
              <div class="form-group">
                  <label class="control-label col-md-3">Nota:</label>
                  <div class="col-md-8">
                    <input type="text" class="form-control" id="MDes_Descripcion" name="MDes_Descripcion" placeholder="Introduce detalle del movimiento"> 
                  </div>
              </div>

    </div> <!-- FIN Modal body -->

    <div class="modal-footer">
        <div id="msgErrModal"  class="alert alert-danger" align="left" >completa por programa</div>
        <input type="hidden" id="operation" name="operation" >
        <input type="hidden"  name="id" id="id" value="0">
        <input type="submit" id="btn-submit" class="btn btn-success" value="Aceptar">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
    </div> <!-- FIN Footer -->

  </div> <!-- FIN modal-content -->
  </form>  
 </div>  <!-- FIN modal-dialog -->
</div> <!-- FIN Formulario de Alta/ Modificacion -->

@section('scrip_alta_modif')

<script>

    // Uno de los usos importantes del ready() es de declarar allí los "listeners"
    // de eventos como submit, click, etc.
    // También se utiliza para inicializar componentes de javascript.
    $(document).ready(function() {

// Al Aceptar el formulario de nuevo/modif.
$("#save-modify-form-caja").on("submit", function(event) {

    // Cancelar. Dado que vamos a manejar nosotros el envío.
    event.preventDefault();

    // Validaciones
    if ($("#MCaj_Monto").val() == '') {  
        $("#msgErrModal").html('Debe completar el Importe');
        $("#msgErrModal").show();
        return
    }                         

    // En el form está definido el tipo de operación.
    var action_name = '../cajas/store2';

    // Obtener todos los datos cargados.
    var form = $("#save-modify-form-caja")[0];

    $("#MCaj_Monto").val( numberFormatBd ( $("#MCaj_Monto").val() ) )
    console.log( "Graba:" +  $("#MCaj_Monto").val())
    var formdata = $(form).serialize();

    $.ajax({
        dataType: "json",type:  'get', data: formdata,
        url: action_name,            
        success: function(data){
                $("#userModal").modal("hide");
                muestroMsg(data.ret,1000);
                $("#id").val(data.id);
                consultar();
                //searchByFormdata(); // Recargar la busq. o actualiza lo seleccionado
        },
        error:  function(xhr,err){ 
           if (xhr.status == 401) {
      //        msgerror( "Se desconecto. Vuelva a Ingresar su Usuario");
               document.location.reload(); // Para que recargue y pida login
           }else if (xhr.status == 402) {
               var mensaje= xhr.responseJSON.message;
                $("#msgErrModal").html(mensaje);
                $("#msgErrModal").show();
           }else{
              var mensaje= "readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText;
                $("#msgErrModal").html(mensaje);
                $("#msgErrModal").show();
            }

        } // Fin si hay error
    }); // Fin llamado Ajax

}); // Fin de Aceptar el formulario de nuevo/modif.

}); // Fin de document).ready

console.log ("general Modif")
  const numeroInput = document.getElementById('MCaj_Monto');
  const auxmonto = new AutoNumeric(numeroInput, 'commaDecimalCharDotSeparator');

// Si cambia la Sucursal
  $("#MCaj_Sucursal").on("change", buscarCuentasCambio  );

  // Si cambia la cuenta, busca las monedas asociadas
  $("#MCaj_CtaOri").on("change", buscarMonedasCambio );


  function iniciopantalla()  {
      //$("#MCaj_Monto").val("");
      auxmonto.clear();
      $("#MDes_Descripcion").val("");
      buscarCuentas("01","P"); // Para que cargue l combo 
      $("#MCaj_Sucursal").focus();
  }


  function buscarCuentasCambio(){
      buscarCuentas('01','P')
  }
    
  function buscarCuentas( CuentaSelec , MonedaSelec){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      $sucursal = $("#MCaj_Sucursal").val();
      $.ajax({
            global: false,
            dataType: "json",
            data: {"sucursal": $sucursal,"cod_cuenta": CuentaSelec},
            url:   '../cajas/combo_cuenta_sucursal',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              $("#MCaj_CtaOri").html(respuesta.html);
              buscarMonedas(MonedaSelec); // Para que cargue l combo de monedas
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

  function buscarMonedasCambio(){
      buscarMonedas('P')
  }

  function buscarMonedas(MonedaSelec){
      // Carga combo moneda segun cuenta seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      $cuenta = $("#MCaj_CtaOri").val();
      $.ajax({
            global: false,
            dataType: "json",
            data: {"cuenta": $cuenta,"moneda": MonedaSelec},
            url:   '../cajas/combo_moneda_cuenta',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              $("#MCaj_Moneda").html(respuesta.html);
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


    $("#userModal").draggable({
       handle: ".modal-header"
    });

    //  JS de Formulario de Alta/ Modificacion 
      /**
     * Muestra el form de carga para el alta, ó Modificacion presentando los datos
     * en caso de que se proporcione por param. el id del registro a editar.
     * Para ambos casos se prepara según el tipo de operación a efectuar.
     * @param   is_modif        boolean     True si es modificar, false si es alta.
     * @param   id_registro     int         Id del registro para el que mostrar datos.
     */

    function showEditModalCaja(is_modif, id_registro) {

        var form = $("#save-modify-form-caja")[0];

        // Vaciar el formulario del modal.
        form.reset();

        $("#operation").val("store"); //Indica es un alta
        $("#modal-title").text("Alta de Movimientos Detallados");
        $("#msgErrModal").hide();

        if (is_modif) {
            // Si es Modificacion lee el registro para completar los campos
            var formdata = {
                id:         id_registro
            };
            $("#operation").val("update");
            $("#modal-title").text("Modificación de Movimientos Detallados");

            $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url:  '../cajas/show',            
                success: function(data){
                        var row;
                        row = data.result;
                        // Rellenar los datos sobre el registro a editar.
                        @foreach($campos_pantalla as $campoaux)
                            $("#{{$campoaux['name']}}").val(row.{{$campoaux['name']}});
                        @endforeach
                        $("#id").val(data.id);    
                        console.log ("Lee:" + $("#MCaj_Monto").val() )
                        auxmonto.set($("#MCaj_Monto").val()); 

                        $("#MCaj_Sucursal").attr("readonly", true); //no permito modif porque se complica sincornizacion con las suc
                        // Asi funciona mal porque queda desabilitado 
                        //$('#MCaj_Sucursal option:not(:selected)').attr('disabled',true);

                        buscarCuentas(row.MCaj_CtaOri,row.MCaj_Moneda)


                        $('#MCaj_Codigo').selectpicker('val', row.MCaj_Codigo)

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
        }else{
            // Alta
            iniciopantalla()
            $("#MCaj_Sucursal").attr("readonly", false);
        }


        $("#userModal").modal("show")
            // Cuando termina de mostrarse.
            .on("shown.bs.modal", function(e) {
            $("#MCaj_FecMov").select(); // Selecciona todo el texto del 1er campo.
        });
    }



</script>

@endsection()