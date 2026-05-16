@extends('template.main')

@section('titulo', 'Proceso Archivos de Tarjetas')

@section('contenido')

<form enctype="multipart/form-data" method="post" action="{{ route('tarjetas.upload') }}">
  @csrf
  <div class="row">
    <div class="col-sm-8 col-md-6">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h3 class="panel-title">Carga de Archivos de Tarjetas</h3>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label class="control-label">Seleccionar Archivos (Txt):</label>
            <input type="file" id="archivos" name="archivos[]" class="form-control" multiple accept=".txt" required/>
          </div>

          <div class="form-group" id="lista-archivos" style="display:none;">
            <label class="control-label">Archivos seleccionados:</label>
            <table class="table table-striped table-condensed">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Tamaño</th>
                </tr>
              </thead>
              <tbody id="lista-body"></tbody>
            </table>
          </div>
        </div>
        <div class="panel-footer">
          <button type="submit" id="btn-subir" class="btn btn-success pull-right" disabled>
            <i class="glyphicon glyphicon-upload"></i> Procesar Archivos
          </button>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection

@section('scrip')
<script>

function formatearTamano(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

$(document).ready(function () {
    $('#archivos').change(function () {
        var files = this.files;
        var tbody = $('#lista-body');
        tbody.empty();

        if (files.length > 0) {
            $.each(files, function (i, file) {
                var fila = '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + file.name + '</td>' +
                    '<td>' + formatearTamano(file.size) + '</td>' +
                    '</tr>';
                tbody.append(fila);
            });

            $('#lista-archivos').show();
            $('#btn-subir').prop('disabled', false);
        } else {
            $('#lista-archivos').hide();
            $('#btn-subir').prop('disabled', true);
        }
    });
});

</script>
@endsection
