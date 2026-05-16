@extends('template.main')

@section('titulo','Ingreso de Remitos InterSucursales')
   
@section('contenido')

<form   autocomplete="off" class="form-horizontal"   role="form"
        onkeypress="return event.keyCode != 13;">
<!-- Panel General -->
<div class="panel panel-info">         
    <div class="panel-heading">
      <div class="row" >
        <div class="col-lg-2 col-md-2" id="titulo_pagina"><b> REMITO NRO:{{$lote->Lot_Numlot}} </b> </div>
        <div id="lblestado" class="col-lg-10 col-md-10 text-right" style="color: red;">  </div>
      </div>   
    </div>   <!-- /Fin Header Panel  -->

    <div class="panel-body">         
      <input type="hidden" name="idcompra" id="idcompra" value="<?= $lote->Lot_Numlot; ?>">
      <input type="hidden" name="sucursal" id="sucursal" value="0">

        <div class="row" >
            <div class="col-lg-2 col-md-2">
                <label>Fecha</label>
                <br>
                <input class="form-control text-center" type="date" id="fecha" name="fecha" value="<?= $lote->Lot_FecMov; ?>" required/>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Sucursal Origen:</label>
                <br>
                <select name="sucori" id="sucori" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $lote->Lot_Sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
            </div>    
            <div class="col-lg-3 col-md-3">
                <label>Sucursal Destino:</label>
                <br>
                <select name="sucdes" id="sucdes" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $lote->Lot_IdProv ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
            </div>    

            <div class="col-lg-4 col-md-4">
                <label>Observaciones</label>
                <br>
                <input class="form-control" type="text" id="observacion" name="observacion" value="<?= $lote->Lot_Observ; ?>" >
            </div>
        </div>

        <div class="row">
          <div class="col-lg-12 col-md-12">
            <br>
            @if( env('MODULO_OPTICA') == "S" ) 
            <button type="button" class=" btn btn-default "
                id="btncristal" onclick="showAltaCristal()">
                <i class="glyphicon glyphicon-plus"></i> Ingresar Cristales
            </button>          
            @endif  <!-- OPTICA -->  
            <button id="btnadd" type="button" class=" btn btn-success pull-right"
                 onclick="showAltaItem()">
                <i class="glyphicon glyphicon-plus"></i> Ingresar Articulo
            </button>
          </div>
        </div>
        <br>
        <div class="row"> <!-- Tabla de Detalle que se van cargando -->
            <div class="col-lg-12 col-md-12">
                 <div id="jsTabla"></div>
            </div>
        </div>
        <br>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <button type="button" class="btn btn-default pull-right" onClick="document.location = 'lista_remitos'">Regresar</button>

                <button id="btnproceso" type="button" class="btn btn-success pull-right" onClick="FinalizaCompra()">Finaliza Remito</button>
            </div>
        </div>
        
    </div> <!-- /Fin  Body  -->

</div> <!-- /Fin Panel General-->
</form>


<!-- Formulario AltaItem -->
<div id="userModal_AltaItem" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Agregar Articulo </h4>
        </div>
        <div class="modal-body">
            <input type="hidden" name="" id="add_ind_alta" value="">
          <div class="row">
            <div class="col-lg-2 col-md-2">
                <label>Cantidad</label>
                <br>
                <input class="form-control text-right" type="number" name="" id="cantidad" value="1" >
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Familia</label>
                <br>
                <select class="form-control" name="id_familia" id="id_familia">
                    <?php $FLIA_ID = "REC"; ?>
                    @include('common.combo_familia')
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Articulo</label>
                <br>
                <div class="input-group"> 
                <input class="form-control" type="text" name="" id="id_producto" placeholder="Buscar Articulo">
               </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Descripción </label>
                <br>
                <input class="form-control" type="text" name="" id="descrip_producto" value="" disabled>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Categoria</label>
                <br>
                <input class="form-control" type="text" name="" id="add_categoria" value="" disabled>
            </div>
          </div> <!-- /Fin Row 1 Seleccion de Articulo -->
          <br>
          <div class="row">
            <div class="col-lg-2 col-md-2">
                <label>Costo Suc</label>
                <br>
                <input class="form-control text-right" type="number" id="add_precio_lista" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Costo Real</label>
                <br>
                <input class="form-control text-right" type="number"  id="add_costo" value="" readOnly>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Precio Venta</label>
                <br>
                <input class="form-control text-right" type="number"  id="add_precio" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Prec.Mínimo</label>
                <br>
                <input class="form-control text-right" type="number" name="" id="add_precio_min" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Stock Suc 1</label>
                <br>
                <input class="form-control text-right" type="number" name="" id="add_stock01" value="" disabled>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Stock Suc 2</label>
                <br>
                <input class="form-control text-right" type="number" name="" id="add_stock02" value="" disabled>
            </div>
          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgVentanaAddItem1" class="alert alert-warning" align="left" > </div>
            <div id="msgVentanaAddItem" class="alert alert-success" align="left" > </div>
            <div id="msgErrorVentanaAddItem" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  id="btnAddArticulo" class="btn btn-success" onclick="ingresar_articulo()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de AltaItem -->

