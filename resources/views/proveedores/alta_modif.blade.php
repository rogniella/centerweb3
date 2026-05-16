<!-- Formulario de Alta/ Modificacion -->

@section('primer_campo','Prov_Cuit')  <!-- Es el campo que se selecciona al abrir la ventana -->

@section('formulario_alta_modificacion')

    <div class="modal-body">
        <div class="form-group">
          <label class="control-label col-md-2 text-right">Cuit:</label>
          <div class="col-md-8">
            <input type="text" class="form-control" name="Prov_Cuit" id="Prov_Cuit" maxlength="11">
          </div>
          <div class="col-md-1">
                    <button type="button"  class="btn"
                        title="Afip" onclick="validaCuitAfip()">
                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                    </button>
          </div>
          
        </div>
        <div class="form-group">
          <label class="control-label col-md-2 text-right">Raz.Social:</label>
          <div class="col-md-10">
            <input type="text" class="form-control" name="Prov_RazSocial" id="Prov_RazSocial" required maxlength="50">
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2 text-right">Nom.Fantacia:</label>
            <div class="col-md-10">
                <input type="text" class="form-control" name="Prov_NomFant" id="Prov_NomFant" maxlength="50" required>
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Teléfono:</label>
            <div class="col-md-4">
                <input type="text" class="form-control" name="Prov_Telefono" id="Prov_Telefono" maxlength="30">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-md-2">Dirección:</label>
          <div class="col-md-10">
                <input type="text" class="form-control" name="Prov_Calle" id="Prov_Calle" maxlength="80">
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-md-2">Email:</label>
          <div class="col-md-10">
                <input type="email" class="form-control" name="Prov_EMail" id="Prov_EMail" maxlength="50">
          </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-2">Imputación:</label>
            <div class="col-md-4">
                <select class="form-control" name="Prov_CtaCon" id="Prov_CtaCon">
                    <option value="1">Materia Prima</option>
                    <option value="2">Gastos</option>
                    <option value="3">Bienes Durables</option>
                </select>
            </div>  

        <label class="control-label col-md-2">Tipo Prov:</label>
            <div class="col-md-4">
        <select class="form-control" name="Prov_TipoProv" id="Prov_TipoProv">
            <option value=" "> </option>
            <option value="LA">Laboratorio</option>
        </select>
            </div>  

        </div>   

        <div class="form-group">
          <label class="control-label col-md-2">Notas:</label>
          <div class="col-md-10">
                <input type="text" class="form-control" name="Prov_Observ" id="Prov_Observ" maxlength="50">
          </div>
        </div>
        
    </div> <!-- FIN Modal body -->

@endsection()
<!-- FIN Formulario de Alta/ Modificacion -->

@section('scrip_alta_modif')

<script src="{{ asset('js/valida_cuit.js') }}"></script>

<script>

    function validaCuitAfip() {

        $.ajax({
            //global: false, // Para no mostrar cuadro Procesando
            dataType: "json",
            data: {cuit: $("#Prov_Cuit").val()},
            url:  "../afip/valida_cuit",
                    type:  'get',
                    success: function(data){
                        console.log (data )
                        if (data.msgError != "") {
                            $("#msgErrModal").html(data.msgError);
                            $("#msgErrModal").show();
                         //   $("#Prov_Cuit").focus();
                        } else {
                            $("#msgErrModal").hide();
                            $("#Prov_RazSocial").val(data.razonSocial);
                            $("#Prov_NomFant").val(data.razonSocial);
                            $("#Prov_Calle").val(data.direccion);
                        }
                    },
                    error:  function(xhr,err){ 
                        $("#msgErrModal").html("Problema en la validación. Con conexion al Servidor." +xhr.responseText );
                        $("#msgErrModal").show();
                    }
         });




    }

    // También se utiliza para inicializar componentes de javascript.
        $("#Prov_Cuit").on("change", function() {
            var scuit = $(this).val();
            retorno = validaCuit(scuit);
            if (retorno == "") {
                $("#msgErrModal").hide();

                if ($("#operation").val() == "store") {
                  var num = $(this).val();
                  $.ajax({
                    global: false, // Para no mostrar cuadro Procesando
                    dataType: "json",
                    data: {cuit: num},
                    url:  "../{{$modulo_abm}}/validate_cuit_exists",
                    type:  'get',
                    success: function(data){
                        if (data.existing == true) {
                            $("#msgErrModal").html("Ya existe un registro con éste Cuit. " + data.Prov_RazSocial);
                            $("#msgErrModal").show();
                            $("#Prov_Cuit").focus();
                        } else {
                            $("#msgErrModal").hide();
                        }
                    },
                    error:  function(xhr,err){ 
                        $("#msgErrModal").html("Problema en la validación. Con conexion al Servidor." +xhr.responseText );
                        $("#msgErrModal").show();
                    }
                  });
               }

            }else{
                $("#msgErrModal").html("Cuit inválido. " + retorno );
                $("#msgErrModal").show();
                $("#Prov_Cuit").focus();                
            }
        }); // Fin Validar Cuit

</script>
@endsection <!-- Fin scrip_alta_modif -->