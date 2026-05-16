<script>

    // Para que permita mover    
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
    function showEditModal(is_modif, id_registro) {
        var form = $("#save-modify-form")[0];

        // Vaciar el formulario del modal.
        form.reset();

        $("#operation").val("store"); //Indica es un alta
        $("#modal-title").text("Nuevo");
        $("#msgErrModal").hide();

        if (is_modif) {
            // Si es Modificacion lee el registro para completar los campos
            var formdata = {
                id: id_registro
            };
            $("#operation").val("update");

            $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url:  '../{{$modulo_abm}}/show',            
                success: function(data){
                        $("#modal-title").text("Modificar  Id:" + data.id ); 
                        var row;
                        row = data.result;
                        // Rellenar los datos sobre el registro a editar.
                        @foreach($campos_pantalla as $campoaux)
                            $("#{{$campoaux['name']}}").val(row.{{$campoaux['name']}});
                        @endforeach 
                        $("#id").val(data.id);                
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
        }

        $("#userModal").modal("show")
            // Cuando termina de mostrarse.
            .on("shown.bs.modal", function(e) {
            $("#@yield('primer_campo')").select(); // Selecciona todo el texto del 1er campo.
        });
    }

    // Uno de los usos importantes del ready() es de declarar allí los "listeners"
    // de eventos como submit, click, etc.
    // También se utiliza para inicializar componentes de javascript.
    $(document).ready(function() {

        // Al Aceptar el formulario de nuevo/modif.
        $("#save-modify-form").on("submit", function(event) {

            // Cancelar. Dado que vamos a manejar nosotros el envío.
            event.preventDefault();

            // En el form está definido el tipo de operación.
            var action_name = '../{{$modulo_abm}}/' +  $("#operation").val();

            // Obtener todos los datos cargados.
            var form = $("#save-modify-form")[0];
            var formdata = $(form).serialize();

            $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url: action_name,            
                success: function(data){
                        $("#userModal").modal("hide");
                        muestroMsg(data.ret,1000);
                        $("#id").val(data.id);                
                        searchByFormdata(); // Recargar la busq. o actualiza lo seleccionado
                },
                error:  function(xhr,err){ 
                    var mensaje= xhr.responseText;

                    //console.log (xhr.responseJSON.message);
                    //console.log (xhr.responseText);

                    if (typeof xhr.responseJSON.errors != "undefined"){ //las validaciones de input   
                        mensaje="";
                        $.each(xhr.responseJSON.errors, function(key, value){   
                          mensaje = mensaje + '<p>'+value+'</p>'
                        });
                    }else{
                      if (typeof xhr.responseJSON.message != "undefined"){ //los abort generados   
                          mensaje =  '<p>'+xhr.responseJSON.message+'</p>'
                        }
                    };  
                    $("#msgErrModal").html(mensaje);
                    $("#msgErrModal").show();
                } // Fin si hay error
            }); // Fin llamado Ajax

        }); // Fin de Aceptar el formulario de nuevo/modif.

    }); // Fin de document).ready

</script>

@yield('scrip_alta_modif')
