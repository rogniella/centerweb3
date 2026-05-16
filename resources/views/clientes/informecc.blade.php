@extends('template.informes')
@section('titulo','Informe de Cuentas Corrientes')
   
@section('contenido')

<form class="form-inline" role="form" >
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title" id="titulo" >Cuenta Corriente</h3>
            </div>
            <div class="panel-body">
                       <div class="form-group">
                        <label class="control-label">Condición:</label>
                        <select id="tipo_inf1" name="tipo_inf1" class="form-control">
                          <option value="P">Pendientes</option>
                          <option value="T">Todos</option>
                        </select>
                        </div>  

                <div class="form-group">
                    <label class="control-label">Fechas:</label>
                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn">
                      <span>
                        <i class="fa fa-calendar"></i> Últimos 30 días
                      </span>
                        <i class="fa fa-caret-down"></i>
                    </button>              
                    </div>
                </div>
                <div class="form-group">
                  <label class="control-label">Cuenta:</label>
                  <input type="number" value="<?= $cta ; ?>" class="form-control" id="cta" name="cta" disabled="true" required/>
                  <input type="text" value="<?= $apenom  ; ?>" class="form-control"  disabled="true">

                </div>               
            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

   <div class="row">
    <!-- Panel De Totales -->
    <div class="col-sm-12">
        <div class="panel panel-warning">
            <div id="saldo" class="panel-heading">
                Saldo Actual: $
            </div>   <!-- /.panel-heading -->
        </div>   <!-- / Fin.panel -->
    </div>  <!-- Fin .col-lg-12 -->

  </div>   <!-- /.Row -->
       
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">     

          <table id="mitabla"
           data-toggle="table"
           data-search="true"
           data-show-export="true"
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           class="table table-striped"
          >
          <thead>
          <tr>
            <th data-field="fecha" data-halign="center" data-align="left" data-sortable="true">Fecha </th>
            <th data-field="comprobante" data-halign="center" data-align="left" data-sortable="true">Comprobante</th>
            <th data-field="estado" data-halign="center" data-align="center" data-sortable="true">Estado</th>
            <th data-field="vencimiento" data-halign="center" data-align="center" data-sortable="true">Vencimiento</th>
            <th data-field="debe" data-halign="center" data-align="right" data-sortable="true">Debe</th>
            <th data-field="haber" data-halign="center" data-align="right" data-sortable="true">Haber</th>
            <th data-field="saldo" data-halign="center" data-align="right" data-sortable="true">Saldo</th>
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

	
    var $table = $('#mitabla'); // Tabla principal

    // Funcion de carga de Tablas
    function consultar($fecha,$fechafin)  {
	// LLama a 2da pagina con la logica de la busqueda
	// ------------------------------------------------      
        $tipoinf = $('#tipo_inf1').val()
        if ($tipoinf == 'P') {
            $('#titulo').html('Consulta Cuenta Corriente: PENDIENTES ' );
        }else{
            $('#titulo').html('Consulta Cuenta Corriente del ' + $fecha + ' al ' + $fechafin);
        }
        $cta = $('#cta').val()
	$.ajax({
            dataType: "json",
            data: { tipo_inf: $tipoinf,cta: $cta, fecha: $fecha, fechafin: $fechafin},
            url:   'informecc_proceso',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                $('#saldo').html('<b> Saldo Actual: $ ' + data.saldo + '</b>' );
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
           
/*=============================================
DATE RANGE
=============================================*/
    $(document).ready(function(){
         // Tomo los datos de entrada
         $fecha = moment().subtract(29, 'days').format('YYYY-MM-DD');
         $fechafin = moment().format('YYYY-MM-DD');
    	 consultar($fecha,$fechafin);
    });      


    // Cambio el Combo de Agrupacion    
    $(function () {
        $('select[name="tipo_inf1"]').change(function () {
             consultar($fecha,$fechafin);
        });    
    });


$('#daterange-btn').daterangepicker(
  {
    ranges   : {
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Mes Anterior'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
      'Último 12 meses'  : [moment().subtract(1, 'year').startOf('month'),  moment().endOf('month')],
      'Todos los Movimentos'  : [moment().subtract(30, 'year').startOf('year'),  moment().endOf('month')]
    },
    startDate: moment().subtract(29, 'days'),
    endDate  : moment()
    },
    function (start, end) {
        $('#daterange-btn span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))
        var fechaInicial = start.format('YYYY-MM-DD');
        var fechaFinal = end.format('YYYY-MM-DD');
    	consultar(fechaInicial,fechaFinal);
    }
  )  // Fin $('#daterange-btn').daterangepicker(
  
</script>

@endsection <!-- Fin scrip -->