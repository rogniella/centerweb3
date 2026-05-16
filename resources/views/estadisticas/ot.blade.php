@extends('template.informes')
@section('titulo','Estadisticas Ordenes de Trabajo')
   
@section('contenido')

<?php 
    $anio = date("Y");  
    $mes = date("m");  
?>    
       
<form class="form-inline" role="form" >

    <div class="panel panel-info">
        <div class="panel-heading">      
          <div class="row">
            <div data-dismiss="alert" aria-hidden="true"></div>
            &nbsp<big><i class="fa fa-info-circle"></i> <strong>Estadísticas Mensuales de OT (Ordenes de Trabajos)</strong></big>
          </div>    <!-- /.row -->
        </div> <!-- Fin panel-heading -->
        <div  class="panel-body">
                    <div class="row" style="padding:2px;">
                       <div class="col-lg-3">
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Sucursal:</span>

                    <select  name="Ot_Sucursal" id="Ot_Sucursal"  class="form-control">
                      @include('common.combo_sucursal')
                    </select>

                        </div>
                      </div>    <!-- /.col -->

                       <div class="col-lg-2">
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Año 1:</span>
                          <input type="number" class="form-control" id="anio1_inf1" value="<?= date("Y") - 1 ?>" maxlength="4"
                     required/>
                        </div>
                      </div>    <!-- /.col -->
                      <div class="col-lg-2">
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Año 2:</span>
                          <input type="number" class="form-control" id="anio2_inf1" value="<?= date("Y")  ?>" maxlength="4"
                     required/>
                        </div>
                      </div>    <!-- /.col -->
                      <div class="col-lg-3">

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Tipo:</span>
                          <select id="tipo_ot" name="tipo_ot" class="form-control">
                            <option value="A">Anteojos</option>
                            <option value="L">Lentes Contacto</option>
                            <option value="C">Celulares</option>
                            <option value="G">Garantías</option>
                          </select>
                        </div>  

                        <div class="input-group" style="padding:2px;">
                            <a id="esconder" onclick="mostrar()">Mas filtros..</a>
                        </div>  

                      </div>    <!-- /.col -->

                    </div> <!-- Fin row 1ra linea de filtros -->


                    <div id="ocultar" class="row" style="padding:1px;background:white;">
                      <div class="col-lg-3">
                       <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Obra Social:</span>
                        <select id="tipo_obr" name="tipo_obr" class="form-control">
                            <option value="1">Todas</option>
                            <option value="2">Particular</option>
                            <option value="3">PAMI</option>
                        </select>                        
                       </div> 
                      </div>    <!-- /.col -->
                      <div class="col-lg-3">

                       <div class="input-group" style="padding:2px;">
                        <span class="input-group-addon">Médicos Oculistas:</span>
                        <select id="medico" name="medico" class="form-control">
                            <option value=" ">Todos</option>
                            <option value="0">Sin Medico</option>
                            <option value="10001">Paccini Laura</option>
                            <option value="10082">Meza Agustin</option>
                            <option value="10301">Rivarola Romina</option>
                            <option value="10441">Stucke Rene</option>
                        </select>                        
                       </div>
                      </div>    <!-- /.col -->
                      <div class="col-lg-3">

                       <div class="input-group" style="padding:2px;">
                        <span class="input-group-addon">Vendedor:</span>
                        <select id="vendedor" name="vendedor" class="form-control">
                            <option value="1">Todos</option>
                           <?PHP  
                                $vendedor ='';     
                           ?>
                           @include('common.combo_vendedor')
                        </select>                        
                       </div>
                      </div>    <!-- /.col -->

                    </div> <!-- Fin div oculto -->


                    <div class="row" style="padding:4px;">
                        <button type="button" id="btnconsulta" onClick="informe_por_tipo()" class="btn btn-primary pull-right">Consultar</button>
                    </div> <!-- /.row -->            

          </div> <!-- Fin panel body  Titulo --> 
        </div>  <!-- Fin panel Titulo --> 
    
    <!-- Primera Fila de Informes -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row" style="padding:2px;">
                        &nbsp<big><i class="fa fa-line-chart"></i><strong> Cantidades Inter Anuales de OT (Ordenes de Trabajos)</big> </strong>  
                    </div> <!-- /.row -->
                </div> <!-- Fin panel-heading -->
                <div class="panel-body">
                          
                    <div id="morris-line-inf1"></div>
                      <table id="tabla_inf1"
                       data-toggle="table"
                       data-cache = "false"
                       data-page-list=""

                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Periodo</th>
                        <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel"  data-field="valor1" data-halign="center" data-align="right"><div id="lbl_anio1_inf1"> Año 1</div></th>
                        <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel"  data-field="valor2" data-halign="center" data-align="right"><div id="lbl_anio2_inf1"> Año 2</div></th>
                        <th data-field="dif" data-halign="center" data-align="center">Dif % InterAnual</th>
                        <th data-field="difmes" data-halign="center" data-align="center">Dif % Mes Anterior</th>
                      </tr>
                      </thead>
                     </table>
                </div>  <!-- /.panel-body -->
            </div>    <!-- /.panel -->
        </div>   <!-- /.Col -->             

        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row" style="padding:2px;">
                        &nbsp<big><i class="fa fa-usd"></i><strong> Montos Inter Anuales de OT (Ordenes de Trabajos) </big> </strong>  
                    </div> <!-- /.row -->
                </div> <!-- Fin panel-heading -->
                <div class="panel-body">
                          
                    <div id="morris-line-inf2"></div>
                      <table id="tabla_inf2"
                       data-toggle="table"
                       data-cache = "false"
                       data-page-list=""

                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Periodo</th>
                        <th  data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel"  data-field="valor1" data-halign="center" data-align="right"><div id="lbl_anio1_inf2"> Año 1</div></th>
                        <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel"  data-field="valor2" data-halign="center" data-align="right"><div id="lbl_anio2_inf2"> Año 2</div></th>
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


  // Se ejecuta al cargar la pagina  
  $(document).ready(function() {

  });
        
      //funcion para ocultar el panel de filtrado
  $("#ocultar").hide();

  function mostrar(){
    let text="";
    if ($("#esconder").text() === 'Mas filtros..') {
      $("#ocultar").show();
      text = "Cerrar Filtros Extras";
    }else {
      $("#ocultar").hide();
      text = 'Mas filtros..'; // Abrir
    }
    $("#esconder").html(text);
  }
  


  //  Para elegir color en Google poner "elegir color"
  //  -------------------------

  // Grafico Cantidad InterAnual 
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
            "<b><div class='ran-azul'>" + $('#anio1_inf1').val() + ": </b> " + numberFormat(row.a) + "</div>" +
            "<b><div class='ran-verde'>" + $('#anio2_inf1').val() + ": </b> " + numberFormat(row.b) + "</div>";
    },    
    smooth: false,   // True = Para que una puntos con curva  no con recta
    gridTextSize     : 10
  });


  // Grafico Montos InterAnual 
  var grafico_inf2 = new Morris.Line({
    element          : 'morris-line-inf2',
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


  // Boton Consultar -> Trae los datos por Ajax
  function informe_por_tipo() {
        var action_name = "ot_interanualNvo";

        var formdata = {
              sucursal: $('#Ot_Sucursal').val(),
              tipo_ot: $('#tipo_ot').val(),
              tipo_obr: $('#tipo_obr').val(),
              vendedor: $('#vendedor').val(),
              medico: $('#medico').val(),
              anio1: $('#anio1_inf1').val(),
              anio2: $('#anio2_inf1').val()
        };
        var $table = $('#tabla_inf1'); 
        $.get('ot_proceso?action=' + action_name, formdata, "json")
                .done(function(data) {
                     // Titulos de las Tablas   
                     $('#lbl_anio1_inf1').html( $('#anio1_inf1').val());
                     $('#lbl_anio2_inf1').html( $('#anio2_inf1').val());
                     $('#lbl_anio1_inf2').html( $('#anio1_inf1').val());
                     $('#lbl_anio2_inf2').html( $('#anio2_inf1').val());
                     // Datos de las Tablas y Graficos   
                     grafico_inf1.setData(data.grafico_cant);
                     $table.bootstrapTable('load', data.tabla_cant);
                     grafico_inf2.setData(data.grafico_mto);
                     $tableinf2.bootstrapTable('load', data.tabla);
                })
                .fail(function(xhr,err) {                   
                    msgerror( xhr.responseText);
        });        
  }; // Fin Informe por tipo
  

  // Informe  por movimientos detallados (*** LAS 2 TABLAS LLAMAN AL MISMO ***)
  var $tableinf1 = $('#tabla_inf1');
  $tableinf1.on('all.bs.table', function (e, name, args) {
        //console.log(name, args);
        anio = 0;
        if (name == 'click-cell.bs.table' ) {
            //console.log(  args [2].periodo) // Valor de la columna
            if(  args [0] == 'valor1'){  // Nombre Columna                        	
            	anio = $('#anio1_inf1').val()
            };
            if(  args [0] == 'valor2'){  // Nombre Columna                        	
            	anio = $('#anio2_inf1').val()
            };
            if(anio > 0 && args [2].mes > 0 ) {
                mes = args [2].mes 
                if ( mes > 12 )  { //Todo el año
                  mes = 12
                  mesini = 1
                  var ultimoDia = new Date(anio, 12 , 0); 
                } else {
                  mesini = mes
                  var ultimoDia = new Date(anio, args [2].mes , 0);
                }

                //console.log ( ultimoDia.toDateString() )

                ruta = '../ot/index?sucursal=' + $('#Ot_Sucursal').val() + '&fecha=' + anio + '/'+ mesini  +'/'+'01&fechafin=' + anio +'/'+ mes  +'/'+ ultimoDia.getDate() + '&tipoot='  + $('#tipo_ot').val() 

                window.open(ruta, '', '_blanck');
            };
        }    
   });

  // Llamada Informe  por movimientos detallados
  var $tableinf2 = $('#tabla_inf2');
  $tableinf2.on('all.bs.table', function (e, name, args) {
        //console.log(name, args);
        anio = 0;
        if (name == 'click-cell.bs.table' ) {
            //console.log(  args [2].periodo) // Valor de la columna
            if(  args [0] == 'valor1'){  // Nombre Columna                          
              anio = $('#anio1_inf1').val()
            };
            if(  args [0] == 'valor2'){  // Nombre Columna                          
              anio = $('#anio2_inf1').val()
            };
            if(anio > 0 && args [2].mes > 0 ) {

                mes = args [2].mes 
                if ( mes > 12 )  { //Todo el año
                  mes = 12
                  mesini = 1
                  var ultimoDia = new Date(anio, 12 , 0); 
                } else {
                  mesini = mes
                  var ultimoDia = new Date(anio, args [2].mes , 0);
                }

                //console.log ( ultimoDia.toDateString() )

                ruta = '../ot/index?sucursal=' + $('#Ot_Sucursal').val() + '&fecha=' + anio + '/'+ mesini  +'/'+'01&fechafin=' + anio +'/'+ mes  +'/'+ ultimoDia.getDate() + '&tipoot='  + $('#tipo_ot').val() 

                window.open(ruta, '', '_blanck');

            };
        }    
   });

</script>

@endsection <!-- Fin scrip -->
