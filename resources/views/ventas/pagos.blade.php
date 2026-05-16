@extends('template.main_alta_modal')

@section('titulo','Venta Forma de Pago')
   
@section('contenido')

<div id="msgErrAfip"  class="alert alert-danger" align="left" ></div>
<!-- Panel General del Nuevo Comprobante -->
<div class="panel panel-info">   

    <div class="panel-heading">
        <b> FORMA DE PAGO </b>

    </div>   <!-- /Fin Header Panel  -->

    <div class="panel-body">          

    <div class="form-group row">   
        <div class="col-xs-3" style="padding-right:0px">     
            <div class="input-group">
                <strong style="font-size: 20px;">Total: </strong>
                <span class="input-group-addon">$</span>
                <input type="text" class="ctl-label text-right" style="font-size: 20px;width: 140px;" name="total" id="total" value="0" readonly>
            </div>                          
          </div>                          
         </div> <!-- /Fin  row  -->

    <form id="formularioPrincipal" autocomplete="off" role="form" onkeypress="return event.keyCode != 13;">

      <hr>
      <!--=   ENTRADA MÉTODO DE PAGO  Ayudado con chatGpt-->
      <h4>Selección Método de Pago</h4>

<div class="form-group row">
  <div class="col-xs-2" style="padding-right:0px">
    <div class="input-group">
      <select class="form-control" id="metodoPago" name="metodoPago" required>
        <option value="" disabled selected>Seleccione un método</option>
        <option value="P" >Pesos</option>
        <option value="T">Tarjeta Crédito</option>
        <option value="TB">Transferencia Bancaria</option>
        <option value="CC">Cuenta Corriente</option>
      </select>
    </div>
  </div> <!-- /Fin Col  -->
  <!-- Contenedor dinámico de opciones -->
  <div class="col-xs-2" style="padding-right:0px">
    <div id="opcionesPago" class="mt-3">
        <!-- Aquí se insertan dinámicamente los formularios según el método de pago seleccionado -->
    </div>
   </div> <!-- /Fin Col  -->
   <div class="col-xs-2" style="padding-right:0px">
      <button id="addBtnPago" class="btn btn-primary mt-3" onclick="agregarMetodoPago()">Añadir Método de Pago</button>
   </div> <!-- /Fin Col  -->
</div>

<div class="row"> <!-- Tabla de Detalle que se van cargando -->
<div class="col-lg-7 col-md-7">
<table id="tbl-pagos" class="table table-bordered table-hover mt-3" >
  <thead>
    <tr>
      <th>Método</th>
      <th>Detalle</th>
      <th>Monto</th>
      <th style="text-align: center;" data-halign="center"><span class="fa fa-wrench"></span></th>
    </tr>
  </thead>
  <tbody>
    <!-- Las filas se completarán dinámicamente -->
  </tbody>
</table>

</div> <!-- /Fin Col  -->

</div> <!-- /Fin row  -->

    </div>   <!-- /Fin Header Panel  -->

    <div class="panel-footer"> 
      <div class="form-group row">   
        <div class="col-xs-3" style="padding-right:0px">     
            <div class="input-group">
                <strong style="font-size: 20px;">Total: </strong>
                <span class="input-group-addon">$</span>
                <input type="text" class="ctl-label text-right" style="font-size: 20px;width: 140px;" name="total" id="total" value="0" readonly>
            </div>                          
          </div>                          
          <div class="col-xs-4" style="padding-right:0px">     
           <div id="opcionesPagoResumen" >
        <!-- Aquí se insertan dinámicamente los formularios según el método de pago seleccionado -->
            </div>
          </div>                          
          <div class="col-xs-4" style="padding-right:0px">     

            <button type="button" id="VentaButton" class="btn btn-success pull-right" onClick="FinalizaVenta()">Finaliza Venta</button>
           </div>                          
        </div> <!-- /Fin  row  -->
    </div> <!-- /Fin  Foter  -->

  </form>


