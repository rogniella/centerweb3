@extends('template.main_alta_modal')

@section('titulo', 'Control Stock')

@section('contenido')
    
<form class="form-inline" role="form" >
    <div class="row">
     <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <input type="hidden" name="numlot" id="numlot" value="{{ $lote->Lot_Numlot}}">

        <div class="panel panel-info">         
            <div class="panel-heading">
            <div class="row" >
              <div class="col-lg-7 col-md-7" id="titulo_pagina">
               <h3 class="panel-title">Control de Stock Lote: {{ $lote->Lot_Numlot}} </h3>
              </div> 
              <div id="lblestado" class="col-lg-5 col-md-5 text-right" style="color: red;">  </div>
            </div>
            </div>

           <div class="panel-body">         
             <div class="row" >
                <div class="col-lg-2 col-md-2">
                    <label>Fecha</label>
                    <br>
                    <input class="form-control text-center" type="date" id="fecha" name="fecha" value="<?= $lote->Lot_FecMov; ?>" required/>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label>Sucursal</label>
                    <br>
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $lote->Lot_Sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                </div>               
                <div class="col-lg-3 col-md-3">
                    <label>Rubro</label>
                    <br>
                    <select name="familia" id="familia" class="form-control" required>
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == $lote->Lot_Familia ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                </div>               
                <div class="col-lg-4 col-md-4">
                    <label>Observaciones</label>
                    <br>
                    <input class="form-control" type="text" id="observacion" name="observacion" value="{{$lote->Lot_Observ}}" >
                </div>
             </div>
            </div>
        </div> <!-- Fin Panel Info -->

        <div id="toolbar">
            <label>&nbsp</label>
            <button type="button" class=" btn btn-success"
                id="btnadd" onclick="showIng_Compra2( 0)">
                <i class="glyphicon glyphicon-plus"></i> Generar Nuevo Sector 
            </button>
        </div>
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <table id="mitabla"
           data-toggle="table"
           data-show-print="true"
           data-cache = "false"
           data-page-list=""
           class="table table-striped"
           data-toolbar="#toolbar"
           data-toolbar-align="right"
           >
          <thead>
          <tr>
            <th data-field="Lot_Numlot" data-align="center" data-formatter="opcionesFormatter"></th>
            <th  data-field="Lot_Sucursal" data-align="center"  data-sortable="true">Sucursal</th>
            <th  data-field="Lot_Familia" data-align="left"  data-sortable="true">Familia</th>
            <th data-field="Lot_Observ" > Sector</th>    
            <th  data-field="Lot_Cantidad" data-align="right" data-sortable="true">Cantidad</th>
          </tr>
          </thead>
       </table>
    </div> <!-- fin Panel Tabla -->
   </div> <!-- fin de col -->
  </div> <!-- fin de row -->
  <div class="row">
        <div class="col-lg-12 col-md-12">
            <button type="button" class="btn btn-default pull-right" onClick="document.location = 'index'">Regresar</button>
        </div>
  </div>

  <!-- Opciones Solo si es Administrador-->
  @if(Auth::user()->perfil_id == 'ADM')
  <div class="row">
      <div class="col-lg-3 col-md-3">
          <label>Filtrar</label>
          <input class="form-control" type="text" name="filtro" id="filtro" value="{{$lote->Lot_Filtro}}">
      </div> <!-- fin de col -->
      <div class="col-lg-2 col-md-2">
          <select name="actualiza" id="actualiza" class="form-control">
                <option value="NO" >Simulasión</option>
                <option value="SI" >Actualizar Datos</option>
          </select>
      </div> <!-- fin de col -->
      <div class="col-lg-2 col-md-2">
         <button type="button" class=" btn btn-success"
            id="form-search-btn" onclick="calcular_ajuste( '' )">
            Calcular Ajuste 
        </button>
      </div> <!-- fin de col -->
  </div> <!-- fin de row -->

  <div class="row">
  <br>
  <div class="col-sm-6">
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <table id="mitabla_total"
           data-toggle="table"
           data-cache = "false"
           data-page-size="50"
           data-page-list=""
           class="table table-striped"
           >
          <thead>
          <tr>
            <th  data-field="descripcion" data-align="left"  data-sortable="true">T O T A L E S</th>
            <th  data-field="cantidad" data-align="right"  data-sortable="true">Cantidad</th>
          </tr>
          </thead>
       </table>
    </div> <!-- fin Panel Tabla -->
   </div> <!-- fin de col -->
 

  <div class="row">
  <div class="col-sm-12">
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <table id="mitabla_ajuste"
           data-toggle="table"
           data-search="true"
           data-show-print="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""
           class="table table-striped"
           >
          <thead>
          <tr>
            <th  data-field="Prod_idweb" data-halign="center"  data-align="center" data-formatter="opcionesFormatterProd">Opciones</th>
            <th  data-field="Prod_Id" data-align="left"  data-sortable="true">Producto</th>
            <th  data-field="Prod_Descripcion" data-align="left"  data-sortable="true">Descripción</th>
            <th  data-field="Prod_Precio" data-align="right" data-sortable="true">Precio</th>
            <th  data-field="stock_bd" data-align="right" data-sortable="true">Stock Sistema</th>
            <th  data-field="stock_ingresado" data-align="right" data-sortable="true">Stock Ingresado</th>
            <th  data-field="stock_bd_otra" data-align="right" data-sortable="true">Stock Otra Suc.</th>
            <th  data-field="ajuste" data-align="right" data-sortable="true">Ajuste</th>
            <th data-field="obs" > Observaciones</th>    
          </tr>
          </thead>
       </table>
    </div> <!-- fin Panel Tabla -->
   </div> <!-- fin de col -->
 
  </div> <!-- fin de row -->

 
  </div> <!-- fin de row -->
  @endif <!-- Fin de ADM -->

