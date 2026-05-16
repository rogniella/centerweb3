@extends('template.consultas')
@section('titulo','Consulta de Clientes')
   
@section('contenido')

  @include('clientes.consulta')
  @include('common.modal_consulta')

@endsection <!-- Fin Contenido -->


@section('scrip')

<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>
    
  $(document).ready(function(){
     
  });      
  

  // Todos los Eventos de la Tabla       
  var $table = $('#vtatabla'); // Tabla De Compras realizas

  $table.on('all.bs.table', function (e, name, args) {

    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla

      if ( args [0] == 'idvta'){  // Nombre Columna                          
       // Busco los datos de la OT o Comprobante y despliega pantall Modal
       consulta_comprobante(args [2].tipo, args [2].idvta, args [2].sucursal)
      }; // Fin Clik Id OT
    
    } // Clik de La tabla    

  }); // Fin Todos los Eventos de la Tabla


  // Todos los Eventos de la Tabla       
  var $tableOt = $('#ottabla'); // Tabla De Compras realizas
  $tableOt.on('all.bs.table', function (e, name, args) {

    if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla

      if ( args [0] == 'id'){  // Nombre Columna                          
       // Busco los datos de la OT o Comprobante y despliega pantall Modal
      // consulta_comprobante(args [2].tipoOT, args [2].id, args [2].sucursal)
       consulta_comprobante('ot_idweb', args [2].idweb, 0)

      }; // Fin Clik Id OT
    
    } // Clik de La tabla    

  }); // Fin Todos los Eventos de la Tabla


</script>

@endsection   <!-- Fin scrip -->
