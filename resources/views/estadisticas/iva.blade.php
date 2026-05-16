@extends('template.informes')
@section('titulo','Estadisticas Libros Iva')
   
@section('contenido')

<?php 
    $anio = date("Y");  
    $mes = date("m");  
?>    


       
<form class="form-inline" role="form" >

    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-info alert-dismissable">
                <div data-dismiss="alert" aria-hidden="true"></div>
                <i class="fa fa-info-circle"></i> <strong>Estadísticas Libros IVA</strong>
            </div>
        </div>
    </div>    <!-- /.row -->
    
    <!-- Fila de Informes -->
    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row">
                        &nbsp&nbsp<i class="fa fa-line-chart"></i><strong> Diferencial entre Compras/Ventas</strong>                          
                        <div class="form-group pull-right">
                        <label class="control-label">Tipo:</label>
                        <select id="tipo_mto" name="tipo_mto" class="form-control">
                            <option value="T">Montos Totales</option>
                            <option value="I">Montos IVA</option>
                        </select>
                        </div>  
                    </div>                             
                </div> <!-- Fin panel-heading -->
                <div class="panel-body">
                    <div class="form-group">
    		   	            <label class="control-label">Año:</label> 
                        <input type="number" class="form-control" id="anio1_dif1" value="<?= date("Y") ?>" maxlength="4"
           					 required/>
                    </div>
                    <button type="button" id="btnconsulta" onClick="informe_por_tipo_dif()" class="btn btn-primary pull-right">Consultar</button>
                      <div id="morris-line-dif1"></div>
                      <table id="tabla_dif1"
                       data-toggle="table"
                       data-cache = "false"
                       data-show-export="true"
                       data-show-print="true"                       
                       data-page-list=""
                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Periodo</th>
                        <th data-field="valor1" data-halign="center" data-align="right">Ventas</th>
                        <th data-field="valor2" data-halign="center" data-align="right">Compras</th>
                        <th data-field="dif" data-halign="center" data-align="right">Diferencia</th>
                        <th data-field="difmes" data-halign="center" data-align="center">% Ganancia</th>
                      </tr>
                      </thead>
                     </table>
                </div>  <!-- /.panel-body -->
            </div>    <!-- /.panel -->
        </div>   <!-- /.Col -->             

        
        <div class="col-lg-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row">
                        &nbsp&nbsp<i class="fa fa-line-chart"></i><strong> Inter Anual por Tipo</strong>                          
                        <div class="form-group pull-right">
                        <label class="control-label">Tipo:</label>
                        <select id="tipo_iva" name="tipo_iva" class="form-control">
                            <option value="V">Ventas</option>
                            <option value="C">Compras</option>
                            <option value="R">Resultado</option>
                        </select>
                        </div>  
                    </div>     
                    
            
                </div> <!-- Fin panel-heading -->
                <div class="panel-body">
                    <div class="form-group">
    		   	<label class="control-label">Año 1:</label> 
                        <input type="number" class="form-control" id="anio1_inf1" value="<?= date("Y") - 1 ?>" maxlength="4"
           					 required/>
                    </div>
                    <label class="control-label">Año 2:</label> 
                    <input type="number" class="form-control" id="anio2_inf1"  value="<?= date("Y") ?>" maxlength="4"
           					 required/>
                    <button type="button" id="btnconsulta" onClick="informe_por_tipo()" class="btn btn-primary pull-right">Consultar</button>
                          
                    <div id="morris-line-inf1"></div>
                      <table id="tabla_inf1"
                       data-toggle="table"
                       data-cache = "false"
                       data-show-export="true"
                       data-show-print="true"                       
                       data-page-list=""
                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Periodo</th>
                        <th data-field="valor1" data-halign="center" data-align="right"><div id="lbl_anio1_inf1"> Año 1</div></th>
                        <th data-field="valor2" data-halign="center" data-align="right"><div id="lbl_anio2_inf1"> Año 2</div></th>
                        <th data-field="dif" data-halign="center" data-align="center">Dif % InterAnual</th>
                        <th data-field="difmes" data-halign="center" data-align="center">Dif % Mes Anterior</th>
                      </tr>
                      </thead>
                     </table>
                </div>  <!-- /.panel-body -->
            </div>    <!-- /.panel -->
        </div>   <!-- /.Col -->             

    </div>   <!-- /.Row -->
    
    

                
</form> 
                
@endsection <!-- Fin Contenido -->

@section('scrip')

<script>

    //  Para elegir color en Google poner "elegir color"
    //  -------------------------
        


    $(document).ready(function(){
        // Ya cargo de entrada
         informe_por_tipo_dif();
         informe_por_tipo();
    });    



// Diferecias por Tipo
// ===========================
  var grafico_dif1 = new Morris.Line({
    element          : 'morris-line-dif1',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a', 'b'],
    labels           : ['Ventas','Compras'],
    lineColors: ['#1E25B2','red'], // Azul y Rojo
    lineWidth        : 2,
    parseTime : false,
    preUnits    : '$',
    smooth: false,
    gridTextSize     : 10
  });

  function informe_por_tipo_dif() {
        var action_name = "informe_portipo_dif";
        var formdata = {
            tipo_mto: $('#tipo_mto').val(),	
            anio1: $('#anio1_dif1').val()
        };
        var $table = $('#tabla_dif1'); 
        $.get('iva_proceso?action=' + action_name, formdata, "json")
                .done(function(data) {
                     grafico_dif1.setData(data.grafico);                             
                     $table.bootstrapTable('load', data.tabla);
                })
                .fail(function(xhr,err) {                   
                    msgerror( xhr.responseText);
        });
  }; // Fin Informe por tipo Dif




  //InterAnual por Tipo   
  var grafico_inf1 = new Morris.Line({
    element          : 'morris-line-inf1',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a', 'b'],
    labels           : ['Año 1','Año 2'],
    lineColors: ['#1E25B2','#1EB236'], // Verde y Azul
    lineWidth        : 2,
    parseTime : false,
    hoverCallback: function (index, options, content, row) {
     return "<b>" + row.y + "</b><br>" + 
            "<b><div class='ran-azul'>" + $('#anio1_inf1').val() + ": </b> $" + numberFormat(row.a) + "</div>" +
            "<b><div class='ran-verde'>" + $('#anio2_inf1').val() + ": </b> $" + numberFormat(row.b) + "</div>";
    },    
    preUnits         : '$',
    smooth: false,
    gridTextSize     : 10
  });

  function informe_por_tipo() {
        var action_name = "informe_portipo";
        var formdata = {
            	tipo_iva: $('#tipo_iva').val(),
            	anio1: $('#anio1_inf1').val(),
            	anio2: $('#anio2_inf1').val()
        }
        var $table = $('#tabla_inf1'); 
        $.get('iva_proceso?action=' + action_name, formdata, "json")
                .done(function(data) {
                     grafico_inf1.setData(data.grafico);
                     //falta ver como cambiar el titulo de la tabla console.log($table);
                     $table.bootstrapTable('load', data.tabla);
                     $('#lbl_anio1_inf1').html( $('#anio1_inf1').val());
                     $('#lbl_anio2_inf1').html( $('#anio2_inf1').val());                     
                })
                .fail(function(xhr,err) {                   
                    msgerror( xhr.responseText);
        });
  }; // Fin Informe por tipo
   
</script>

@endsection <!-- Fin scrip -->
