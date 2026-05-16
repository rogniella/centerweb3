@extends('template.informes')
@section('titulo','Estadisticas Ingresos/Egresos por Códigos')
   
@section('contenido')

<?php 
    $anio = date("Y");  
    $mes = date("m");  
?>    

<!-- Select2  Ayuda en  https://select2.org/ -->
<link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{ asset('plugins/select2/select2-bootstrap.min.css')}}"> 

<form class="form-inline" role="form" >


    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-info alert-dismissable">
                <div data-dismiss="alert" aria-hidden="true"></div>
                <i class="fa fa-info-circle"></i> <strong>Estadísticas Mensuales de Ingresos/Egresos</strong>
            </div>
        </div>
    </div>    <!-- /.row -->
    

    <!-- Primera Fila de Informes -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading"  >
                    <div class="row" style="padding:4px;">
                        &nbsp<big><i class="fa fa-line-chart"></i><strong> Diferencial por Movimientos </big> </strong> 
                    </div>
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Sucursal:</span>
                          <select  name="sucursal_dif1" id="sucursal_dif1"  class="form-control">
                                @include('common.combo_sucursal')
                          </select>
                        </div>
                    </div> <!-- /.row -->                
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Año:</span>
                          <input type="number" class="form-control" id="anio1_dif1" value="<?= date("Y") ?>" maxlength="4"
                     required/>
                        </div>
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Expresado en:</span>
                          <select  name="moneda_dif1" id="moneda_dif1"  class="form-control">
                            <option value="1" >Pesos</option>
                            <option value="2" >Dolar Blue</option>
                         </select>
                        </div>  
                    </div>                
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Tipo Informe:</span>
                          <select id="tipo_dif1" name="tipo_dif1" class="form-control">                            
                           <?PHP  
                              $INF_TIPO =2;
                              $INF_ID = 0   ?>
                              @include('common.combo_informe')
                          </select>
                        </div>  
                    </div>                
                    <div class="row" style="padding:4px;">
                      <select name="codigosdif" id="codigosdif" class="form-control select2" multiple="multiple" data-placeholder="Selecione los Códigos"
                         style="width: 100%;">
                          <?PHP
                            $cod_codmov = "";
                          ?>    
                           @include('common.combo_codmov')  
                      </select>
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                      <select name="codigosdif2" id="codigosdif2" class="form-control select2" multiple="multiple" data-placeholder="Selecione los Códigos"
                         style="width: 100%;">
                          <?PHP $cod_codmov = "";                          ?>      
                           @include('common.combo_codmov')
                      </select>
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                        <button type="button" id="btnconsulta" onClick="informe_por_tipo_dif()" class="btn btn-primary pull-right">Consultar</button>
                    </div> <!-- /.row -->
                </div> <!-- Fin panel-heading -->

                <div class="panel-body" >
                      <div id="morris-line-dif1"></div> 
                      
                      <table id="tabla_dif1"
                       data-toggle="table"
                       data-cache = "false"
                       data-page-list=""
                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Periodo</th>
                        <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor1" data-halign="center" data-align="right">Ingresos</th>
                        <th data-title-tooltip="Click para Ver Detalle" data-formatter="fotmatoColSel" data-field="valor2" data-halign="center" data-align="right">Gastos</th>
                        <th data-field="dif" data-halign="center" data-align="center">Diferencia</th>
                        <th data-field="difmes" data-halign="center" data-align="center">% Ahorro</th>
                      </tr>
                      </thead>
                     </table>
                </div>  <!-- /.panel-body -->
            </div>    <!-- /.panel -->
        </div>   <!-- /.Col -->             

        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row" style="padding:3px;">
                        &nbsp<big><i class="fa fa-line-chart"></i><strong> Inter Anual por Movimientos </big> </strong>  
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Sucursal:</span>
                          <select  name="sucursal_inf1" id="sucursal_inf1"  class="form-control">
                                @include('common.combo_sucursal')
                          </select>
                        </div>
                    </div> <!-- /.row -->                
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Año 1:</span>
                          <input type="number" class="form-control" id="anio1_inf1" value="<?= date("Y") - 1 ?>" maxlength="4"
                     required/>
                        </div>
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Año 2:</span>
                          <input type="number" class="form-control" id="anio2_inf1" value="<?= date("Y")  ?>" maxlength="4"
                     required/>
                        </div>
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Expresado en:</span>
                          <select  name="moneda" id="moneda"  class="form-control">
                            <option value="1" >Pesos</option>
                            <option value="2" >Dolar Blue</option>
                         </select>
                        </div>  
                    </div>                

                    <div class="row" style="padding:4px;">
                        <div class="input-group">
                          <span class="input-group-addon">Tipo Informe:</span>
                          <select id="tipo_inf1" name="tipo_inf1" class="form-control">                            
                           <?PHP  
                              $INF_TIPO =1;     
                              $INF_ID =19; // POr defecto Personalizado
                            ?>
                           @include('common.combo_informe')

                          </select>
                        </div>  
                    </div> <!-- /.row -->
                    <div class="row" style="padding:3px;">
                      <select name="codigos" id="codigos" class="form-control select2" multiple="multiple" data-placeholder="Selecione los Códigos"
                         style="width: 100%;">
                          <?PHP
                            $cod_codmov = "";
                          ?>      
                          @include('common.combo_codmov')

                      </select>
                    </div> <!-- /.row -->
                    <div class="row" style="padding:4px;">
                        <button type="button" id="btnconsulta" onClick="informe_por_tipo()" class="btn btn-primary pull-right">Consultar</button>
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

    </div>   <!-- /.Row -->
    
  
                
