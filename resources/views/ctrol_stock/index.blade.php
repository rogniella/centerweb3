@extends('template.main')

@section('titulo', 'Control Stock')

@section('contenido')
    
<form class="form-inline" role="form" >
    <div class="row">
    <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Control de Stock por Sucursal</h3>
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
                <i class="glyphicon glyphicon-plus"></i> Generar Nuevo 
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
            <th data-field="lote" data-align="center" data-formatter="opcionesFormatter"></th>
            <th data-field="Fecha" data-halign="center" data-align="center" data-sortable="true">Fecha </th>
            <th  data-field="Lot_Sucursal" data-align="center"  data-sortable="true">Sucursal</th>
            <th  data-field="Lot_Familia" data-align="left"  data-sortable="true">Familia</th>
            <th data-field="Lot_Filtro" >Filtro</th>    
            <th data-field="Lot_Observ" >Observaciones</th>    
            <th  data-field="Lot_Cant_ing" data-align="right" data-sortable="true">Cantidad Ingresada</th>
            <th  data-field="Lot_Cant_bd" data-align="right" data-sortable="true">En BaseDatos</th>
            <th  data-field="Lot_Cantidad" data-align="right" data-sortable="true">Ajuste</th>
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

        var opciones =  '<button type="button" class="btn btn-primary btn-xs"'+
                    'title="Continuar Carga" onclick="showIng_Compra2('+ Id +')">'+
                    '<i class="glyphicon glyphicon-pencil"></i>'+
                '</button>'; 

      if ( $("#tipo_consulta").val() != 'F') {          
      // Opcion Eliminar  Solo si esta conectado y es ADM -->
      @if(Auth::user())
        @if(Auth::user()->perfil_id == 'ADM'  )
          opciones = opciones + '&nbsp;'+
              '<button type="button" class="btn btn-danger btn-xs"'+
                  'title="Eliminar Lote" onclick="deleteReg(\''+ Id +'\')">'+
                  '<i class="glyphicon glyphicon-trash" aria-hidden="true"></i>'+
              '</button>';
        @endif
      @endif
      }
      return opciones;

    }

    function showIng_Compra2( id_lote) {
          
       // Paso a la pantalla detalle 
       console.log( $("#tipo_consulta").val() )
       if ($("#tipo_consulta").val() == 'F' ) {
        document.location = 'consulta?id_lote='+id_lote;
       }else{
        document.location = 'index_partes?id_lote='+id_lote;
       }

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

    function deleteReg(id) {

        Swal.fire({
                  title: 'Eliminar Lote de Ajuste ? <br>' ,
                  text: "Confirmar!",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonText: 'Cancelar',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Si, Eliminar!'
        }).then((result) => {
         if (result.value) {
            $.ajax({
                global: false,
                dataType: "json",
                data: { idcompra: id,
                        familia: 'BAJA',
                        observ: 'Se dio de Baja'
                },
                url:   'ActualizaDatosLote',
                type:  'get'
                }).done(function(data) {
                    consultar(); 
            });

        }
        })  // Confirmacion      
    }

    function consultar()  {
        // LLama a 2da pagina con la logica de la busqueda
        // ------------------------------------------------      
        // Tomo los datos de entrada
        var $table = $('#mitabla'); 
        
        $tipo_consulta = $("#tipo_consulta").val();

        $.ajax({
            dataType: "json",
            data: { tipo_consulta: $tipo_consulta , tipo_lote: 'T',numlot: 0} ,
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