</form> 


<!-- Formulario de Alta/ Modificacion de Productos -->
<?php include( base_path() . "/resources/views/productos/campos.php");?>
@include('productos.alta_modif')


@endsection()

@section('scrip')

<script>

$(document).ready(function() {

        estadolote = '{{$lote->Lot_Estado}}';
        if (estadolote == 'C'){ //EN CARGA
            $("#lblestado").html( '<b>PENDIENTE DE PROCESAR</b>')  
            col_opciones_visible = true    
        }else{  // Finalizad
            $("#lblestado").html( '<b>ERROR ESTA OPCION NO ES PARA EL ESTADO DE ESTE LOTE</b>')   
            $("#btnadd").hide()
        }

        consultar(); // Carga la tabla superiror con los Sectores


        $('#fecha').change( function() {
            ActualizaDatosLote()           
        });

        $('#sucursal').change( function() {
            ActualizaDatosLote()           
        });

        $('#observacion').change( function() {
            ActualizaDatosLote()           
        });

        $('#filtro').change( function() {
            ActualizaDatosLote()           
        });

}); // Fin de ready


function opcionesFormatterProd(value,columnas) {

      var Id = value;

     // console.log  ( Id)
      var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar Producto" onclick="showEditModalProducto(true,\''+ Id +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';



      return opciones;
}



function ActualizaDatosLote()  {
            //console.log ( 'cambio datos lote')  

            sucursal = document.getElementById('sucursal').value;            
            familia= document.getElementById('familia').value;            
            $.ajax({
                global: false,
                dataType: "json",
                data: { idcompra: $("#numlot").val(),
                    sucursal: sucursal,
                    familia: familia,
                    fecmov: $("#fecha").val(),
                    filtro: $("#filtro").val(),
                    observ: $("#observacion").val()
                },
                url:   'ActualizaDatosLote',
                type:  'get'
                }).done(function(data) {
                        $("#titulo_pagina").html('Remito Nro: ' + data.idcompra)
            });
        } 

    /**
     * Da el formato requerido al valor presente en la columna de opciones.
     * @param value     valor que contiene la columna.
     */
    function opcionesFormatter(value) {
        var Id = value;

        botones = '<button type="button" class="btn btn-primary btn-xs"'+
                    'title="Consultar " onclick="showIng_Compra2('+ Id +')">'+
                    '<i class="fa fa-info-circle"></i>'+
                  '</button>';
        return botones;        
    }

    function showIng_Compra2( id_lote) {
          
       // Paso a la pantalla Sector - Stock
       $numlot = $("#numlot").val();
       document.location = 'create?id_lote='+id_lote + '&num_lote='+ $numlot ;

    }
   



    function consultar()  {
   
        // Tomo los datos de entrada
        var $table = $('#mitabla'); 
        
        $numlot = $("#numlot").val();
        $.ajax({
            dataType: "json",
            data: { tipo_consulta: '' , tipo_lote: 'S',numlot: $numlot },
            url:   'buscar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()


    function calcular_ajuste( estado)  {

        // Tomo los datos de entrada
        var $table = $('#mitabla_ajuste'); 
        var $table_total = $('#mitabla_total'); 
        
        $numlot = $("#numlot").val();
        if ( estado == '' ) {
            estado = $("#actualiza").val()
        } 

        $.ajax({
            dataType: "json",
            data: { actualiza:  estado ,numlot: $numlot ,filtro: $("#filtro").val() },
            url:   'calcular_ajuste',
            type:  'get',
            success: function(data){
                $table_total.bootstrapTable('load', data.totales);
                $table.bootstrapTable('load', data.results);
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // calcular_ajuste()

</script>

@endsection()