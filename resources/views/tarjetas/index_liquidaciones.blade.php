@extends('template.informes')
@section('titulo','Consulta de Liquidaciones de Tarjetas')
   
@section('contenido')

<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de Liquidaciones de Tarjetas</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                <label class="control-label">Tarjetas:</label>
                <select name="filtro0" id="filtro0" class="form-control" required>
                        @foreach($productos as $key => $value)
                            <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                </select>    
                <label class="control-label">Liquidación:</label>
                  <input type="text" class="form-control" name="filtro2" id="filtro2"  placeholder="Nro.Liquidación" value="">

                <label class="control-label">Acreditación:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn">
                      <span>
                        <i class="fa fa-calendar"></i> Rango de fecha
                      </span>
                        <i class="fa fa-caret-down"></i>
                    </button>              
                    </div>

                <label class="control-label">Fec.Presentación:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn-ope">
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

        <!-- Panel De la Tabla -->
        <div class="panel panel-success">     
          <table id="mitabla"
           data-toggle="table"
           data-toolbar-align="right"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list="" 
           data-height="460"     
           data-show-footer="true"            
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="fecha_clearing" data-halign="center" data-align="center" data-footer-formatter="idTotal" data-sortable="true" >Fecha Acred.</th>
            <th data-field="fecha_presentacion" data-halign="center" data-align="center" data-sortable="true" >Presentación</th>

            <th data-field="idliquidacion"  data-formatter="fotmatoColSel" data-sortable="true" data-halign="center" data-align="center" >Nro.Liquidación</th>
            <th data-field="descripcion"  data-sortable="true"data-halign="center" data-align="left" >Tarjeta</th>
            <th class="success"  data-field="mto_bruto" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Mto.Ventas</th>
            <th data-field="mto_neto" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Mto.Acreditar</th>

            <th class="warning" data-field="ret_ib" data-sortable="true" data-footer-formatter="mtoFormatter" data-align="right"> Ret.IB</th>
            <th class="warning" data-field="mto_arancel" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Arancel</th>
            <th class="warning" data-field="iva_arancel" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter"  data-sortable="true">Iva Arancel(21)</th>

            <th class="warning" data-field="costo_financiero" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Cost.Financiero</th>
            <th class="warning" data-field="iva_anticipo" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter"  data-sortable="true">Iva Cost.Finan.(10.5)</th>

            <th data-field="percep_iva" data-sortable="true" data-footer-formatter="mtoFormatter" data-align="right"> Percep.Iva</th>
            <th data-field="mto_otros_deb" data-sortable="true" data-footer-formatter="mtoFormatter" data-align="right">Otros.Deb</th>
            <th data-field="plazo_pago" data-sortable="true" data-align="right">Plazo</th>
            <th data-field="observacion"  data-sortable="true"data-halign="center" data-align="left" >Observación</th>

          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->

        <!-- Panel De Totales -->               
        <table id="tabla_tot"
                data-show-export="true" 
                data-export-data-type="all"  
                data-toggle="table"
                data-cache = "false"
                data-page-list=""      
                class="table table-striped"
              >
              <thead>
              <tr>
                <th class="success" data-field="label" data-halign="center"  data-align="left" data-sortable="true">T A R J E T A </th>
                <th data-field="cantidad" data-sortable="true" data-halign="center" data-align="right">Operaciones</th>
                <th data-field="mtos" data-sortable="true" data-halign="center" data-align="right">Ventas</th>
                <th data-field="ret_ib" data-sortable="true" data-halign="center" data-align="right">Ret.IB</th>
                <th data-field="mto_arancel" data-sortable="true" data-halign="center" data-align="right">Arancel</th>
                <th data-field="iva_arancel" data-sortable="true" data-halign="center" data-align="right">Iva (21)</th>
                <th data-field="costo_financiero" data-sortable="true" data-halign="center" data-align="right">Cost.Finac</th>
                <th data-field="iva_anticipo" data-sortable="true" data-halign="center" data-align="right">Iva (10.5)</th>
              </tr>
              </thead>
        </table>  

    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

</form> 

@endsection <!-- Fin Contenido -->

@section('scrip')


