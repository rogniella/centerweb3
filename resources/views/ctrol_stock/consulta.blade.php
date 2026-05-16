@extends('template.main')

@section('titulo', 'Control Stock - Consulta')

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
               <h3 class="panel-title">Consulta Control de Stock Lote: {{ $lote->Lot_Numlot}}    Fecha Proceso: {{ $lote->Lot_FecMov}} </h3>
              </div> 
              <div id="lblestado" class="col-lg-5 col-md-5 text-right" style="color: red;">  </div>
            </div>
            </div>

           <div class="panel-body">         
             <div class="row" >
                <div class="col-lg-2 col-md-2">
                    <label>Sucursal</label>
                    <br>
                    <select name="sucursal" id="sucursal" class="form-control" required disabled>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $lote->Lot_Sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                </div>               
                <div class="col-lg-2 col-md-2">
                    <label>Rubro</label>
                    <br>
                    <select name="familia" id="familia" class="form-control" required disabled>
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == $lote->Lot_Familia ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                </div>               
                <div class="col-lg-2 col-md-2">
                    <label>Filtro</label>
                    <br>
                    <input class="form-control" type="text" name="filtro" id="filtro" value="{{$lote->Lot_Filtro}}" disabled>
                </div> <!-- fin de col -->
                <div class="col-lg-4 col-md-4">
                    <label>Observaciones</label>
                    <br>
                    <input class="form-control" type="text" id="observacion" name="observacion" value="{{$lote->Lot_Observ}}">
                </div>

            </div>
            </div>
        </div> <!-- Fin Panel Info -->

   </div> <!-- fin de col -->
  </div> <!-- fin de row -->
  <div class="row">
        <div class="col-lg-12 col-md-12">
            <button type="button" class="btn btn-default pull-right" onClick="document.location = 'index'">Regresar</button>
        </div>
  </div>

 
  <div class="row">
  <br>
  <div class="col-sm-6">
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <table id="tabla_total"
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
 
  </div> <!-- fin de row -->
 
  <div class="row">
  <div class="col-sm-12">
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <div class="panel-heading">
            <div class="row">
              <div class="col-lg-7 col-md-7" >
                 <h3 class="panel-title">A J U S T E S</h3>
              </div>
             </div>  
          </div>         

         <table id="tabla_ajuste"
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
            <th  data-field="mov_cantidad" data-align="right" data-sortable="true">Ajuste</th>
            <th  data-field="marca" data-align="left"  data-sortable="true">Marca</th>
            <th  data-field="Prod_Id" data-align="left"  data-sortable="true">Producto</th>
            <th  data-field="Prod_Descripcion" data-align="left"  data-sortable="true">Descripción</th>
            <th  data-field="Prod_Categoria" data-align="left"  data-sortable="true">Categoria</th>
            <th  data-field="Prod_Precio" data-align="right" data-sortable="true">Precio</th>
          </tr>
          </thead>
       </table>
    </div> <!-- fin Panel Tabla -->
   </div> <!-- fin de col -->
 
  </div> <!-- fin de row -->

  <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <select name="groupby_ajuste" id="groupby_ajuste" class="form-control">
                        <option value="" >Agrupar por..</option>
                        <option value="marca" >Marca</option>
                        <option value="Prod_Categoria">Categoria</option>
                    </select>
                </div>      
        </div>      
  </div>   <!-- /.Row -->
    <div class="row">
            <div class="col-md-6">
              <table id="tabla_tot_ajuste"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="success" data-field="label" data-halign="center"  data-align="left" data-sortable="true">AJUSTES</th>
                <th data-field="cantidad" data-sortable="true" data-halign="center" data-align="right">Cantidad</th>
                <th data-field="value" data-sortable="true" data-halign="center" data-align="right">%</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
  </div>   <!-- /.Row -->

  <br>
  <br>
        <!-- Panel De la Tabla Ingresados -->
        <div class="panel panel-success">
          <div class="panel-heading">
            <div class="row">
              <div class="col-lg-7 col-md-7" >
                 <h3 class="panel-title">I N G R E S A D O S </h3>
              </div>
             </div>  
          </div>         
          <table id="tabla_ingresados"
           data-toggle="table"
           data-search="true"
           data-show-print="true"
           data-show-export="true" 
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""
           class="table table-striped"
           >
          <thead>
          <tr>
            <th  data-field="Lot_Observ" data-align="left"  data-sortable="true">Sector</th>
            <th  data-field="stock_ingresado" data-align="right" data-sortable="true">Cantidad</th>
            <th  data-field="stock_bd" data-align="right" data-sortable="true">Actual en Bd</th>
            <th  data-field="marca" data-align="left"  data-sortable="true">Marca</th>
            <th  data-field="Prod_Id" data-align="center"  data-sortable="true">Código</th>
            <th  data-field="Prod_Categoria" data-align="center"  data-sortable="true">Categoria</th>
            <th  data-field="Prod_Descripcion" data-align="left"  data-sortable="true">Descripción</th>
            <th  data-field="Prod_Precio" data-align="right" data-sortable="true">Precio</th>
            <th  data-field="Prod_Precio2" data-align="right" data-sortable="true">Prec.Min</th>

          </tr>
          </thead>
       </table>

    <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <select name="groupby" id="groupby" class="form-control">
                        <option value="" >Agrupar por..</option>
                        <option value="Lot_Observ" >Sector</option>
                        <option value="marca" >Marca</option>
                        <option value="Prod_Categoria">Categoria</option>
                    </select>
                </div>      
        </div>      
  </div>   <!-- /.Row -->
    <div class="row">


            <div class="col-md-6">
              <table id="tabla_tot_ingresos"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="success" data-field="label" data-halign="center"  data-align="left" data-sortable="true">INGRESADOS</th>
                <th data-field="cantidad" data-sortable="true" data-halign="center" data-align="right">Cantidad</th>
                <th data-field="value" data-sortable="true" data-halign="center" data-align="right">%</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
  </div>   <!-- /.Row -->

  </div> <!-- fin Panel Tabla -->


  <div class="row">
        <div class="col-lg-12 col-md-12">
            <button type="button" class="btn btn-default pull-right" onClick="document.location = 'index'">Regresar</button>
            <br>
            <br>
        </div>
  </div>


