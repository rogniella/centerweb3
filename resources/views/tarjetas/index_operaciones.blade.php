@extends('template.informes')
@section('titulo','Consulta de Operaciones con Tarjetas')
   
@section('contenido')

<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de Operaciones con Tarjetas</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">

                {!! Form::select('filtro0', $productos, '', ['id' => 'filtro0', 'class' => 'form-control', 'required']) !!}    


                  <input type="text" class="form-control" name="filtro2" id="filtro2"  placeholder="Nro.Liquidación" value="">

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
           data-show-footer="true"            
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="fecha_clearing" data-halign="center" data-align="center" data-footer-formatter="idTotal" data-sortable="true" >Fecha Acred.</th>
            <th data-field="fecha_presentacion" data-halign="center" data-align="center" data-sortable="true" >Presentación</th>

            <th data-field="idliquidacion"  data-sortable="true" data-halign="center" data-align="center" >Nro.Liquidación</th>
            <th data-field="descripcion"  data-sortable="true"data-halign="center" data-align="left" >Tarjeta</th>
            <th data-field="mto_bruto" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Mto.Ventas</th>
            <th data-field="mto_sindto" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Mto.Acreditar</th>
            <th data-field="mto_arancel" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Arancel</th>
            <th data-field="iva_arancel" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter"  data-sortable="true">Iva Arancel(21)</th>

            <th data-field="mto_financiero" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter" data-sortable="true">Cost.Financiero</th>
            <th data-field="iva_financiero" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter"  data-sortable="true">Iva Cost.Finan.(10.5)</th>


            <th data-field="iva_financiero" data-sortable="true" data-footer-formatter="mtoFormatter" data-align="right"> Ret.IB</th>
            <th data-field="iva_financiero" data-sortable="true" data-footer-formatter="mtoFormatter" data-align="right"> Percep.Iva</th>
            <th data-field="iva_financiero" data-sortable="true" data-footer-formatter="mtoFormatter" data-align="right">Otros.Deb</th>
            <th data-field="plazo_pago" data-sortable="true" data-align="right">Plazo</th>
            <th data-field="observacion"  data-sortable="true"data-halign="center" data-align="left" >Observación</th>

          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->
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
    // Calculo el todal 
    var field = this.field
    return '$ ' +   data.map(function (row) {
          return +  ( row[field].toFixed(2) )
      }).reduce(function (sum, i) {
        
        // Sum es string  i e numero
        total = parseFloat(sum) + i

//        console.log  ( typeof sum , typeof i , typeof total  , Number.parseFloat(total).toFixed(2) )

//        console.log  ( sum , i , total  , Number.parseFloat(total).toFixed(2) )
        //ret = total.toFixed(2)
        return  Number.parseFloat(total).toFixed(2) 
      }, 0)  
   }


    var $fecha = '';
    var $fechafin ;

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

    var $table = $('#mitabla'); // Tabla principal

  
    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro0: $('#filtro0').val(), filtro2: $('#filtro2').val()  ,fecha: $fecha , fechafin: $fechafin  },
            url:   'buscar_operaciones',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
      
</script>
 
@endsection <!-- Fin scrip -->
