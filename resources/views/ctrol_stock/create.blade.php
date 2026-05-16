@extends('template.main')

@section('titulo','Control de Stock - Sectores')
   
@section('contenido')

<form   autocomplete="off" class="form-horizontal"   role="form"
           onkeypress="return event.keyCode != 13;">
<!-- Panel General -->
<div class="panel panel-info">         
    <div class="panel-heading">
      <div class="row" >
        <div class="col-lg-7 col-md-7" id="titulo_pagina">
          <b> Lote: {{ $lote->Lot_IdProv}}  Sector Nro: {{ $lote->Lot_Numlot }}  Sucursal: {{ $lote->Lot_Sucursal }} Rubro: {{ $lote->Lot_Familia }} </b>
        </div> 
        <div id="lblestado" class="col-lg-5 col-md-5 text-right" style="color: red;">  </div>
      </div> 
    </div>   <!-- /Fin Header Panel  -->

    <div class="panel-body">         
      <input type="hidden" name="idcompra" id="idcompra" value="<?= $lote->Lot_Numlot; ?>">

        <div class="row" >
            <div class="col-lg-4 col-md-4">
                <label>Sector</label>
                <br>
                <input class="form-control" type="text" id="observacion" name="observacion" value="<?= $lote->Lot_Observ; ?>" >
            </div>
        </div>
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <button type="button" class=" btn btn-success pull-right"
                id="btnadd" onclick="showAltaItem()">
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
                <button type="button" class="btn btn-default pull-right" onClick="document.location = 'index_partes?id_lote={{ $lote->Lot_IdProv}}'">Regresar</button>
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
                <input class="form-control" type="text" name="id_familia" id="id_familia" value="{{ $lote->Lot_Familia}}" disabled>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Articulo</label>
                <br>
                <input class="form-control" type="text" name="" id="id_producto" placeholder="Buscar Articulo">
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
                <label>Precio Venta</label>
                <br>
                <input class="form-control text-right" type="number"  id="add_precio" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Prec.Mínimo</label>
                <br>
                <input class="form-control text-right" type="number" name="" id="add_precio_min" value="">
            </div>
          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgVentanaAddItem1" class="alert alert-warning" align="left" > </div>
            <div id="msgVentanaAddItem" class="alert alert-success" align="left" > </div>
            <div id="msgErrorVentanaAddItem" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  id="btnAddArticulo" class="btn btn-success" onclick="ingresar_articulo()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de AltaItem -->

@endsection <!-- Fin Contenido -->

@section('scrip')

<!-- http://js-grid.com/     https://github.com/tabalinas/jsgrid -->

<link type="text/css" rel="stylesheet" href="{{ asset('plugins/jsgrid/jsgrid.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{ asset('plugins/jsgrid/jsgrid-theme.min.css')}}" />

<script type="text/javascript" src="{{ asset('plugins/jsgrid/jsgrid.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jsgrid/i18n/jsgrid-es.js')}}"></script>

