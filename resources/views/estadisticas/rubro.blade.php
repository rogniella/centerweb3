@extends('template.informes')
@section('titulo','Estadisticas Por Rubro')
   
@section('contenido')

<?php 
    $anio = date("Y");  
    $mes = date("m");  
    if ($_GET) {
        $sucursal = $_GET["sucursal"];
        $INF_ID = $_GET["tipoinf"];
        $id_producto = $_GET["id_producto"];
        $desc_producto = $_GET["desc_producto"];
    }else{
        $sucursal = "";
        $INF_ID = "REC";
        $id_producto = "";
        $desc_producto = "";
    }    

?>    
       
<form autocomplete="off" class="form-inline" role="form" >

    <div class="panel panel-info">
        <div class="panel-heading">      
          <div class="row">
            <div data-dismiss="alert" aria-hidden="true"></div>
            &nbsp<big><i class="fa fa-info-circle"></i> <strong>Estadísticas Mensuales por Rubros</strong></big>
          </div>    <!-- /.row -->
        </div> <!-- Fin panel-heading -->
        <div  class="panel-body">
            <div class="row" style="padding:2px;">

                        <div class="input-group">
                          <span class="input-group-addon">Sucursal:</span>
                <select name="sucursal_inf1" id="sucursal_inf1" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                        </div>

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Año 1:</span>
                          <input type="number" class="form-control" id="anio1_inf1" value="<?= date("Y") - 1 ?>" maxlength="4"
                     required/>
                        </div>
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Año 2:</span>
                          <input type="number" class="form-control" id="anio2_inf1" value="<?= date("Y")  ?>" maxlength="4"
                     required/>
                        </div>

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Rubro:</span>
                          <select name="tipo_inf1" id="tipo_inf1" class="form-control">
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == $INF_ID ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                        </div>  

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Operación:</span>
                          <select id="operacion_inf1" name="operacion_inf1" class="form-control"> 
                            <option value= 'V' selected> Ventas</option>
                            <option value= 'C'> Compras</option>
                          </select>
                        </div>  

                        <div class="input-group" style="padding:2px;">
                            <a id="esconder" onclick="mostrar()">Mas filtros..</a>
                        </div>  

                    </div> <!-- Fin div row -->

                    <div id="ocultar" class="row" style="padding:1px;background:white;">
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Descripción Articulo:</span>
                            <input class="form-control" type="text" name="" value="<?= $desc_producto ; ?>" id="desc_producto" placeholder="Buscar Descripción Articulo">
                        </div>
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Articulo:</span>
                            <input class="form-control" type="text" value="<?= $id_producto ; ?>" name="" id="id_producto" placeholder="Buscar Articulo">
                        </div>
                        <label id="descrip_producto"> </label>


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
                        &nbsp<big><i class="fa fa-line-chart"></i><strong> Cantidades Inter Anuales por Rubro </big> </strong>  
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
                        &nbsp<big><i class="fa fa-usd"></i><strong> Montos Inter Anuales por Rubro </big> </strong>  
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

  <?php 
      if ($_GET) {
        echo "informe_por_tipo()";
      }
  ?>    
     

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


        // Busqueda Automatica de Productos
        $('#id_producto').typeahead({
            items: 15,
            minLength: 2,
            highlight: true,
            source: function(query, process) {
              var familia = $('#tipo_inf1').val();
              $.ajax({
                  global: false,
                  dataType: "json",
                  data: {},
                  url:   '../productos/buscaproducto?terms='+query+'&familia='+familia,
                  type:  'get',
                  success: function(data){
                     return process(data);
                  },
                  error:  function(xhr,err){ 
                    msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
                  }
              });
            },
            // Al seleccionar.
            afterSelect: function(item) {
                $('#id_producto').val(item.id);
                //$("#descrip_producto").attr('disabled',true);
                //$('#descrip_producto').val(item.descripcion);
                $("#descrip_producto").html(item.descripcion);

            }
        }); //Fin Busqueda Producto







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
    smooth: false,
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
        var action_name = "informe_rubro_cant";
        var formdata = {
              sucursal: $('#sucursal_inf1').val(),
            	tipo_inf: $('#tipo_inf1').val(),
              id_producto: $('#id_producto').val(),
              desc_producto: $('#desc_producto').val(),
              operacion: $('#operacion_inf1').val(),
            	anio1: $('#anio1_inf1').val(),
            	anio2: $('#anio2_inf1').val()
        }
        var $table = $('#tabla_inf1'); 
        $.get('rubro_proceso?action=' + action_name, formdata, "json")
                .done(function(data) {
                     // Titulos de las Tablas   
                     $('#lbl_anio1_inf1').html( $('#anio1_inf1').val());
                     $('#lbl_anio2_inf1').html( $('#anio2_inf1').val());
                     $('#lbl_anio1_inf2').html( $('#anio1_inf1').val());
                     $('#lbl_anio2_inf2').html( $('#anio2_inf1').val());
                     // Datos de las Tablas y Graficos   
                     grafico_inf1.setData(data.grafico_cant);
                     $table.bootstrapTable('load', data.tabla_cant);
                     grafico_inf2.setData(data.grafico);
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
                ruta = '../productos/movimientos?cod_cero=S&sucursal=' + $('#sucursal_inf1').val() + '&id_producto=' + $('#id_producto').val() +  '&desc_producto=' + $('#desc_producto').val() + '&mes=' + args [2].mes + '&anio=' + anio + '&familia=' + $('#tipo_inf1').val() + '&operacion=' +  $('#operacion_inf1').val() 
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
                ruta = '../productos/movimientos?cod_cero=S&sucursal=' + $('#sucursal_inf1').val() + '&id_producto=' + $('#id_producto').val() +  '&desc_producto=' + $('#desc_producto').val() + '&mes=' + args [2].mes + '&anio=' + anio + '&familia=' + $('#tipo_inf1').val() + '&operacion=' +  $('#operacion_inf1').val() 
                window.open(ruta, '', '_blanck');
            };
        }    
   });

</script>
  
@endsection <!-- Fin scrip -->
