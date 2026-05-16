@extends('template.main')

@section('titulo', 'Remitos de Mercadería InterSucursales')

@section('contenido')
    
<form class="form-inline" role="form" >
    <div class="row">
    <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Remitos InterSucursales</h3>
            </div>
 
            <div class="panel-body">         
                <div class="form-group">
                    <label class="control-label">Tipo de Consulta:</label>
                    <select name="tipo_consulta" id="tipo_consulta" class="form-control">
                        <option value="C" >En Proceso de carga</option>
                        <option value="E" >Enviados</option>
                        <option value="F" >Procesados</option>
                    </select>
                </div>               
            </div>
 
        </div> <!-- Fin Panel Info -->

        <div id="toolbar">
            <label>&nbsp</label>
            <button type="button" class=" btn btn-success"
                id="form-search-btn" onclick="showIng_Remito( 0)">
                <i class="glyphicon glyphicon-plus"></i> Generar Nuevo Remito
            </button>
        </div>
        <!-- Panel De la Tabla -->
        <div class="panel panel-success">         
          <table id="mitabla"
           data-toggle="table"
           data-search="true"
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""
           class="table table-striped"
           data-toolbar="#toolbar"
           data-toolbar-align="right"
           >
          <thead>
          <tr>
            <th data-field="Lot_Numlot" data-align="center" data-formatter="opcionesFormatter"></th>
            <th data-field="Lot_Numlot" data-halign="center" data-align="center" data-sortable="true">Nro.</th>
            <th data-field="Fecha" data-halign="center" data-align="center" data-sortable="true">Fecha </th>
            <th  data-field="Suc_Origen" data-align="left"  data-sortable="true">Suc.Origen</th>
            <th  data-field="Suc_Destino" data-align="left"  data-sortable="true">Suc.Destino</th>
            <th data-field="Lot_Observ" > Observaciones</th>    
            <th  data-field="Lot_Monto" data-halign="right" data-align="right" data-sortable="true">Monto</th>
            <th  data-field="Lot_Cantidad" data-align="right" data-sortable="true">Cantidad</th>
          </tr>
          </thead>
        </table>
        </div> <!-- fin Panel Tabla -->
   </div> <!-- fin de col -->
  </div> <!-- fin de row -->

</form> 

@endsection()

@section('scrip')

<script>
    
    /**
     * Da el formato requerido al valor presente en la columna de opciones.
     * @param value     valor que contiene la columna.
     */

    function opcionesFormatter(value,row,index) {

        var Id = value;

        var Tipo = "'"  + row.Tipo + "'"
        var archivo = "'"  + row.archivo + "'"


        if($("#tipo_consulta").val() != 'C') {    
           botones = '<button type="button" class="btn btn-primary btn-xs"'+
                    'title="Consultar Remito" onclick="showIng_Remito('+ Id +')">'+
                    '<i class="fa fa-info-circle"></i>'+
                '</button>'+ '&nbsp;';
           botones = botones + '<button type="button" class="btn   btn-danger   btn-xs"'+
                  'title="Imprimir PDF" onclick="imprimePDF('+ Id  +')">'+
                    '<i class="fa fa-file-pdf-o"></i>'+
                '</button>' + '&nbsp;'
        }else{
           botones = '<button type="button" class="btn btn-primary btn-xs"'+
                        'title="Continuar Carga" onclick="showIng_Remito('+ Id +')">'+
                    '<i class="fa fa-info-circle"></i>'+
                '</button>'+ '&nbsp;';

        }        

        return botones; 

    }

    function showIng_Remito(  id_lote) {
          
        // Paso a la pantalla detalle 

        // Paso a la pantalla detalle de la compra
       document.location = 'carga_remito?id_lote='+id_lote;

    }
      

    function imprimePDF(  id_lote) {
          

      // Paso a imprimir
//      document.location = 'genera_remito?id='+id_lote;
   
      window.open( '../remitos/remito_' + id_lote  + '.pdf'   , '', '_blanck' )


    }
   
    $(document).ready(function(){
       // Se carga al iniciar
       consultar();
    });      

    // Cambio el Combo tipo de Consulta 
    $(function () {            
        $('select[name="tipo_consulta"]').change(function () {
           consultar();
        });    
    });


    function consultar()  {

        var $table = $('#mitabla'); 
        $tipo_consulta = $("#tipo_consulta").val();
        $.ajax({
            dataType: "json",
            data: { tipo_consulta: $tipo_consulta},
            url:   'buscar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
                                   
</script>

@endsection()