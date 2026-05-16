<!-- Formulario de Alta/ Modificacion -->

@section('primer_campo','Cli_Documento')  <!-- Es el campo que se selecciona al abrir la ventana -->

@section('formulario_alta_modificacion')




    <div class="modal-body">
        <label class="control-label">DNI / CUIT</label>
        <div class="row">
          <div class="col-lg-4">
            <select class="form-control"  name="Cli_CodDocumento" id="Cli_CodDocumento">
                <option value="DNI">DNI</option>
                <option value="CUIT">CUIT</option>
            </select>
          </div> <!-- Fin col -->
          <div class="col-lg-8">
            <div class="input-group">
                <input type="text" class="form-control" name="Cli_Documento" id="Cli_Documento" pattern="\d{7,11}"  required title="DNI/CUIT solo válido con 7/11 dígitos." maxlength="11">
                <span class="input-group-btn">
                    <button type="button"  class="btn"
                        title="Valida Padrón Afip" onclick="validaCuitAfip()">
                        <i class="fa fa-check-circle" aria-hidden="true"></i> AFIP
                    </button>
                </span>
            </div>
          </div> <!-- Fin col -->
        </div> <!-- Fin row -->
        <div id="msgErrDNI" class="alert alert-danger" align="left" ></div>

        <br>
        <label class="control-label">Apellido y Nombres / Razón Social</label>
        <input type="text" class="form-control" name="Cli_ApeNom" id="Cli_ApeNom" maxlength="50" required>

        <br>
        <label class="control-label">Domicilio</label>
        <input type="text" class="form-control" name="Cli_Calle" id="Cli_Calle" maxlength="80">
       
        <br>
        <label class="control-label">Responsabilidad IVA</label>
        <select class="form-control" name="Cli_CodRespIVA" id="Cli_CodRespIVA">
            <option value="CF">Consumidor Final</option>
            <option value="RI">Resp. Inscripto</option>
            <option value="EX">Exento</option>
            <option value="MO">Monotributo</option>
        </select>

        <br>
        <label for="Cli_Telefono" class="form-label">Número de WhatsApp o Teléfono:</label>
        <div class="row">
          <div class="col-lg-4">
            <select class="form-control" id="Cli_Pais" name="Cli_Pais">
                <option value="+54">+54   (Argentina)</option>
                <option value="+55">+55   (Brasil)</option>
                <option value="+598">+598 (Uruguay)</option>
                <option value="F">Fijo</option>
                <!-- Agrega más opciones según sea necesario -->
            </select>
          </div> <!-- Fin col -->
          <div class="col-lg-8">
            <div class="mb-3">
             <input type="text" class="form-control" id="Cli_Telefono" name="Cli_Telefono" placeholder="Ejemplo: 3772 404245" required>
             <small class="form-text text-muted">Ingrese número completo de WhatsApp sin el código de país.</small>
            </div>
        </div> <!-- Fin col -->
        </div> <!-- Fin row -->
    




        <input type="hidden" class="form-control" name="Cli_Id" id="Cli_Id" >

    </div> <!-- FIN Modal body -->

@endsection()
<!-- FIN Formulario de Alta/ Modificacion -->

@section('scrip_alta_modif')

<script src="{{ asset('js/valida_cuit.js') }}"></script>