</form> 

@endsection <!-- Fin Contenido -->

@section('scrip')


<!-- Select2 -->
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>

<script>



  // Se ejecuta al cargar la pagina  
  $(document).ready(function() {
         //Initialize Select2 Elements 
         $('.select2').select2({
             language: "es",
             placeholder: 'Select una optioDDDDDDDn'
         });
         cargaCodigosDif();
         cargaCodigos();
  });


    // Cuando elige el tipo de Informe Visualiza los Codigos
    $("#tipo_dif1").on("change", cargaCodigosDif);

     function cargaCodigosDif(){
        // Carga combo Codigos de Movimientos segun Informe seleccionado
        // global: false,   Hace que no despliegue msg de Procesando para este llamado
        $informe_sel = $("#tipo_dif1").val();
        $.ajax({
                global: false,
                dataType: "json",
            data: {"informe": $informe_sel},
            url:   'combo_codmov_informe',
            type:  'get',
            success: function(respuesta){
                    //lo que se si el destino devuelve algo
                    $("#codigosdif").html(respuesta.html);
                    $("#codigosdif2").html(respuesta.html2);
                    $('.select2').select2({theme: "bootstrap",language: "es"});
              //      $('.select2').select2({theme: "classic"});
            },
            error:	function(xhr,err){ 
                     msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
            }
        });                   
    }     

    // Si elije algun codigo, el tipo de informe , pasa a ser Personalizado
    $("#codigos").on("change",cambiotpoInf);
     function cambiotpoInf(){
        $("#tipo_inf1").val(19); // 19 = Personalizado
    }

    // Cuando elige el tipo de Informe Visualiza los Codigos
    $("#tipo_inf1").on("change", cargaCodigos);
    
     function cargaCodigos(){
        // Carga combo Codigos de Movimientos segun Informe seleccionado
        // global: false,   Hace que no despliegue msg de Procesando para este llamado
        $informe_sel = $("#tipo_inf1").val();
        $.ajax({
                global: false,
                dataType: "json",
            data: {"informe": $informe_sel},
            url:   'combo_codmov_informe',
            type:  'get',
            success: function(respuesta){
                    //lo que se si el destino devuelve algo
                    $("#codigos").html(respuesta.html);
//                    $('.select2').select2({theme: "classic",language: "es"});
                    $('.select2').select2({theme: "bootstrap",language: "es"});
            },
            error:	function(xhr,err){ 
                     msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
            }
        });                   
    }     
    
    
        
    //  Para elegir color en Google poner "elegir color"
    //  -------------------------
    
    var $tableinf2 = $('#tabla_inf2');
    $tableinf2.on('all.bs.table', function (e, name, args) {
        //console.log(name, args);
        if (name == 'click-cell.bs.table' ) {
            console.log(  args)
      //    echo '<a href="vistas/modulos/imprimir-reporte.php?reporte=reporte&fechaInicial='.$_GET["fechaInicial"].'&fechaFinal='.$_GET["fechaFinal"].'">
            console.log(  args [0]) // Nombre Columna
            console.log(  args [2].periodo) // Valor de la columna
            // FALTA IMPLEMENTAR DETALLE DE LOS MOV POR BARRA
      //      ruta = 'ventas_detalle.php?fechaini=' + args [0] + '&fechafin=' + args [2].periodo
      //      window.open(ruta, '', 'width=330,height=252,scrollbars=NO,statusbar=NO,left=800,top=600');
            
        }    
    });
        

  //xLabelFormat: function(d) {
  //    return "Mes: " + d.src.y  
  //},
  //yLabelFormat: function(d) {
  //    return "Mes: " + $('#anio1_inf1').val()
  //},


