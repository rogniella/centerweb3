@extends('template.main_alta_modal')
@section('titulo','Consulta de Ventas por Fecha')
   
@section('contenido')

<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de Movimientos por Mostrador</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == 0 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                </div>      

                <div class="form-group">
                    <label class="control-label">Fechas:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn">
                      <span>
                        <i class="fa fa-calendar"></i> Rango de fecha
                      </span>
                        <i class="fa fa-caret-down"></i>
                    </button>              
                    </div>
                </div>

              <div class="form-group">
                <button type="button" class="btn btn-primary pull-right" id="form-search-btn" onclick="consultar()">Actualizar</button>
              </div>

            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

     </div> <!-- Fin col -->
  </div>   <!-- /.Row -->

  <!-- Panel De la Tabla -->
  <div class="panel panel-success">     

  <!-- Segunda Fila de Informes -->
  <!-- Fila  Panel De Totales -->
  <div class="row">


        <div class="col-sm-12">
           <div class="alert alert-danger" role="alert" id="tituloTotal">...</div>
        </div> <!-- fin de col 12 -->   
  </div>   <!-- /.Row -->
    <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <select name="groupby" id="groupby" class="form-control">
                        <option value="" >Agrupar por..</option>
                        <option value="sucursal" >Sucursal</option>
                        <option value="codigo" >Codigo</option>
                        <option value="moneda">Moneda</option>
                    </select>
                </div>      
        </div>      
  </div>   <!-- /.Row -->
    <div class="row">


            <div class="col-md-6">
              <table id="tabla_inf"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="success" data-field="label" data-halign="center"  data-align="left" data-sortable="true">H A B E R </th>
                <th data-field="mtos" data-sortable="true" data-halign="center" data-align="right">Acumulado</th>
                <th data-field="value" data-sortable="true" data-halign="center" data-align="right">%</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
            <div class="col-md-6">
                <div id="morris-area-chart"></div>
            </div>  <!-- Fin .col-lg-6 -->
  </div>   <!-- /.Row -->
    <div class="row">

            <div class="col-md-6">
              <table id="tabla_infD"
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="warning" data-field="label" data-halign="center"  data-align="left" data-sortable="true">D E B E </th>
                <th data-field="mtos" data-sortable="true" data-halign="center" data-align="right">Acumulado</th>
                <th data-field="value" data-sortable="true" data-halign="center" data-align="right">%</th>
              </tr>
              </thead>
              </table>  
            </div>  <!-- Fin .col-lg-6 -->
            <div class="col-md-6">
                <div id="morris-area-chartD"></div>
            </div>  <!-- Fin .col-lg-6 -->


    </div>  <!-- Fin .col-lg-12 -->

  </div>   <!-- /.Row -->

            <div id="toolbar">
              <label>&nbsp</label>
              <button type="button" class="pull-right btn btn-default"
                id="form-search-btn" onclick="alta()">
                <i class="glyphicon glyphicon-plus"></i> Nuevo 
              </button>
            </div>
          <table id="mitabla"
           data-toggle="table"
           data-toolbar="#toolbar"
           data-toolbar-align="right"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="sucursal"  data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="fecha"  data-halign="center" data-align="center" data-sortable="true" data-sorter="dateSorter">Fecha</th>
            <th data-field="codigo" data-halign="center" data-align="left" data-sortable="true">C&oacute;digo </th>
            <th data-field="monto" data-halign="center" data-align="right" data-sortable="true" data-sorter="priceSorter">Monto</th>
            <th data-field="moneda"  data-halign="center" data-align="center" data-sortable="true">Moneda</th>
            <th data-field="mtopesos" data-halign="center" data-align="right" data-sortable="true" data-sorter="priceSorter">Monto $</th>
            <th data-field="hora" data-halign="center" data-align="center" data-sortable="true">Hora</th>
            <th data-field="descri"> Detalle</th>
            <th data-field="tipoOT" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="idfac" data-formatter="fotmatoColSel"  data-halign="center" data-align="center" data-sortable="true">OT Nro </th>
            <th data-field="id" data-halign="center"  data-align="center" data-formatter="opcionesFormatter">Opciones</th>
          </tr>
          </thead>
       </table>
  
   </div> <!-- fin Panel Tabla -->
  </div> <!-- fin de col 12 -->    

</form> 

<?php

  $modulo_abm='cajas';  
  $campos_pantalla = [
      [ 'name' => 'MCaj_FecMov'],
      [ 'name' => 'MCaj_Sucursal'],
      [ 'name' => 'MCaj_CtaOri'],
      [ 'name' => 'MCaj_Codigo'],
      [ 'name' => 'MCaj_Monto'],
      [ 'name' => 'MCaj_Moneda'],
      [ 'name' => 'MDes_Descripcion']
  ];

