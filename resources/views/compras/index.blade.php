@extends('template.main')
@section('titulo', 'Compras')
@section('contenido')
    
<form class="form-inline" role="form" >
    <div class="row">
    <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Compras en proceso de carga</h3>
            </div>

           <div class="panel-body">         
                <div class="form-group">
                    <label class="control-label">Tipo de Consulta:</label>
                    <select name="tipo_consulta" id="tipo_consulta" class="form-control">
                        <option value="C" >En Proceso de carga</option>
                        <option value="F" >Procesados</option>
                    </select>
                </div>               
            </div>
        </div> <!-- Fin Panel Info -->

        <div id="toolbar">
            <label>&nbsp</label>
            <button type="button" class=" btn btn-success"
                id="form-search-btn" onclick="showIng_Compra2( 0)">
                <i class="glyphicon glyphicon-plus"></i> Generar Nueva Compra
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
            <th data-field="Fecha" data-halign="center" data-align="center" data-sortable="true">Fecha </th>
            <th  data-field="Lot_Sucursal" data-align="center"  data-sortable="true">Sucursal</th>
            <th  data-field="Prov_NomFant" data-align="left"  data-sortable="true">Proveedor</th>
            <th data-field="Lot_Observ" > Observaciones</th>    
            <th data-field="Lot_Numlot" data-halign="center" data-align="center" data-sortable="true">Nro.Compra</th>
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
    function opcionesFormatter(value) {
        var Id = value;

        if($("#tipo_consulta").val() != 'C') {    
           botones = '<button type="button" class="btn btn-primary btn-xs"'+
                    'title="Consultar " onclick="showIng_Compra2('+ Id +')">'+
                    '<i class="fa fa-info-circle"></i>'+
                '</button>'+ '&nbsp;';
           botones = botones + '<button type="button" class="btn   btn-danger   btn-xs"'+
                  'title="Imprimir PDF" onclick="imprimePDF('+ Id  +')">'+
                    '<i class="fa fa-file-pdf-o"></i>'+
                '</button>' + '&nbsp;'
        }else{
                return '<button type="button" class="btn btn-primary btn-xs"'+
                        'title="Continuar Carga" onclick="showIng_Compra2('+ Id +')">'+
                    '<i class="glyphicon glyphicon-pencil"></i>'+
                '</button>'; 
        }
        return botones;        
    }

    function showIng_Compra2( id_lote) {
          
       // Paso a la pantalla detalle de la compra
       document.location = 'create?id_lote='+id_lote;

    }
   


    function imprimePDF(  id_lote) {
          
      window.open( '../remitos/compra_' + id_lote  + '.pdf'   , '', '_blanck' )

    }

    $(document).ready(function(){
       consultar();
    });      

    // Cambio el Combo tipo de Consulta 
    $(function () {            
        $('select[name="tipo_consulta"]').change(function () {
           consultar();
        });    
    });

    function consultar()  {
        // LLama a 2da pagina con la logica de la busqueda
        // ------------------------------------------------      
        // Tomo los datos de entrada
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