// Diferecias por Movimientos
// ===========================
  var grafico_dif1 = new Morris.Line({
    element          : 'morris-line-dif1',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a', 'b'],
    labels           : ['Ingresos','Gastos'],
    lineColors: ['#1E25B2','red'], // Azul y Rojo
    lineWidth        : 2,
    parseTime : false,
    preUnits    : '$',
    smooth: false,
    gridTextSize     : 10
  });

  function informe_por_tipo_dif() {
        var action_name = "informe_portipo_dif";
        
        var param_codigos = $('#codigosdif').val();                
        var param_codigos2 = $('#codigosdif2').val();                

        var formdata = {
              sucursal: $('#sucursal_dif1').val(),
              moneda: $('#moneda_dif1').val(),
            	tipo_inf: $('#tipo_dif1').val(),
                codigos: param_codigos,
                codigos2: param_codigos2,
            	anio1: $('#anio1_dif1').val()
        };
        var $table = $('#tabla_dif1'); 
        $.get('codmov_proceso?action=' + action_name, formdata, "json")
                .done(function(data) {
                     grafico_dif1.setData(data.grafico);                             
                     $table.bootstrapTable('load', data.tabla);
                })
                .fail(function(xhr,err) {                   
                    msgerror( xhr.responseText);
        });
  }; // Fin Informe por tipo Dif


  //InterAnual por Movimientos   
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
        if ($('#tipo_inf1').val() != 19 ) {
            var param_codigos = "PorTipoInf";
        }else{
            var param_codigos = $('#codigos').val(); // Seleccion personalizada de codigos       
        }
        if (param_codigos == '') {
          msgerror( 'Primero Seleccione los Códigos de Movimientos');
          return
        }  

        var formdata = {
              sucursal: $('#sucursal_inf1').val(),
              moneda: $('#moneda').val(),
            	tipo_inf: $('#tipo_inf1').val(),
            	codigos: param_codigos,
            	anio1: $('#anio1_inf1').val(),
            	anio2: $('#anio2_inf1').val()
        }
        var $table = $('#tabla_inf1'); 
        $.get('codmov_proceso?action=' + action_name, formdata, "json")
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
  
      // Informe  por movimientos detallado
    var $tabledif1 = $('#tabla_dif1');
    $tabledif1.on('all.bs.table', function (e, name, args) {
        //console.log(name, args);
        if (name == 'click-cell.bs.table' ) {
            anio = $('#anio1_dif1').val()
            var param_codigos = '';
            if(  args [0] == 'valor1'){  // Nombre Columna                        	
                 param_codigos = $('#codigosdif').val();        
            }
            if(  args [0] == 'valor2'){  // Nombre Columna                        	
                 param_codigos = $('#codigosdif2').val();        
            }
            if(  param_codigos != ''){                         	
                ruta = 'infmov_detalle?sucursal=' + $('#sucursal_dif1').val() +'&mes=' + args [2].mes + '&anio=' + anio + '&tipoinf=19' + '&codigos=' +  param_codigos
                window.open(ruta, '', '_blanck');
            }
        }    
    });

  // Informe  por movimientos detallados
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
                if ($('#tipo_inf1').val() != 19 ) {
                    var param_codigos = "PorTipoInf";
                }else{
                    var param_codigos = $('#codigos').val();        
                }
                ruta = 'infmov_detalle?sucursal=' + $('#sucursal_inf1').val() +'&mes=' + args [2].mes + '&anio=' + anio + '&tipoinf=' + $('#tipo_inf1').val() + '&codigos=' +  param_codigos
                window.open(ruta, '', '_blanck');
            };
        }    
   });
   
</script>

@endsection <!-- Fin scrip -->
