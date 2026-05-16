 <script>

    // Para que permita mover venta Modal   
    $("#userModal_AltaCristal").draggable({
      handle: ".modal-header"
    });

    // Campos Pantalla Modal Add Cristal
    // ----------------------------------
    $('#add_cil').keyup(function(e) {
        //   $(this).val($(this).val().replace(',', '.'));
        //   $(this).val($(this).val().replace('+', ''));
        if (e.which == 13) {
           $("#unidad").focus(); // Saco el foco del btn por si apreta 2 veces la tecla enter
            ingresar_cristal();
        }
    });
    $('#unidad').keyup(function(e) {
        if (e.which == 13) {
            $('#add_esf').focus();
        }
    });
    $('#add_esf').keyup(function(e) {
        // hago asi porque en alguna maq anda mal el +
        // $(this).val($(this).val().replace('+', ''));
        if (e.which == 13) {
            $('#add_cil').focus();
        }
    });
    //  Fin Add Cristales

    function showAltaCristal() {
       //$("#add_factor").val( $("#factor").val() )
       $("#msg2VentanaAddItem1").hide();
       $("#msg2VentanaAddItem").hide();
       $("#msg2ErrorVentanaAddItem").hide();
       // Abre la ventana, y permite salir con Esc
//       $("#userModal_AltaCristal").modal({ keyboard: true, show: true})
       $("#userModal_AltaCristal").modal("show")
            // Cuando termina de mostrarse, selecciono el 1re campo.
            .on("shown.bs.modal", function(e) {
              //no funciona ??  $("#id_material").select(); 
              $("#unidad").select(); 
       });                    
    }   

  

    function ingresar_cristal(  )  {

        // Tomo los datos de entrada
        $('#btnAddCristal').prop('disabled', true);
        $("#add_ind_alta").val('CRI')

        prod = codigoCristal( $("#id_material").val() , $("#id_color").val() , $("#add_esf").val() ,  $("#add_cil").val() )
 
        $("#id_familia").val('CRI')
        $("#id_producto").val(prod)

        $("#msg2VentanaAddItem1").html( '<b>Úlimo Item ingresado: </b>'  +  $("#id_producto").val()  + '  ' + $("#descrip_producto").val());
        $("#msg2VentanaAddItem1").show();

        $idcompra = $("#idcompra").val();
        $sucursal = $("#sucursal").val();

        $.ajax({
            global: false,
            dataType: "json",
            data: { idcompra: $idcompra 
                    , sucursal: $sucursal
                    , ind_alta: $("#add_ind_alta").val()
                    , fila: 0
                    , familia: $("#id_familia").val()
                    , idprod: $("#id_producto").val()
                    , cantidad: $("#unidad").val()
                },
            url:   '../compras/AddItem',
            type:  'get',
            success: function(data){
                if(data.error != '') {
                    $("#msg2ErrorVentanaAddItem").html( '<b>ERROR en Úlimo Item ingresado: </b>' + data.error )
                    $("#msg2ErrorVentanaAddItem").show()                               
                }else{
                    $("#descrip_producto").val(data.descripcion)
                    $("#msg2VentanaAddItem1").html( '<b>Úlimo Item ingresado: </b>' + $("#id_producto").val()  + '  ' + $("#descrip_producto").val());
                    $("#msg2VentanaAddItem1").show();
                    $("#jsTabla").jsGrid("render").done(function() {
                     console.log("rendering completed and data loaded");
                    });
                    $("#msg2ErrorVentanaAddItem").hide();
                    $('#add_esf').val('');
                    $('#add_cil').val('');
                    $("#unidad").select(); // Hace foco y selecciona el contenido 
                }
                $('#btnAddCristal').prop('disabled', false);
            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
                $('#btnAddCristal').prop('disabled', false);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin CargaItems()

</script>