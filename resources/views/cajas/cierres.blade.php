@extends('template.informes')
@section('titulo','Consulta de Cierre de Cajas')
   
@section('contenido')

<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de Cierre de Cajas por Fecha</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <select  name="sucursal" id="sucursal"  class="form-control">
                      @include('common.combo_sucursal')
                    </select>
                </div>      


                <div class="form-group">
                    <label class="control-label">Fechas:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right daterange-btn" id="daterange-btn">
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
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="sucursal" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="id" data-halign="center" data-align="center" data-sortable="true">Cierre</th>
            <th data-field="fecha" data-halign="center" data-align="center" data-sortable="true">Fecha</th>
            <th data-field="hora" data-halign="center" data-align="center" data-sortable="true">Hora</th>
            <th data-field="usuario" data-halign="center" data-align="center" data-sortable="true">Responsable</th>
            <th data-field="retiro_p" data-halign="center" data-align="right" data-sortable="true">Retiro $</th>
            <th data-field="tarjeta" data-halign="center" data-align="right" data-sortable="true">Tarjetas</th>
            <th data-field="ajuste" data-halign="center" data-align="right" > Ajuste $</th>
            <th data-field="ajuste_motivo"> Motivo</th>
          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->


    </div>  <!-- Fin .col-lg-12 -->

  </div>   <!-- /.Row -->

</form> 


@endsection <!-- Fin Contenido -->


@section('scrip')

<script>


   // Vbles Generales de Entreda
    var $fecha;
    var $fechafin;
   
    $(document).ready(function(){
         // Tomo los datos de entrada
         $fecha = '<?= date("Y-m-d"); ?>';
         $fechafin = '<?= date("Y-m-d"); ?>';
         consultar();
    });      
  
    var $table = $('#mitabla'); // Tabla principal


    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $sucursal  = $('#sucursal').val();
       $.ajax({
            dataType: "json",
            data: { sucursal: $sucursal  , fecha: $fecha, fechafin: $fechafin},
            url:   'cierres2',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
      

    //  Cambio el Combo de Agrupacion    
    $(function () {
        $('select[name="sucursal"]').change(function () {
             consultar();
        });    
    });      

        
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
