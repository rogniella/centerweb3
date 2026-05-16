@extends('template.main_alta_modal')

@section('titulo','Nueva Venta')
   
@section('contenido')

<div id="msgErrAfip"  class="alert alert-danger" align="left" ></div>
<!-- Panel General del Nuevo Comprobante -->
<div class="panel panel-info">   

    <div class="panel-heading">
        <b> NUEVA VENTA </b>
    </div>   <!-- /Fin Header Panel  -->

    <div class="panel-body">          
    <form id="formularioPrincipal" autocomplete="off" role="form" onkeypress="return event.keyCode != 13;">
        <div class="row">
            <div class="col-lg-2 col-md-2">
                <label>Sucursal</label>
                <br>
                <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == "" ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Vendedor</label>
                <br>
                <select class="form-control" name="id_vendedor" id="id_vendedor" autofocus>
                       <?PHP $vendedor ='';     ?> 
                       @include('common.combo_vendedor')
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Tipo Comp.</label>
                <br>
                <select class="form-control" name="id_tipo_cbte" id="id_tipo_cbte">
                   @include('common.combo_comprobante_fiscal')
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Fecha</label>
                <br>
                <input class="form-control" type="date" name="fecha" id="fecha" value="<?= date("Y-m-d"); ?>" required/ readonly>
            </div>
            <div class="col-lg-4 col-md-4">
                <label>Cliente</label>
                <br>
                <div class="input-group">
                    <input type="hidden" id="id_clienteweb" name="id_clienteweb" value="" >
                    <input class="form-control" type="text" id="id_cliente" name="id_cliente" value="" autocomplete="off" placeholder="DNI/ApelidoNombre" >
                    <span class="input-group-btn">
                        <button type="button" class="btn" id="modif-cliente-btn"
                        title="Consultar/Modificar" onclick="BtnModificaCliente()">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn" title="Nuevo"
                         id="form-search-btn" onclick="BtnNuevoCliente()">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>     
                    </span>
                </div>
            </div>    
        </div>

        <div class="row" style="padding: 1px;">
            <div class="col-lg-8 col-md-8">
            </div>
            <div class="col-lg-4 col-md-4">
                <!-- Cuadro con datos del Cliente, se actualiza cuando ingresa clientes -->
                <div class="alert-info" id="datos_cliente" hidden="true">
                    <b><span  id="nombre_cliente">xxxxxxxx </span><br></b>
                    <span  id="dni_cliente" >nnnnnnnnnn</span><br> 
                    <span  id="telefono_cliente"> nnnnnn  </span>
                </div> 
            </div>
        </div>

        <input type="hidden" name="json_items" id="json_items" value="">
        <input type="hidden" name="json_pagos" id="json_pagos" value="">
                    
      <br>
      <!-- Panel Seleccion de Articulos -->
      <div class="panel panel-warning">   
        <div class="panel-heading">
            <b> Selección de Articulos </b> 
        </div>   <!-- /Fin Header Panel  -->

        <div class="panel-body">          
            <!--<div class="row" style="background-color: #efe0e0"> -->
            <div class="row">
              <div class="col-lg-2 col-md-2">
                <select class="form-control"  id="id_familia">
                    <?php $FLIA_ID = "VAR"; ?>
                    @include('common.combo_familia')
                </select>
              </div> 
              <div class="col-lg-2 col-md-2">
              <div class="input-group">
                <input class="form-control" type="text" name="" id="id_producto" value="" placeholder="Buscar Artículo">
             <!-- no me funciona    
               <span class="input-group-addon"><i class="glyphicon glyphicon-search" onclick="busca_articulo()" ></i></span>
              --> 
              </div>
              </div><div class="col-lg-3 col-md-3">
                <input class="form-control" type="text" name="" id="descrip_producto" value="" disabled>
              </div>

              <div class="col-lg-1 col-md-1">
                    <input class="form-control text-right" type="text" name="" id="cantidad" value="" placeholder="Cantidad">
              </div>
              <div class="col-lg-2 col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">$</span>
                        <input class="form-control text-right" type="text" name="" id="precio_unitario" value="" placeholder="Precio Unit.">
                    </div>
              </div>
              <div class="col-lg-1 col-md-1">
                    <div class="input-group">
                        <span class="input-group-addon">%</span>
                        <input class="form-control text-right" type="text" name="" id="bonif_unitario" value="0" placeholder="Bonif">
                    </div>
              </div>
              <div class="col-lg-1 col-md-1">
                    <button type="button"  class="btn btn-primary"
                        title="Agregar" onclick="ingresar_articulo()">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                    </button>
              </div>
            </div>
        </div>   <!-- /Fin  Panel Body  -->
      </div>   <!-- /Fin  Panel Seleccion de Articulos -->

      <div class="row"> <!-- Tabla de Detalle Productos que se van cargando -->
            <div class="col-lg-12 col-md-12">
                <h4>    Su Venta  </h4>            
                <table id="tbl-items" class="table table-bordered table-hover">
                    <thead>
                        <th style="text-align: center;">Cantidad</th>
                        <th>Producto</th>
                        <th style="text-align: center;">Precio Unit.</th>
                        <th style="text-align: center;">Bonif</th>
                        <th style="text-align: center;">SubTotal</th>
                        <th style="text-align: center;" data-halign="center"><span class="fa fa-wrench"></span></th>
                    </thead>
                    <tbody>
                        <!-- Se va completando por Scrip cada vez que agrega un producto-->
                    </tbody>
                </table>
            </div>
      </div> <!-- /Fin  Panel Su Venta -->

      <hr>
      <h4>Observaciones</h4>
      <div class="form-group row">
          <div class="col-xs-5">
           <textarea id="observaciones" name="observaciones" rows="5" cols="70" placeholder="Escribe tus observaciones aquí..."></textarea><br>
          </div> <!-- /Fin Col  -->

          <div class="col-xs-7 text-right">
              <div class="input-group" style="display: inline-flex; align-items: center;">
                    <strong style="font-size: 20px; margin-right: 30px;">Total: </strong>
                    <span class="input-group-addon" style="font-size: 20px; width: 50px;">$</span>
                    <input type="text" class="form-control text-right" style="font-size: 20px; width: 160px;" name="total" id="total" value="0" readonly>
              </div>
          </div>
      </div>
    </div>   <!-- /Fin Header Panel  -->

    <div class="panel-footer"> 
      <div class="form-group row">   
       <div class="col-xs-3" >     
        <button type="button" id="PresuButton" class="btn btn-warning" onClick="FinalizaPresu()">PRESUPUESTAR</button>
       </div>                          
       <div class="col-xs-9" >     
        <button type="button" id="VentaButton" class="btn btn-success pull-right" onClick="FinalizaVenta()">COBRAR</button>
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

  document.addEventListener("DOMContentLoaded", () => {
    actualizarTotales();
  });
 
  // Función para actualizar totales
  function actualizarTotales() {
   
   // const enableButton = document.getElementById("VentaButton");
    
    $('#total').val( formatearNumeroConSeparadorDeMiles( venta.total, 2 ));
    totalPagar = venta.total

    if (totalPagar > 0) {
        VentaButton.disabled = false; //Activo el boton Vender
        PresuButton.disabled = false; //Activo el boton Vender
    }else{
        // No cargo nada , esta todo en cero
        VentaButton.disabled = true; //Desactivo el boton Vender
        PresuButton.disabled = true; //Desactivo el boton Vender
    }
 
  }









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

                    // Incrementar para el siguiente ingreso.
                    this.items_count++;

                    return (this.items_count - 1);
            },

            deleteItem: function(key) {
                //    this.total -=  ( this.items[key].precio_unitario * this.items[key].cantidad);
                    this.total -=  ( this.items[key].precio_total);
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

        // temporal por ahora todo en pesos
        var total =  Number( numberFormatBd( $('#total').val() ) );

        var key_item = venta.addPago("P" , total , 1, 1);
        // key_item = venta.addPago("R", 10 , 1, 13);
    }   

    function ingresar_articulo() {

        var id_producto = $('#id_producto').val();
        var id_familia = $('#id_familia').val();
        var descrip_producto = $('#descrip_producto').val();
        var id_iva = 5; // 21%
        var texto_iva = $("#id_iva option[value=" + id_iva + "]").text();
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
         Finalizar("Vta" )
    } // finalizaVenta 

    function FinalizaPresu() {
         Finalizar("Presu" )
    } // finalizaPresu 

    function Finalizar( operacion  ) {

        // operacion  =  Vta / Presu
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

        numeroOrig = ""
        punto_facturaOriginal = ""
        if ( operacion == "Vta") {
            operacionDetalle = "esta Venta";


            // Si son Notas de Credito pedir Datos Originales
            console.log(tipo_de_factura)
            if (tipo_de_factura != "Z") tipo_de_factura = $('#id_tipo_cbte').val()

            if(tipo_de_factura == 3  || tipo_de_factura == 8) {
                punto_facturaOriginal = prompt('Ingrese Punto de Venta Original:','');
                numeroOrig = prompt('Ingrese Nro.Comprobante Original:','');
            }

        }else{
          operacionDetalle = "este PRESUPUESTO";
        }



        Swal.fire({
                  title: 'Finalizar ' + operacionDetalle + ' ? <br>  Por un Total $' + $('#total').val()  ,
                  text: "Esta Seguro de Cerrar  " + operacionDetalle,
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonText: 'Cancelar',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Si, Finalizar!'
            }).then((result) => {
                  if (result.value) {
 
                    // temporal aqui por ahora todo en pesos
                    ingresar_pagos();
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
                   observaciones: $('#observaciones').val(),
                   operacion: operacion,
                   id_cliente: $('#id_cliente').val(),
                   json_items:         json_items,
                   json_pagos:         json_pagos
                };


            $.ajax({                
                dataType: "json",type:  'get', data: formdata,
                url:  'store',            
                success: function(data){
                    console.log(data)
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
    
    } // finaliza Aceptar
        
    function inicializar() {

        var VALUES_IVA = {
          5 : 21, 
          4 : 10.5,
          1 : 0
        };

        tipo_de_factura = ""
        venta.values_iva = VALUES_IVA  ;

        $('#id_cliente').val("")
        $('#observaciones').val("")
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