<!-- Formulario AltaCristal -->
@include('compras.alta_cristal')

@endsection <!-- Fin Contenido -->

@section('scrip')

<link type="text/css" rel="stylesheet" href="{{ asset('plugins/jsgrid/jsgrid.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{ asset('plugins/jsgrid/jsgrid-theme.min.css')}}" />

<script type="text/javascript" src="{{ asset('plugins/jsgrid/jsgrid.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jsgrid/i18n/jsgrid-es.js')}}"></script>



<script>

  var estadolote = '' 
  var col_opciones_visible

  $("#userModal_AltaItem").draggable({
      handle: ".modal-header"
  });

  $(document).ready(function() {

        estadolote = '{{$lote->Lot_Estado}}';
        if (estadolote == 'C'){ //EN CARGA
            $("#lblestado").html( '<b>PENDIENTE DE PROCESAR</b>')  
            col_opciones_visible = true     
        }else if (estadolote == 'E') {  //ENVIADO
            $("#lblestado").html( '<b>FALTA CONFIRMACIÓN</b>')
            col_opciones_visible = false    
            $("#btnproceso").html( 'Confirmar')     
            $("#btnadd").hide()
            $("#btncristal").hide()
            $("#fecha").prop('disabled', true )    
            $("#sucori").prop('disabled', true )    
            $("#sucdes").prop('disabled', true )    
        }else{  // Finalizar
            $("#lblestado").html( '<b>FINALIZADO</b>')   
            col_opciones_visible = false    
            $("#btnadd").hide()     
            $("#btnproceso").hide()
            $("#btncristal").hide()
            $("#fecha").prop('disabled', true );    
            $("#sucori").prop('disabled', true );    
            $("#sucdes").prop('disabled', true);    
        }

        $('#fecha').change( function() {
            ActualizaDatosLote()           
        });

        $('#sucori').change( function() {
            ActualizaDatosLote()           
        });

        $('#sucdes').change( function() {
            ActualizaDatosLote()           
        });

        $('#observacion').change( function() {
            ActualizaDatosLote()           
        });
        
        // Campos Pantalla Modal Add Articulo
        // ----------------------------------
        $('#cantidad').keyup(function(e) {
            if (e.which == 13) {
                $('#id_familia').focus();
            }
        });

        // er este no puedo pasar con enter    
        //$('#id_familia').on('change', function(e) {
        //        $('#id_producto').focus();
        //});

        // Busqueda Automatica de Productos
        $('#id_producto').typeahead({
            items: 15,
            minLength: 2,
            highlight: true,
            source: function(query, process) {
              var familia = $('#id_familia').val();
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
            },
            // Al seleccionar.
            afterSelect: function(item) {
                $('#add_ind_alta').val('N');
                $('#id_producto').val(item.id);
                $("#descrip_producto").attr('disabled',true);
                $("#add_categoria").attr('disabled',true);
                $('#descrip_producto').val(item.descripcion);
                $('#add_categoria').val(item.categoria);
                $('#add_stock01').val(item.stock01);
                $('#add_stock02').val(item.stock02);
                $('#add_costo').val( redondeaPesos(item.costo) ) ;

                $('#add_precio_lista').val( redondeaPesos(item.costo) ) ; // por defecto el costo

                $('#add_precio').val( redondeaPesos(item.precio) );
                $('#add_precio_min').val( redondeaPesos(item.precio2) );
                $('#add_precio_lista').focus();
            }
        }); //Fin Busqueda Producto

        $('#id_producto').keyup(function(e) {
            if (e.which == 13) {
                var id_producto = $('#id_producto').val();
                if (id_producto == '') {
                  $('#msgErrorVentanaAddItem').html("Debe ingresar el Código del Producto");    
                  $("#msgErrorVentanaAddItem").show();
                  $('#id_producto').focus();
                };
            }
        });

        $('#descrip_producto').keyup(function(e) {
            if (e.which == 13) {
                $('#add_categoria').focus();
            }
        });

        $('#add_categoria').keyup(function(e) {
            if (e.which == 13) {
                $('#add_precio_lista').focus();
            }
        });

        $('#add_precio_lista').keyup(function(e) {           
            if (e.which == 13) {
                $('#add_precio').focus();
            }
        });


        $('#add_precio').keyup(function(e) {
            if (e.which == 13) {
                $('#add_precio_min').focus();
            }
        });

        // El ultimo campo automatico el bton aceptar
        $('#add_precio_min').keyup(function(e) {
            if (e.which == 13) {
                ingresar_articulo();
            }
        });


        function ActualizaDatosLote()  {
            //console.log ( 'cambio datos lote')  

            sucori = document.getElementById('sucori').value;            
            sucdes = document.getElementById('sucdes').value;            
            $.ajax({
                global: false,
                dataType: "json",
                data: { idcompra: $("#idcompra").val(),
                    idprov: sucdes,
                    sucursal: sucori,
                    fecmov: $("#fecha").val(),
                    observ: $("#observacion").val(),
                    rendimiento: 0,
                    factor: 0
                },
                url:   '../compras/ActualizaDatosLote',
                type:  'get'
                }).done(function(data) {
                        $("#titulo_pagina").html('Remito Nro: ' + data.idcompra)
            });
        } 

    });

  
  $(function () {

    // Ayuda de jsGrid  http://js-grid.com/
    jsGrid.locale("es"); // Idioma Español

    $("#jsTabla").jsGrid({
        height: "auto",
        width: "100%",
        editing: col_opciones_visible,    // Permitir editar dependiendo del estado del lote
       // inserting: true,  // Para Insertar
        sorting: true,
        paging: true,
        pageSize: 100,
        confirmDeleting: true,
        deleteConfirm: "Confirma el Borrado del Artículo de ésta Compra?",
        autoload: true,
        controller: {
            loadData: function() {
                var d = $.Deferred();
                $idcompra = $("#idcompra").val(); 
                console.log('loadData');    
                $.ajax({
                    global: false,
                    dataType: "json",
                    data: { idcompra: $idcompra},
                    url:   '../compras/CargaItems',
                    type:  'get'
                    }).done(function(response) {
                        d.resolve(response.results);
                });
                return d.promise();
            },
            deleteItem: function( item) {
                var d = $.Deferred();
                $idcompra = $("#idcompra").val();
                fila = item.MLot_Fila
                console.log('deleteItem' , fila,  item);    
                $.ajax({
                    global: false,
                    dataType: "json",
                    data: { idcompra: $idcompra, fila:fila },
                    url:   '../compras/DeleteItem',
                    type:  'get'
                    }).done(function(response) {
                      //  d.resolve(response.results);
                });
  //              return d.promise();
            },
            updateItem: function( item) {
                var d = $.Deferred();
                $idcompra = $("#idcompra").val();
                console.log('UpdateItem' ,  item);    
                $.ajax({
                    global: false,
                    dataType: "json",
                    data: { idcompra: $idcompra 
                        , fila: item.MLot_Fila
                        , familia: item.MLot_Familia
                        , idprod: item.MLot_IdProd
                        , descripcion: item.Prod_Descripcion
                        , categoria: item.Prod_Categoria
                        , cantidad: item.MLot_Cantidad
                        , costo: item.Prod_Costo
                        , precio_lista: item.MLot_Precio
                        , precio: item.Prod_Precio
                        , precio_min: item.Prod_Precio2
                    },
                    url:   '../compras/UpdateItem',
                    type:  'get'
                    }).done(function(response) {

                    $("#jsTabla").jsGrid("render").done(function() {
                        console.log("rendering completed and data loaded");
                    });
                      //  d.resolve(response.results);
                });
  //              return d.promise();
            }
        },

             onRefreshed: function (args) {
                // Calcula y visualiza los totales
                var items = args.grid.option("data");
                var total = { Name: "Suma", "Prod_Costo": 0,"MLot_Precio": 0, "MLot_Familia":"<b>TOTALES</b>","Prod_Descripcion":"" ,  IsTotal: true };
               
                total.Prod_Costo = 0;
                cantidad = 0
                items.forEach(function (item) {
                    if (item.Prod_Costo != null) {
                        cantidad +=  parseInt(item.MLot_Cantidad)
                        total.Prod_Costo +=  parseFloat(item.Prod_Costo) * parseInt(item.MLot_Cantidad)
                        total.MLot_Precio +=  parseFloat(item.MLot_Precio) * parseInt(item.MLot_Cantidad)
                      //  console.log (total.Prod_Costo , item.Prod_Costo, cantidad)
                    }                
                });

                total.Prod_Descripcion =  '<b>Cant.Articulos '  + cantidad + '</b>'
              //  total.Prod_Costo =  "<strong>"  +  total.Prod_Costo + "</strong>" 
                var $totalRow = $("<tr>").addClass("total-row");
                console.log ('onRefreshed  Total Costo:' , items.length , total.Prod_Costo,$("#idcompra").val())
                if (items.length > 0) {
                    args.grid._renderCells($totalRow, total);
                    args.grid._content.append($totalRow);
                }
                if($("#idcompra").val() != 0) {   
                  console.log ('Actualizao totales del lote')
                  $.ajax({
                   global: false,
                   dataType: "json",
                   data: { idcompra: $("#idcompra").val(),
                      cantidad: cantidad,
                      mtolista: total.MLot_Precio
                    },
                    url:   '../compras/ActualizaTotalesLote',
                    type:  'get'
                    }).done(function(data) {
                         $("#msgVentanaAddItem").html( '<b>Totales de la Compra </b>       Articulos:' + cantidad + '  Costo Total $ ' + total.MLot_Precio  + '');
                          $("#msgVentanaAddItem").show();
                    });
                } // end if    
            },
        fields: [
            { type: "control", width: 53 , visible: col_opciones_visible ,
                 itemTemplate: function (_, item) {
                    if (item.IsTotal)
                        return "";
                        return jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                }
            }, //Iconos de funciones
            { name: "MLot_Fila", type: "number" , visible: false},
            { title: "Cant", name: "MLot_Cantidad", type: "number",width: 57  },
            { title: "Familia", name: "MLot_Familia", type: "text", readOnly: true  ,width: 59 , align: "center"},
            { title: "Código", name: "MLot_IdProd", type: "text", width: 80 , validate: "required" , readOnly: true ,  align: "center"},
            { title: "Categoría", name: "Prod_Categoria", type: "text", width: 75 },
            { title: "Descripción", name: "Prod_Descripcion", type: "text", width: 130 , validate: "required" },
            { title: "Prec.Lista", name: "MLot_Precio", type: "number", itemTemplate: function(value) {return formatoPesos(value);} , width: 70 },
            { title: "Costo", name: "Prod_Costo", type: "number",  width: 72, itemTemplate: function(value)  {return formatoPesos(value);}  },
            { title: "Precio", name: "Prod_Precio", type: "number" , width: 70 , itemTemplate: function(value) {return formatoPesos(value);}  },
            { title: "Prec.Min", name: "Prod_Precio2", type: "number", width: 70, itemTemplate: function(value) {return formatoPesos(value);}  }
        ]
    });
  });


    function formatoPesos( value) {

        if (value != null) {
            var valor = parseFloat(value);

            return valor.toFixed(0); // nnnn.00

        } else {
            return ('');
        }

    }   

    function redondeaPesos( value) {

        if (value != null) {
            var valor = parseFloat(value);
            // nnn0
            return  (valor / 10).toFixed(0) * 10 ;
            // nn00
     //       return  (valor / 100).toFixed(0) * 100 ;

        } else {
            return ('');
        }
    }   

    function showAltaItem() {
       //$("#add_factor").val( $("#factor").val() )
       $("#msgVentanaAddItem1").hide();
       $("#msgVentanaAddItem").hide();
       $("#msgErrorVentanaAddItem").hide();
       $('#add_ind_alta').val('N');
       $("#userModal_AltaItem").modal("show")
            // Cuando termina de mostrarse, selecciono el 1re campo.
            .on("shown.bs.modal", function(e) {
              $("#cantidad").select(); 
        });                    
    }   

    function porcentajeFormatter(value, row) {
        var redondeo = parseFloat(value)
        return  redondeo.toFixed(2)   
    }




    function ingresar_articulo()  {

        // Tomo los datos de entrada

        if ($("#id_producto").val() == '') {
            $('#msgErrorVentanaAddItem').html("Debe ingresar el Código del Producto");            
            $("#msgErrorVentanaAddItem").show();
            $('#id_producto').focus();
            return
        }
        if ($("#descrip_producto").val() == '') {
            $('#msgErrorVentanaAddItem').html("Debe ingresar la Descripción");            
            $("#msgErrorVentanaAddItem").show();
            $('#descrip_producto').focus();
            return
        }

        $idcompra = $("#idcompra").val();
        $('#btnAddArticulo').prop('disabled', true);

        $.ajax({
            global: false,
            dataType: "json",
            data: { idcompra: $idcompra 
                    , ind_alta: $("#add_ind_alta").val()
                    , fila: 0
                    , familia: $("#id_familia").val()
                    , idprod: $("#id_producto").val()
                    , descripcion: $("#descrip_producto").val()
                    , categoria: $("#add_categoria").val()
                    , cantidad: $("#cantidad").val()
                    , costo: $("#add_costo").val()
                    , precio_lista: $("#add_precio_lista").val()
                    , precio: $("#add_precio").val()
                    , precio_min: $("#add_precio_min").val()
                },
            url:   '../compras/AddItem',
            type:  'get',
            success: function(data){
                 $('#add_ind_alta').val('N');
                $("#idcompra").val(data.idcompra) // En el 1er Item lo genera
                $("#titulo_pagina").html('Compra Nro: ' + data.idcompra)

                $("#msgVentanaAddItem1").html( '<b>Úlimo Item ingresado: </b>' + $("#id_familia").val() + '-' + $("#id_producto").val()  + '  ' + $("#descrip_producto").val());
                $("#msgVentanaAddItem1").show();

                $("#jsTabla").jsGrid("render").done(function() {
                    console.log("rendering completed and data loaded");
                });
                    $("#msgErrorVentanaAddItem").hide();
                    $('#id_producto').val('');
                    $('#descrip_producto').val('');
                    $('#add_categoria').val('');
                    $('#add_precio_lista').val('');
                    $('#add_costo').val('');
                    $('#add_precio').val('');
                    $('#add_precio_min').val('');
                    $('#id_producto').focus();
                  $('#btnAddArticulo').prop('disabled', false);
             },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
               $('#btnAddArticulo').prop('disabled', false);
             } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin CargaItems()


    function FinalizaCompra() {

        // Validaciones 
        sucori = document.getElementById('sucori').value;            
        sucdes = document.getElementById('sucdes').value;            
        if ( sucdes == sucori) {
            Swal.fire(
              'ERROR!!',
              'Sucursal Destino tiene que ser diferente a la de Origen.',
              'error')
            return  
        }    

        Swal.fire({
          title: 'Finalizar Remito ?',
          text: "Esta Seguro de Cerrar este Remito!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonText: 'Cancelar',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Si, Finalizar!'
        }).then((result) => {
          if (result.value) {
          $('#btnproceso').prop('disabled', true);
          $.ajax({
            global: false,
            dataType: "json",
            data: { idcompra: $("#idcompra").val() },
            url:   '../compras/Finalizar',
            type:  'get',
            success: function(data){
              if(data.msgError == '') {
               if(data.pdf != '') {
                  window.open( data.pdf, '', '_blanck' );
               }
               Swal.fire(       
                 'Finalizado!',
                 'Su Remito se Cerro con Éxito.',
                 'success'
               )
               document.location = 'lista_remitos'
              }else{                 
                msgerror( data.msgError);
              } //if data.err
              $('#btnproceso').prop('disabled', false);
            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
                $('#btnproceso').prop('disabled', false);

            } // Fin si hay error
          }); // Fin llamado Ajax

          }
        })
    }

</script> 

@include('compras.alta_cristal_js')

@endsection <!-- Fin scrip -->