<script>

   // Formatea linea de Totales de la Grilla
   function idTotal() {
     return 'T O T A L E S'
   }

  
   function mtoFormatter(data) {
    var field = this.field
    return '$ ' + data.map(function (row) {
        var val = parseFloat(row[field])
        return isNaN(val) ? 0 : val
    }).reduce(function (sum, i) {
        return Number.parseFloat((parseFloat(sum) + i)).toFixed(2)
    }, 0)
   }

  function fotmatoColSel(value,row,index) {
      // Lo usamos para hacer que una columna quede como hipervinculo y pueda pedir mas detalle
        //value: el valor del campo. 
        //row: los datos de la fila (un vector con toda la fila.
        //index: el indice de la fila.
        var Id = value;
        //console.log(row)
        return '<a>'+ Id  + '</a>'            ;
  }    // Vbles Generales de Entreda


    var $fecha = '';
    var $fechafin ;
    var $fecha_ope = '';
    var $fechafin_ope ;

    $(document).ready(function(){
        // Tomo los datos de entrada
        //consultar();
    });      

    $('#daterange-btn').daterangepicker(
     {
      ranges   : {
        'Hoy'       : [moment(), moment()],
        'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Próximos 30 días': [ moment() , moment().subtract(-30, 'days') ],
        'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
        'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        '2do Último mes'  : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')]
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
  
    );  // Fin $('#daterange-btn').daterangepicker(


    $('#daterange-btn-ope').daterangepicker(
     {
      ranges   : {
        'Hoy'       : [moment(), moment()],
        'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
        'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],

        '2do Último mes'  : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')]
     },
     startDate: moment(),
     endDate  : moment()
     },
     function (start, end) {

          $('#daterange-btn-ope span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))
          $fecha_ope = start.format('YYYY-M-D');
          $fechafin_ope = end.format('YYYY-M-D');
          consultar();

     }
  
    );  // Fin $('#daterange-btn').daterangepicker(



    var $table = $('#mitabla'); // Tabla principal

 // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {

    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla
      if ( args [0] == 'idliquidacion'){  // Nombre Columna                          
        // Busco los datos 
        idLiq = args [2].idliquidacion
        ruta = 'lista_operaciones?idLiq=' + idLiq
        window.open(ruta, '', '_blanck');
      }; // Fin Clik 

    } // Clik de La tabla    
   }); // Fin Todos los Eventos de la Tabla

  
    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro0: $('#filtro0').val(), filtro2: $('#filtro2').val()  ,fecha: $fecha , fechafin: $fechafin ,fechaope: $fecha_ope , fechafinope: $fechafin_ope  },
            url:   'buscar_liquidaciones',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                generarTotales('descripcion') // Genera totales por Tarjeta Col =1

            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()


    function generarTotales(columna)  {

        // En columna tiene que ir como se llama en la grilla
        // Recorro la tabla para actualizar los Totales
        
        var tablajson = $table.bootstrapTable('getData');


        var mto;

        var TotalesCodigo = [];
        var TotalesCantidad = [];
        var TotalesMontos = [];
        var TotalesIb = [];
        var TotalesMtoIva1 = [];
        var TotalesIva1 = [];
        var TotalesMtoIva2 = [];
        var TotalesIva2 = [];

        var pos=0;

        console.log (columna)

        for (var fila in tablajson) {
            mto = tablajson[fila]['mto_bruto'] // Los importes estan en la col 3

            // vectores acumuladores
            pos = TotalesCodigo.indexOf(tablajson[fila][columna]) 
            if (  pos == -1 ) {
                pos = TotalesCodigo.length
                TotalesCodigo.push (tablajson[fila][columna])
                TotalesCantidad.push(0)
                TotalesMontos.push(0)
                TotalesIb.push(0)
                TotalesMtoIva1.push(0)
                TotalesIva1.push(0)
                TotalesMtoIva2.push(0)
                TotalesIva2.push(0)
            } 
            TotalesCantidad[pos] =  TotalesCantidad [pos] + 1
            TotalesMontos[pos] =  TotalesMontos [pos] + parseFloat(mto)
            TotalesIb[pos] =  TotalesIb [pos] + parseFloat(tablajson[fila]['ret_ib'])
            TotalesMtoIva1[pos] =  TotalesMtoIva1 [pos] + parseFloat(tablajson[fila]['mto_arancel'])
            TotalesIva1[pos] =  TotalesIva1 [pos] + parseFloat(tablajson[fila]['iva_arancel'])
            TotalesMtoIva2[pos] =  TotalesMtoIva2 [pos] + parseFloat(tablajson[fila]['costo_financiero'])
            TotalesIva2[pos] =  TotalesIva2 [pos] + parseFloat(tablajson[fila]['iva_anticipo'])

        }

        var DataTabla = [];
        var $tableTotales = $('#tabla_tot'); 
        TotalesCodigo.forEach(function (elemento, indice, array) {
            DataTabla[indice] = {
               mtos:  TotalesMontos[indice].toFixed(2) ,
               ret_ib:  TotalesIb[indice].toFixed(2) ,
               mto_arancel:  TotalesMtoIva1[indice].toFixed(2) ,
               iva_arancel:  TotalesIva1[indice].toFixed(2) ,
               costo_financiero:  TotalesMtoIva2[indice].toFixed(2) ,
               iva_anticipo:  TotalesIva2[indice].toFixed(2) ,
               cantidad:  TotalesCantidad[indice] ,
               label:   TotalesCodigo[indice] 
              }
        }) // Fin recorro Vec Acumulador

        $tableTotales.bootstrapTable('load', DataTabla);

    } // Fin GenerarTotales                                  

      
</script>
 
@endsection <!-- Fin scrip -->