</form> 


@endsection()

@section('scrip')

<script>

$(document).ready(function() {

        $("#lblestado").html( '<b>FINALIZADO</b>')   
        consultar(); // Cargas la tablas

        $('select[name="groupby"]').change(function () {
            console.log($(this).val())
            generarTotales( $('#groupby').val() , $('#tabla_ingresados') , $('#tabla_tot_ingresos') ,'stock_ingresado'  )  // Genera totales por Codigo Col =0 Moneda Col =2
        });    

        $('select[name="groupby_ajuste"]').change(function () {
            console.log($(this).val())
            generarTotales($('#groupby_ajuste').val() , $('#tabla_ajuste') , $('#tabla_tot_ajuste') ,'mov_cantidad'  )  // Genera totales por Codigo Col =0 Moneda Col =2
        });   
 
        $('#observacion').change( function() {
            ActualizaDatosLote()           
        });

}); // Fin de ready


function ActualizaDatosLote()  {
            
            $.ajax({
                global: false,
                dataType: "json",
                data: { idcompra: $("#numlot").val(),
                    observ: $("#observacion").val()
                },
                url:   'ActualizaDatosLote',
                type:  'get'
                }).done(function(data) {
//                        $("#idcompra").val(data.idcompra) // En el 1er Item lo genera
            });
} 

function generarTotales(columna , $table , $tableTotales , nombre_col_cant )  {

  // En columna tiene que ir como se llama en la grilla
  // Recorro la tabla para actualizar los Totales
 // var $table = $('#tabla_ingresados'); 
//  var $tableTotales = $('#tabla_tot_ingresos');

  $table.bootstrapTable('refreshOptions', {
              groupBy: true,
              groupByField: columna
            });

  var tablajson = $table.bootstrapTable('getData');

  var cant  = 0 ;
  var cant_total = 0;

  var TotalesCodigo = [];
  var TotalesCantidad = [];
  var pos=0;

  for (var fila in tablajson) {
    cant = parseFloat(tablajson[fila][nombre_col_cant]) 
    cant_total = cant_total + cant 
    // vectores acumuladores
    pos = TotalesCodigo.indexOf(tablajson[fila][columna]) 
    if (  pos == -1 ) {
        pos = TotalesCodigo.length
        TotalesCodigo.push (tablajson[fila][columna])
        TotalesCantidad.push(0)
    } 
    TotalesCantidad[pos] =  TotalesCantidad [pos] + parseFloat( cant)
  }

  var DataTabla = [];
  var indiceT = 0   
  TotalesCodigo.forEach(function (elemento, indice, array) {
     indiceT = indiceT + 1
     procentaje = 0   
     if (cant_total != 0) {
       procentaje = TotalesCantidad[indice] / cant_total * 100
     }
     DataTabla[indice] = {
       cantidad:  TotalesCantidad[indice] ,
       value:  procentaje.toFixed(2) ,
       label:   TotalesCodigo[indice] 
      }
  }) // Fin recorro Vec Acumulador
  if (cant_total != 0) {
    DataTabla[indiceT] = {
       cantidad:  cant_total ,
       value:  "100" ,
       label: "<b> T O T A L E S </b>"
    }
  }
  $tableTotales.bootstrapTable('load', DataTabla);

} // Fin GenerarTotales                                  


    function consultar()  {

        // Tomo los datos de entrada
        var $table = $('#tabla_ajuste'); 
        var $table_total = $('#tabla_total');
        var $tabla_ingresados = $('#tabla_ingresados'); 
        
        $numlot = $("#numlot").val();

        $.ajax({
            dataType: "json",
            data: { numlot: $numlot  },
            url:   'consulta_datos',
            type:  'get',
            success: function(data){
                $table_total.bootstrapTable('load', data.totales);
                $table.bootstrapTable('load', data.results);
                $tabla_ingresados.bootstrapTable('load', data.ingresados);
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } 

</script>

@endsection()