<script>


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

        // Manejar el envío del formulario
        $('#whatsappForm').submit(function (e) {
            e.preventDefault();
            var codigoPais = $('#Cli_Pais').val();
            var numeroWhatsApp = $('#Cli_Telefono').val();

            if (validarNumeroWhatsApp(codigoPais, numeroWhatsApp)) {
                // Aquí puedes enviar el número a tu servidor o realizar otras acciones
                alert('Número de WhatsApp válido: ' + codigoPais + numeroWhatsApp);
            } else {
                alert('Número de WhatsApp no válido. Por favor, ingrese un número válido.');
            }
        });


    $("#msgErrDNI").hide();

    function validaCuitAfip() {
        //Boton para validar y traer los datos desde la Base de AFIP
        $("#msgErrDNI").hide();
        var scuit = $("#Cli_Documento").val();        
        retorno = validaCuit(scuit); //Valido si es un cuit valido
        if (retorno != "") {
            $("#msgErrDNI").html("Cuit inválido. " + retorno );
            $("#msgErrDNI").show();
            $("#Cli_Documento").focus(); 
            return               
        }

        $("#msgErrDNI").html('Buscando en Bases de AFIP ...');
        $("#msgErrDNI").show();
        $.ajax({
            global: false, // Para no mostrar cuadro Procesando
            dataType: "json",type:  'get',
            data: {cuit: $("#Cli_Documento").val()},
            url:  "../afip/valida_cuit",
            success: function(data){
                  //      console.log (data )
                    if (data.msgError != "") {
                        $("#msgErrDNI").html(data.msgError);
                        $("#msgErrDNI").show();
                         //   $("#Prov_Cuit").focus();
                    } else {
                        $("#Cli_ApeNom").val(data.razonSocial);
                        $("#Cli_Calle").val(data.direccion);
                        $("#Cli_CodDocumento").val("CUIT")
                        $("#msgErrDNI").hide();
                        $("#Cli_Telefono").focus(); 
                    }
                },
                error:  function(xhr,err){ 
                    $("#msgErrDNI").html("Problema en la validación. Con conexion al Servidor." +xhr.responseText );
                    $("#msgErrDNI").show();
                }
        });
    } // fin validaCuit 

    // Validar si DNI ya existe al rellenar formulario de alta.
    // En éste caso, cuando el user abandona/cambia el foco del input.
    //   $("#DNI").on("blur", function() {    change funciona mejor
    $("#Cli_Documento").on("change", function() {
        $("#msgErrDNI").hide();
        // Si corresponde valido el cuit
        var scuit = $("#Cli_Documento").val();
        if(scuit.length > 9 ) {
            retorno = validaCuit(scuit);
            if (retorno == "") {
                if ( $("#Cli_CodRespIVA").val() == "CF" ) $("#Cli_CodRespIVA").val("RI")
                $("#Cli_CodDocumento").val("CUIT")
            }else{
                $("#msgErrDNI").html("Cuit inválido. " + retorno );
                $("#msgErrDNI").show();
                $("#Cli_Documento").focus(); 
                return               
            }
        }

        if ($("#operation").val() == "store" && scuit != 0   ) {
            var numdni = $(this).val();
            $.ajax({
                dataType: "json", type:'get', global: false, // Para no mostrar cuadro Procesando
                data: {dni: numdni},
                url:  "../{{$modulo_abm}}/validate_dni_exists",
                success: function(data){
                        //console.log (data)
                        if (data.existing == true) {
                            Swal.fire({
                             type: 'warning',
                             title: data.Cli_ApeNom , text: 'Ya existe con ese DNI. ' ,
                             showCancelButton: true, confirmButtonText: 'Utilizarlo!'
                            }).then((result) => {
                                if (result.value) { 
                                   $('#Cli_ApeNom').val(data.Cli_ApeNom);
                                   $('#Cli_CodRespIVA').val(data.Cli_CodRespIVA);
                                   $('#Cli_Calle').val(data.Cli_Calle);
                                   $('#Sucursal').val(data.Cli_Sucursal);
                                   $('#Cli_Id').val(data.Cli_Id);
                                   $('#id').val(data.Cli_idWEB);
                                   $('#Cli_Telefono').val(data.Cli_Telefono);
                                   $('#Cli_Pais').val(data.Cli_Pais);
                                   $("#operation").val("update");
                                   $("#modal-title").text("Modificar  Id:" + data.Cli_idWEB  );                                    
                                }// Confirmacion         
                            })
                            $("#cli_documento").focus();
                        }
                },
                error:  function(xhr,err){ 
                    msgerror("Problema en la validación. Con conexion al Servidor." +xhr.responseText);
                }
            });
        }
    }); // Fin Validar DNI

</script>
@endsection <!-- Fin scrip_alta_modif -->