?>

@include('cajas.alta_modif')

@include('common.modal_consulta')


@endsection <!-- Fin Contenido -->

@section('scrip')

<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>

   // Vbles Generales de Entreda

    var $sucursal ;

    var $fecha;
    var $fechafin;



   function opcionesFormatter(value,columnas) {
      var Id = value;

      @if(Auth::user())
        @if(Auth::user()->perfil_id == 'ADM')
        console.log(columnas.codigo.substring(0,4) )
        if ( columnas.codigo.substring(0,4) != '0900') { // Si no es trasferencia

          var opciones = '<button type="button" class="btn btn-warning btn-xs"'+
                       'title="Editar registro" onclick="showEditModalCaja(true,\''+ Id +'\')">'+
                       '<i class="glyphicon glyphicon-pencil"></i>'+
                     '</button>';
        }             
        @endif
      @endif

      return opciones;
  }



   
    $(document).ready(function(){
         // Tomo los datos de entrada
         $fecha = '<?= date("Y-m-d"); ?>';
         $fechafin = '<?= date("Y-m-d"); ?>';
         consultar();
    });      
  
    var $table = $('#mitabla'); // Tabla principal

  // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {

    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla

      if ( args [0] == 'idfac'){  // Nombre Columna                          
         // Busco los datos de la OT o Comprobante y despliega pantall Modal
         consulta_comprobante(args [2].tipoOT, args [2].idfac, args [2].sucursal)
      }; // Fin Clik Id OT

    } // Clik de La tabla    

  }); // Fin Todos los Eventos de la Tabla






    //  Cambio el Combo de Agrupacion    
    $(function () {

        $('select[name="sucursal"]').change(function () {
             consultar();
        });    

        $('select[name="groupby"]').change(function () {

            console.log($(this).val())
            $table.bootstrapTable('refreshOptions', {
                groupBy: true,
                groupByField: $(this).val()
            });
            //console.log ($('select[name="dropdown"]').val())
            generarTotales($('#groupby').val()) // Genera totales por Codigo Col =0 Moneda Col =2

         //   if ($('select[name="dropdown"]').val() == 'codigo') { 
         //       generarTotales(0) // Genera totales por Codigo Col =0 Moneda Col =2
         //   } else {
         //       generarTotales(2) // Genera totales por Codigo Col =0 Moneda Col =2                
         //   }
        });    
    });
  

    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $sucursal  = $('#sucursal').val();
       $.ajax({
            dataType: "json",
            data: { sucursal: $sucursal, fecha: $fecha, fechafin: $fechafin},
            url:   'ventas2',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales($('#dropdown').val()) // Genera totales por Codigo Col =0 Moneda Col =2

       //       if ($('select[name="dropdown"]').val() == 'moneda') { 
       //           generarTotales(2) // Genera totales por Codigo Col =0 Moneda Col =2
        //      } else {
  //              generarTotales(0) // Genera totales por Codigo Col =0 Moneda Col =2                
   //           }
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
    
    
    var areachart1 = new Morris.Donut({
        element          : 'morris-area-chart',
        data             : [
           {value: 100, label: 'Sin Datos'}
        ],
        formatter: function (x) { return x + "%"}
            }).on('click', function(i, row){
           // console.log(i, row);
    });


    var color_array = ['#DC3535', '#F26D6D', '#ECA7A7', '#F1C3C3', '#7E6F6A', '#CE64D8'];

    var areachart1D = new Morris.Donut({
        element          : 'morris-area-chartD',
        colors           : color_array,
        data             : [
           {value: 100, label: 'Sin Datos'}
        ],
        formatter: function (x) { return x + "%"}
            }).on('click', function(i, row){
           // console.log(i, row);
    });

    function generarTotales(columna)  {

        // En columna tiene que ir como se llama en la grilla
        // Recorro la tabla para actualizar los Totales
        //alert(JSON.stringify($table.bootstrapTable('getData')));
        var tablajson = $table.bootstrapTable('getData');
        var mto;
        var procentaje=0;

        // Haber
        var DataGrafico = [];
        var TotalesCodigo = [];
        var TotalesDescri = [];
        var TotalesMontos = [];
        var pos=0;
        var mtototal = 0;

        // Debe
        var DataGraficoD = [];
        var TotalesMontosD = [];
        var mtototalD = 0;

        console.log( "Totales por Col:"  + columna) 

        for (var fila in tablajson) {
            if (columna == 'codigo' ) {
                valororden = tablajson[fila]['codigo'];
            }else if (columna == 'sucursal' ){    
                valororden = tablajson[fila]['sucursal'];
            }else{    
                valororden = tablajson[fila]['moneda'];
            }
            pos = TotalesCodigo.indexOf(valororden ) 
            if (  pos == -1 ) {
                pos = TotalesCodigo.length
                TotalesCodigo.push (valororden)
                TotalesMontos.push(0)
                TotalesMontosD.push(0)
                if (columna == 'codigo' ) {
                    TotalesDescri.push(valororden) // Si es por Codigo va la descri del codigo
                }else if (columna == 'sucursal  ' ) {
                    TotalesDescri.push(valororden) // Si es por Codigo va la descri del codigo
                }else{
                    TotalesDescri.push( descripcionMoneda( valororden )) // Es por moneda
                }
            } 

            mto = numberFormatBd ( tablajson[fila]['mtopesos'] ) // Los importes estan en la col 3
            switch  (tablajson[fila]['codH_D'] ) {
                case 'D': // Debitos
                   TotalesMontosD[pos] =  TotalesMontosD [pos] + parseFloat(mto)
                   mtototalD = mtototalD +  parseFloat(mto)
                   break;
                case 'H': // Haber
                   TotalesMontos[pos] =  TotalesMontos [pos] + parseFloat(mto)
                   mtototal = mtototal +  parseFloat(mto)
                   break;
                case 'T': // es transeferencia     
                   mto = 0
            }   

        }

        var $tableTotales = $('#tabla_inf'); 
        var $tableTotalesD = $('#tabla_infD'); 
        var filas = 0
        var indiceH = 0
        var indiceD = 0

        TotalesCodigo.forEach(function (elemento, indice, array) {
            if(parseFloat(TotalesMontos[indice]) > 0) {  // Si es haber
              procentaje = TotalesMontos[indice] / mtototal * 100
              DataGrafico[indiceH] = {
               mtos:  formatearNumeroConSeparadorDeMiles(TotalesMontos[indice] , cantDecMonto) ,
               value:  procentaje.toFixed(2) ,
               label:  TotalesDescri[indice]
              }
              indiceH = indiceH + 1
            }
            if(parseFloat(TotalesMontosD[indice]) > 0) {  // Si es Debe
              procentaje = TotalesMontosD[indice] / mtototalD * 100
              DataGraficoD[indiceD] = {
               mtos:  formatearNumeroConSeparadorDeMiles(TotalesMontosD[indice] , cantDecMonto) ,
               value:  procentaje.toFixed(2) ,
               label:  TotalesDescri[indice]
              }
              indiceD = indiceD + 1               
            }  
        })
        areachart1.setData(DataGrafico);
        areachart1D.setData(DataGraficoD);
        if (mtototal > 0) {
            DataGrafico[indiceH] = {
               mtos:  "<b>" +  formatearNumeroConSeparadorDeMiles(mtototal, cantDecMonto)  + "</b>" ,
               value:  "100" ,
               label:  "<b> T O T A L E S </b>"
            }
            DataGraficoD[indiceD] = {
               mtos:  "<b>" + formatearNumeroConSeparadorDeMiles(mtototalD, cantDecMonto)  + "</b>" ,
               value:  "100" ,
               label:  "<b> T O T A L E S </b>"
            }
        };    
        $tableTotales.bootstrapTable('load', DataGrafico);
        $tableTotalesD.bootstrapTable('load', DataGraficoD);
        var saldo =  parseFloat(mtototal) - parseFloat(mtototalD) 
        if (saldo < 0) {
            $('#tituloTotal').html('<b><div style="color:RED;"> S A L D O : $  ' + formatearNumeroConSeparadorDeMiles(saldo , cantDecMonto) + '  DEBE </div></b>');
        }else{
            $('#tituloTotal').html('<b><div style="color:BLACK;"> S A L D O : $  ' +  formatearNumeroConSeparadorDeMiles(saldo , cantDecMonto) + '  </div></b>');
        }  
    } // Fin GenerarTotales
  
  
    function alta() {
      //  window.location.href="{{ route('cajas.altas') }}";
      showEditModalCaja(false, 0)

    }        

/*=============================================
DATE RANGE
=============================================*/

$('#daterange-btn').daterangepicker(
  {
    ranges   : {
      'Hoy'       : [moment(), moment()],
      'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Últimos 7 días' : [moment().subtract(6, 'days'), moment()],
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment(),
    endDate  : moment()
    },
    function (start, end) {
        $('#daterange-btn span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))

        $fecha = start.format('YYYY-M-D');
        $fechafin = end.format('YYYY-M-D');
        consultar();
    }
  )  // Fin $('#daterange-btn').daterangepicker(

</script>
 

@endsection <!-- Fin scrip -->