<script>

    var col_opciones_visible

    $("#userModal_AltaItem").draggable({
      handle: ".modal-header"
    });

    $(document).ready(function() {

        estadolote = '{{$lote->Lot_Estado}}';
        if (estadolote == 'C'){ //EN CARGA
            $("#lblestado").html( '<b>PENDIENTE DE PROCESAR</b>')  
            col_opciones_visible = true    
        }else{  // Finalizad
            $("#lblestado").html( '<b>FINALIZADO</b>')   
            $("#btnadd").hide()
        }

        $('#observacion').change( function() {
            ActualizaDatosLote()           
        });
        
        // Campos Pantalla Modal Add Articulo
        // ----------------------------------
        $('#cantidad').keyup(function(e) {
            if (e.which == 13) {
                $('#id_producto').focus();
            }
        });

        // Busqueda Automatica de Productos
        $('#id_producto').typeahead({
            items: 15,
            minLength: 4,
            highlight: true,
            source: function(query, process) {
              var familia = $('#id_familia').val();
              $.ajax({
                  global: false,
                  dataType: "json",
                  data: {},
                  url:   '../productos/buscaproducto?terms='+query+'&familia='+familia+'&estado=T',
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
                // El rendimiento muestro el que tenia
                $('#add_precio').val( redondeaPesos(item.precio) );
                $('#add_precio_min').val( redondeaPesos(item.precio2) );
                $('#add_precio_lista').focus();
            }
        }); //Fin Busqueda Producto

        $('#id_producto').keyup(function(e) {
            if (e.which == 13) {
                var id_producto = $('#id_producto').val();
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
                $('#id_producto').focus(); //Le saco el foco para que no repita
                ingresar_articulo();
            }
        });

        function ActualizaDatosLote()  {
            
            $.ajax({
                global: false,
                dataType: "json",
                data: { idcompra: $("#idcompra").val(),
                    observ: $("#observacion").val()
                },
                url:   'ActualizaDatosLote',
                type:  'get'
                }).done(function(data) {
//                        $("#idcompra").val(data.idcompra) // En el 1er Item lo genera
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
                    url:   'CargaItems',
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
                    url:   'DeleteItem',
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
                        , precio: item.Prod_Precio
                        , precio_min: item.Prod_Precio2
                    },
                    url:   'UpdateItem',
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
//                var total = { Name: "Suma", "Prod_Costo": 0,"MLot_Precio": 0, "MLot_Familia":"<b>TOTALES</b>","Prod_Descripcion":"" ,  IsTotal: true };
                var total = { Name: "Suma",  "MLot_Familia":"<b>TOTALES</b>","Prod_Descripcion":"" ,  IsTotal: true };
               
                total.Prod_Costo = 0;
                cantidad = 0
                items.forEach(function (item) {
                    if (item.MLot_Cantidad != null) {
                        cantidad +=  parseInt(item.MLot_Cantidad)
                      //  console.log (total.Prod_Costo , item.Prod_Costo, cantidad)
                    }                
                });

                total.MLot_IdProd =  '<b>Cant.Articulos '  + cantidad + '</b>'
              //  total.Prod_Costo =  "<strong>"  +  total.Prod_Costo + "</strong>" 
                var $totalRow = $("<tr>").addClass("total-row");
                console.log ('onRefreshed  Total Costo:' , items.length , total.Prod_Costo,$("#idcompra").val())
                if (items.length > 0) {
                    args.grid._renderCells($totalRow, total);
                    args.grid._content.append($totalRow);
                }
            },
        fields: [
            { type: "control", visible: col_opciones_visible ,
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
       $("#add_rendimiento").val( $("#rendimiento").val() )
       $("#msgVentanaAddItem1").hide();
       $("#msgVentanaAddItem").hide();
       $("#msgErrorVentanaAddItem").hide();
       $('#id_producto').val('');
       $('#descrip_producto').val('');
       $('#add_categoria').val('');
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

    function searchByFormdata() {
        // Se ejecuta despues de boton modificar proveedor , o de buscar 1 cli
        // Actualiza el cuadro  con los datos del proveedor
        $('#id_proveedor').val( $("#id").val()); // Campos de pantalla externa
        $('#proveedor').val( $("#id").val() + "-" + $("#Prov_RazSocial").val() );
        $("#modif-proveedor-btn").show ();
        $("#datos_proveedor").hide();
    }

    function cargar_items()  {

        $idcompra = $("#idcompra").val();

        $.ajax({
            global: false,
            dataType: "json",
            data: { idcompra: $idcompra},
            url:   'CargaItems',
            type:  'get',
            success: function(data){
   //             $table.bootstrapTable('load', data.results);

            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin CargaItems()

    function ingresar_articulo(  )  {

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
                    , precio: $("#add_precio").val()
                    , precio_min: $("#add_precio_min").val()
                },
            url:   'AddItem',
            type:  'get',
            success: function(data){
                $('#add_ind_alta').val('N');

                $("#msgVentanaAddItem1").html( '<b>Úlimo Item ingresado: </b>' + $("#id_familia").val() + '-' + $("#id_producto").val()  + '  ' + $("#descrip_producto").val());
                $("#msgVentanaAddItem1").show();

                $("#jsTabla").jsGrid("render").done(function() {
                    console.log("rendering completed and data loaded");
                });
                $("#msgErrorVentanaAddItem").hide();
                $('#id_producto').val('');
                $('#descrip_producto').val('');
                $('#add_categoria').val('');
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

</script> 

@endsection <!-- Fin scrip -->