</div> <!-- /Fin Panel General-->

<?php
   include( base_path() . "/resources/views/clientes/campos.php");
?>
@include('clientes.alta_modif')

@endsection <!-- Fin Contenido -->

@section('scrip')

<script>

    // http://autonumeric.org/
    // Configurar AutoNumeric de los campo de entrada
        const numeroInput = document.getElementById('cantidad');
        const auxcantidad = new AutoNumeric(numeroInput, {  digitGroupSeparator: '.' , decimalCharacter: ',', decimalPlaces: 0 } );

        const numeroInput2 = document.getElementById('precio_unitario');
        const auxprecio_unitario = new AutoNumeric(numeroInput2, 'commaDecimalCharDotSeparator');

        const numeroInput3 = document.getElementById('bonif_unitario');
        const auxbonif_unitario = new AutoNumeric(numeroInput3, {  digitGroupSeparator: '.' , decimalCharacter: ',', decimalPlaces: 0 } );


    //Manejo metodos de Pago - Ayuda ChatGPT 12/2024

  let totalPagar = 0; // Monto total a pagar
  let faltaPagar = 0; // Saldo a pagar
  let pagosRealizados = []; // Lista de pagos realizados

  document.addEventListener("DOMContentLoaded", () => {
    actualizarTotales();
  });

  // Función para añadir un método de pago a la tabla
  function agregarMetodoPago() {
    const metodoSeleccionado = document.getElementById("metodoPago").value;
    const opcionesPago = document.getElementById("opcionesPago");
    const inputs = opcionesPago.querySelectorAll("input");
    let detalle = "";
    let monto = 0;

    totalPagar = venta.total
    console.log( "agregarMetodoPago" , totalPagar , venta.total ) 

    inputs.forEach((input) => {
      if (input.name.includes("Monto") || input.name.includes("monto")) {
        monto = parseFloat(input.value);
      } else {
        detalle += `${input.value} `;
      }
    });

    if (!monto || monto <= 0) {
      alert("Por favor, ingrese un monto válido.");
      return;
    }

    if (pagosRealizados.reduce((acc, pago) => acc + pago.monto, 0) + monto > totalPagar) {
      alert("El monto total supera el requerido.");
      return;
    }

    // Agregar el pago al array
    pagosRealizados.push({ metodo: metodoSeleccionado, detalle, monto });

    // Actualizar la tabla
    actualizarTabla();
    actualizarTotales();

    // Limpiar campos
    document.getElementById("metodoPago").value = "";
    opcionesPago.innerHTML = "";
  }

  // Función para actualizar la tabla de Pagos
  function actualizarTabla() {
    const table = document.querySelector("#tbl-pagos");
    table.classList.remove("hidden"); //Visible la tabla

    const tbody = document.querySelector("#tbl-pagos tbody");
    tbody.innerHTML = ""; // Limpiar tabla

    pagosRealizados.forEach((pago, index) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${obtenerNombreMetodo(pago.metodo)}</td>
        <td>${pago.detalle}</td>
        <td>${pago.monto.toFixed(2)}</td>
        <td align="center">
            <button type="button" class="btn btn-xs btn-danger" onclick="eliminarPago(${index})">
                <i class="glyphicon glyphicon-trash"></i>
            </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Función para actualizar totales
  function actualizarTotales() {
   
    const enableButton = document.getElementById("VentaButton");
    
    $('#total').val( formatearNumeroConSeparadorDeMiles( venta.total, 2 ));
    totalPagar = venta.total

    const totalRealizado = pagosRealizados.reduce((acc, pago) => acc + pago.monto, 0);
    const diferencia = totalPagar - totalRealizado;
    faltaPagar = diferencia
    let resumen = ""
    if (totalPagar > 0) {
     //   auxmontoPesos.set(totalRealizado)
     //   $('#montoPesos').val( formatearNumeroConSeparadorDeMiles( totalRealizado, 2 ));
        resumen = `
      <div class="alert ${diferencia > 0 ? "alert-warning" : "alert-success"}">
        Total PagoRealizado: $${totalRealizado.toFixed(2)}<br>
        ${diferencia > 0 ? `Faltan: $${diferencia.toFixed(2)}` : "El monto está completo."}
      </div>
    `;
     VentaButton.disabled = false; //Activo el boton Vender
    }else{
        // No cargo nada , esta todo en cero
        const tbody = document.querySelector("#tbl-pagos");
        tbody.classList.add("hidden"); // Oculto tabla de pagos
        VentaButton.disabled = true; //Desactivo el boton Vender
        addBtnPago.style.display = "none"; //Oculto el Boton 
    }
    document.getElementById("opcionesPagoResumen").innerHTML = resumen;
 
  }

  // Función para eliminar un pago
  function eliminarPago(index) {
    pagosRealizados.splice(index, 1);
    actualizarTabla();
    actualizarTotales();
  }

  // Función para obtener el nombre del método de pago
  function obtenerNombreMetodo(codigo) {
    switch (codigo) {
      case "P":
        return "Pesos";
      case "T":
        return "Tarjeta Crédito";
      case "PP":
        return "PayPal";
      case "TB":
        return "Transferencia Bancaria";
      default:
        return "Otro";
    }
  }




document.getElementById("metodoPago").addEventListener("change", function () {
    const metodo = this.value;
    const opcionesPago = document.getElementById("opcionesPago");
    opcionesPago.innerHTML = ""; // Limpiar contenido anterior

    console.log(metodo  ) 
    addBtnPago.style.display = "inline-block"; //Visualizo el Boton 

    let html = "";
    switch (metodo) {
      case "P": // Pesos
        html = `
            <div class="input-group">
                <span class="input-group-addon">$</span>
                <input class="form-control text-right" type="number" id="montoPesos" value="${faltaPagar}" name="montoPesos" placeholder="Ingrese el monto">
          </div>`;

          break;
      case "T": // Tarjeta de Crédito
        html = `
          <div class="form-group">
            <label for="numTarjeta">Número de Tarjeta:</label>
            <input type="text" class="form-control" id="numTarjeta" name="numTarjeta" placeholder="Ingrese el número de tarjeta" required>
          </div>
          <div class="form-group">
            <label for="montoTarjeta">Monto:</label>
            <input type="number" class="form-control" id="montoTarjeta" name="montoTarjeta" placeholder="Ingrese el monto" required>
          </div>`;
        break;
      case "TB": // Transferencia Bancaria
        html = `
          <div class="form-group">
            <label for="numCuenta">Número de Cuenta:</label>
            <input type="text" class="form-control" id="numCuenta" name="numCuenta" placeholder="Ingrese el número de cuenta" required>
          </div>
          <div class="form-group">
            <label for="montoTransferencia">Monto:</label>
            <input type="number" class="form-control" id="montoTransferencia" name="montoTransferencia" placeholder="Ingrese el monto" required>
          </div>`;
        break;
      default:
        html = "";
    }

    opcionesPago.innerHTML = html;
  });



    // Gestion de Clientes
    // -------------------

    function searchByFormdata() {
        // Se ejecuta despues de boton modificar cliente , o de buscar 1 cli
        // Actualiza el cuadro  con los datos del cliente

        $("#modif-cliente-btn").show ();
        $("#datos_cliente").show();
        if ($("#operation").val() == "store"){
            $('#id_cliente').val( $("#id").val()); // Campos de pantalla externa Id en Sucursal Alta = Web
        }else{
            $('#id_cliente').val( $("#Cli_Id").val()); // Campos de pantalla externa Id en Sucursal
        }
        $('#id_clienteweb').val( $("#id").val()); // Campos de pantalla externa Id Web (Campo Oculto)
      
        console.log($("#id").val() , $("#Cli_Id").val() ,$("#operation").val() )
        $('#nombre_cliente').html($("#Cli_ApeNom").val());

       // if ( $('#Cli_Documento').val().length == 11 ) {
      //      $('#dni_cliente').html("CUIT:" + $("#Cli_Documento").val() );
      //  }else{
      //      $('#dni_cliente').html("DNI:" + $("#Cli_Documento").val() );
      //  }        

      //  $('#id_cliente').val( "hola"); // Campos de pantalla externa

        $('#dni_cliente').html( $("#Cli_CodDocumento").val() + ":" + $("#Cli_Documento").val() );
        $('#telefono_cliente').html("Teléfono:" + $("#Cli_Pais").val() + " " + $("#Cli_Telefono").val());    
    }

    function BtnModificaCliente() { 
        if ( $('#id_cliente').val().length < 1 || isNaN($('#id_cliente').val())) {
            //alert('El número de teléfono debe tener al menos 9 números.');
            return false
        }else{
            $("#msgErrDNI").hide();
            showEditModal(true,  $('#id_clienteweb').val() );
        }    
    }

    function BtnNuevoCliente() {
        // Abre la ventana modal de Alta de Clientes
        $("#msgErrDNI").hide();
        showEditModal(false, 0);
    }


    // ===================================
    // Bloque en el que se administran algunos datos de una forma aislada
    // para evitar dependencia con cualquier UI.
    // ===================================
    var tipo_de_factura = "" 
    var venta = {
            items: {},
            pagos: {},
            items_count: 0,
            pagos_count: 0,            
            total: 0,
            values_iva: [],

            calculaPrecioConIva: function(precio, porc) {
                    return precio + ((precio * porc) / 100);
            },


            addPago: function(moneda, monto, cuotas, cotizacion) {
                    this.pagos[this.pagos_count] = {
                            moneda: moneda,
                            monto: monto,
                            cuotas: cuotas,
                            cotizacion: cotizacion
                    };
                    this.pagos_count++;
                    return (this.pagos_count - 1);
            },

            /**
             * Ingresa un producto/servicio al arreglo, lo suma al total y
             * devuelve el indice del nuevo item.
             */
            addItem: function(id_producto, id_familia, descrip_producto, id_iva, cantidad, precio_unitario , bonif_unitario,precio_total  ) {
                    var porc = this.values_iva[id_iva];
                    var precio_con_iva = this.calculaPrecioConIva(precio_unitario, porc);
                  //  var precio_total = precio_unitario * cantidad * ( 1 - ( bonif_unitario / 100) )
                    
                    this.items[this.items_count] = {
                            id_producto: id_producto,
                            id_familia: id_familia,
                            descrip_producto: descrip_producto,
                            id_iva: id_iva,
                            cantidad: cantidad,
                            precio_unitario: precio_unitario ,
                            bonif_unitario: bonif_unitario ,
                            precio_total: precio_total   ,
                            precio_con_iva: precio_con_iva
                    };

                    this.total +=  precio_total ;

                    console.log( this.total , precio_total  )
                    // Incrementar para el siguiente ingreso.
                    this.items_count++;

                    return (this.items_count - 1);
            },

            deleteItem: function(key) {
                    this.total -=  ( this.items[key].precio_unitario * this.items[key].cantidad);
                    delete this.items[key];
            }

    };

    // ===================================
    // Bloque funcional de la pagina, en el que manejar interacciones e interfaz.
    // ===================================

    function  busca_articulo() {
            //no pude hacer funcionar el btn de busqueda

    }

    function ingresar_pagos() {

        var total =  Number( numberFormatBd( $('#total').val() ) );

        var key_item = venta.addPago($('#metodoPago').val() , total , 1, 1);
        // key_item = venta.addPago("R", 10 , 1, 13);
    }   

    function ingresar_articulo() {

        var id_producto = $('#id_producto').val();
        var id_familia = $('#id_familia').val();
        var descrip_producto = $('#descrip_producto').val();
        var id_iva = 5; // 21%
        var texto_iva = $("#id_iva option[value=" + id_iva + "]").text();
//        var cantidad = Number( $('#cantidad').val() );
//        var precio_unitario = Number( $('#precio_unitario').val() );
        var cantidad =  Number( numberFormatBd( $('#cantidad').val() ) );
        var precio_unitario = Number( numberFormatBd( $('#precio_unitario').val() ) );
        var bonif_unitario = Number( numberFormatBd( $('#bonif_unitario').val() ) );
        var precio_total = Number( precio_unitario * cantidad * ( 1 - ( bonif_unitario / 100) ) ) ;

        if (id_producto  == "" || descrip_producto   == "" ) {
            Swal.fire({
                type: 'error',
                title: 'Error: Ingrese el código y Descripción de Articulos',
                text: 'Debe ingresar el código y Descripción del Articulo a la Venta !',
                footer: ''
            })
            return false;
        }
        if (cantidad == 0 || precio_unitario == 0 ) {
            Swal.fire({
                type: 'error',
                title: 'Error: Ingrese la Cantidad y Precio de Articulos',
                text: 'Debe ingresar Cantidad y Precio  mayor a cero de Articulos a la Venta !',
                footer: ''
            })
            return false;
        }

        // Agregar a nuestro gestor que mantendra los datos.
        var key_item = venta.addItem(id_producto, id_familia, descrip_producto, id_iva, cantidad, precio_unitario,  bonif_unitario, precio_total);

        // Agregar entonces al table de la pantalla.
        cant = formatearNumeroConSeparadorDeMiles(cantidad , 0)
        precio = formatearNumeroConSeparadorDeMiles(precio_unitario , 2)
        bonif = formatearNumeroConSeparadorDeMiles(bonif_unitario , 0)
        preciotot = formatearNumeroConSeparadorDeMiles(precio_total , 2 )

        var htm_row = `
            <tr id="row-${key_item}">
                <td align="right">${cant}</td>
                <td>${descrip_producto}</td>
                <td align="right">${precio}</td>
                <td align="right">${bonif} %</td>
                <td align="right">${preciotot} </td>
                <td align="center">
                        <button type="button" class="btn btn-xs btn-danger" onclick="borrar_articulo(${key_item})">
                                <i class="glyphicon glyphicon-trash"></i>
                        </button>
                </td>
            </tr>
        `;
        $('#tbl-items tbody').append(htm_row);  


        actualizarTotales();


        // Se pocisiona para seguir ingresando
       // $('#id_familia option').first().prop('selected', true);
        $('#id_producto, #descrip_producto').val('');
        //$('#cantidad').val('');
        auxcantidad.clear();
        auxprecio_unitario.clear();
        auxbonif_unitario.clear();
        $('#id_familia').focus();
    }

    function borrar_articulo(key) {
        venta.deleteItem(key);
        $('#row-'+key).remove();
        actualizarTotales();
    }

    
    $(document).ready(function() {

        $("#datos_cliente").hide(); //Oculto cuadro de datos del cliente
        $("#modif-cliente-btn").hide();

        // Busqueda Automatica de Cliente
        $('#id_cliente').typeahead({
            items: 10,
            minLength: 3,
            highlight: true,
            // Traer de un ajax.
            source: function(query, process) {

                    $.ajax({
                        global: false,
                        dataType: "json",
                        data: {},
                        url:   '../clientes/busca_autocompletar?terms='+query,
                        type:  'get',
                        success: function(data){
                            if(data.length<=0){
                                // Si no encuentra nada
                                $("#datos_cliente").show();
                                $('#nombre_cliente').html("No se encuentra coincidencia");
                                $('#dni_cliente').html("");
                                $('#telefono_cliente').html("");
                            }
                            return process(data);
                        },
                        error:  function(xhr,err){ 
                            $("#datos_cliente").show();
                            $('#nombre_cliente').html("Error al buscar" );
                           //msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
                        }
                    });
            },
            // Al seleccionar.
            afterSelect: function(item) {
                    $("#datos_cliente").show();
                    $("#modif-cliente-btn").show ();
                    $('#id_cliente').val(item.idSUC);
                    $('#id_clienteweb').val(item.id);
                    $('#nombre_cliente').html(item.apenom);
                    $('#dni_cliente').html(item.coddocumento + ":" + item.documento);
                    $('#telefono_cliente').html("Teléfono:" + item.telefono);
                    $('#id_familia').focus();
            }
        }); //Fin Busqueda Cliente

        // Busqueda Automatica de Productos
        $('#id_producto').typeahead({
            items: 15,
            minLength: 2,
            highlight: true,
            // Traer de un ajax.
            source: function(query, process) {

                var familia = $('#id_familia').val();
                if( familia == 'VAR' && query == '99') {
                   return // Ingresa manualmente     
                }else{
                    $.ajax({
                        global: false,
                        dataType: "json",
                        data: {},
                        url:   '../productos/buscaproducto?terms='+query+'&familia='+familia,
                        type:  'get',
                        success: function(data){
                                   return process(data);
                        },
                        error:  function(xhr,err){ 
                           msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
                        }
                    });
                }    

            },
            // Al seleccionar.
            afterSelect: function(item) {
                    $('#id_producto').val(item.id);
                    $('#descrip_producto').val(item.name);
                 //   $('#cantidad').val(1);
                    auxcantidad.set(1);
                    auxprecio_unitario.set(item.precio);
                    $('#cantidad').focus();
            }
        }); //Fin Busqueda Producto







            $('#id_familia').keyup(function(e) {
                    if (e.which == 13) {
                            $('#id_producto').focus();
                    }
            });

            $('#id_producto').keyup(function(e) {
                    if (e.which == 13) {
                            var id_familia = $('#id_familia').val();
                            var id_producto = $('#id_producto').val();
                            if (id_familia == 'VAR' && id_producto == '99') {
                            /* NO ME FUNCIONA EL MANDAR EL FOCO AL SIG CAMPO
                                 Swal.fire({
                                   title: 'Ingrese una descripción',
                                   input: 'text',
                                   inputPlaceholder: 'Ingrese descripción del Articulo',
                                   inputValue: '',
                                   showCancelButton: true,
                                   inputValidator: (value) => {
                                    if (!value) {
                                      return 'Tiene que Ingresar una descripción!'
                                    }else{
                                     $('#descrip_producto').val(value);
                                     $('#cantidad').focus();  // ver no anda                                 
                                    }
                                   }
                                })
                              //  if (descrip) {
                                //    Swal.fire(descrip)
                             //   }
                             */
                                var descrip = prompt('Ingrese una descripción');
                                $('#descrip_producto').val(descrip);
                                $('#cantidad').focus();  
                            }
                    }       
            });

            $('#cantidad').keyup(function(e) {
                if (e.which == 13) {
                    $('#precio_unitario').focus();
                }
            });

            $('#precio_unitario').keyup(function(e) {
                if (e.which == 13) {
                  $('#bonif_unitario').focus();
                }
            });

            $('#bonif_unitario').keyup(function(e) {
                if (e.which == 13) {
                    ingresar_articulo();
                }
            });

            $(document).keyup(function(e) {
                if (e.which == 120) {   // F9
                    tipo_de_factura = "Z"  // Indica tipo de Facturacion     
                    FinalizaVenta();
                }
            });

    });

    function FinalizaVenta() {

        //Validaciones
        if (Object.keys(venta.items).length == 0) {
            Swal.fire({
                type: 'error',
                title: 'Error: Ingrese los Articulos',
                text: 'Debe cargar algún Articulo a la Venta !',
                footer: ''
            })
            return false;
        }

        // temporal aqui
        ingresar_pagos();

        // Si son Notas de Credito pedir Datos Originales
        console.log(tipo_de_factura)
        if (tipo_de_factura != "Z") tipo_de_factura = $('#id_tipo_cbte').val()
        numeroOrig = ""
        punto_facturaOriginal = ""
        if(tipo_de_factura == 3  || tipo_de_factura == 8) {
            punto_facturaOriginal = prompt('Ingrese Punto de Venta Original:','');
            numeroOrig = prompt('Ingrese Nro.Comprobante Original:','');
        }


        Swal.fire({
                  title: 'Finalizar Venta ? <br>  Por un Total $' + $('#total').val()  ,
                  text: "Esta Seguro de Cerrar esta Venta!",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonText: 'Cancelar',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Si, Finalizar!'
            }).then((result) => {
                  if (result.value) {
               
                    var json_items = JSON.stringify(venta.items);
                    $('#json_items').val(json_items);

                    var json_pagos = JSON.stringify(venta.pagos);
                    $('#json_pagos').val(json_pagos);


                var formdata = {
                   sucursal: $('#sucursal').val(),
                   id_vendedor: $('#id_vendedor').val(),
                   id_tipo_cbte: tipo_de_factura,
                   numeroOrig: numeroOrig,
                   punto_facturaOriginal: punto_facturaOriginal,
                   fecha: $('#fecha').val(),
                   id_cliente: $('#id_cliente').val(),
                   json_items:         json_items,
                   json_pagos:         json_pagos
                };


            $.ajax({                
                dataType: "json",type:  'get', data: formdata,
                url:  'store',            
                success: function(data){
                    if(data.retError == "" || data.retError == null) {
                        // ir a la pantalla del pdf
                        if(data.pdf != "" ) {
                            window.open(data.pdf, '', '_blanck');
                        }else{
                           if(data.errorAFIP != "" ) {
                            // Si da error de Afip reintento
                            window.open('generaComprobanteAFIP?id='+ data.id + '&tipo=FC', '', '_blanck'); 
                           }                            
                        }    
                        //Ok se grabo bien limpiar pantalla
                        inicializar();
                        muestroMsg( data.mensaje);    
                    }else{
                        msgerror( data.retError);
                    }
                },
                error:  function(xhr,err){ 
                    msgerror( xhr.responseText);
                } // Fin si hay error
            }); // Fin llamado Ajax
            }
        })  // Confirmacion              
    
    } // finalizaVenta 
        
    function inicializar() {

        var VALUES_IVA = {
          5 : 21, 
          4 : 10.5,
          1 : 0
        };

        tipo_de_factura = ""
        venta.values_iva = VALUES_IVA  ;

        $('#id_cliente').val("")
        $("#datos_cliente").hide(); //Oculto cuadro de datos del cliente
        $("#modif-cliente-btn").hide();

        for(var i=0;i< venta.items_count ;i++)
        {
            $('#row-'+i).remove();
        }  
      
        venta.items =  {}
        venta.pagos = {}
        venta.items_count = 0
        venta.pagos_count =  0            
        venta.total = 0

        $('#total').val("0")

    } //fin inicializar

    function valida_estado_servidor() {
        $("#msgErrAfip").hide();
        $.ajax({
            dataType: "json",type:  'get',
            url:  '../afip/valida_estado_servidor',            
            success: function(data){
                if(data.msgError == "" || data.msgError == null) {
                    //Ok se conecto bien, no muestro nada
                }else{
                   $("#msgErrAfip").html("Afip:" + data.msgError + '<br>' +  data.informacion );
                   $("#msgErrAfip").show();
                }
            },
            error:  function(xhr,err){ 
                msgerror( "Al Validar Afip:" + xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax Afip
    } // Fin valida_estado_servidor

    // Se ejecuta al finalizar la carga de la pantalla
    inicializar();
    valida_estado_servidor();

</script> 

@endsection <!-- Fin scrip -->