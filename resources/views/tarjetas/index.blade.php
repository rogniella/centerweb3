@extends('template.main')

@section('titulo', 'Proceso Archivos de Tarjetas')

@section('contenido')

<style>
    .panel-success > .panel-heading { display: block; }

    #drop-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius-lg);
        padding: 50px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color .3s, background .3s, transform .2s;
        background: var(--bg-soft);
        min-height: 160px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    #drop-zone:hover,
    #drop-zone.dragover {
        border-color: var(--primary);
        background: rgba(37,99,235,.04);
        transform: scale(1.01);
    }
    #drop-zone .icono {
        font-size: 52px;
        color: var(--text-muted);
        margin-bottom: 12px;
        transition: color .3s, transform .3s;
    }
    #drop-zone.dragover .icono {
        color: var(--primary);
        transform: translateY(-4px);
    }
    #drop-zone p { margin: 4px 0; color: var(--text-secondary); font-size: 14px; }
    #drop-zone .btn-seleccionar { margin-top: 12px; }

    #lista-archivos .table { margin-bottom: 0; }
    #lista-archivos .table thead th {
        background: var(--bg-soft);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-bottom: 2px solid var(--border);
    }
    #lista-archivos .table tbody td {
        font-size: 13px;
        border-color: var(--border-light);
    }
</style>

<form enctype="multipart/form-data" method="post" action="{{ route('tarjetas.upload') }}">
  @csrf
  <div class="row">
    <div class="col-sm-8 col-md-6">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h3 class="panel-title">Carga de Archivos de Tarjetas</h3>
        </div>
        <div class="panel-body">
          <input type="file" id="archivos" name="archivos[]" multiple accept=".txt" style="display:none"/>

          <div class="form-group">
            <div id="drop-zone">
              <div class="icono">📁</div>
              <p><strong>Arrastra los archivos aquí</strong></p>
              <p>o</p>
              <button type="button" class="btn btn-default btn-seleccionar" id="btn-seleccionar">
                <i class="glyphicon glyphicon-folder-open"></i> Seleccionar Archivos
              </button>
              <p class="text-muted" style="font-size:12px;margin-top:8px;">Archivos .txt — sin límite de cantidad</p>
            </div>
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

// Hacemos de esta manera porque si es solo con el input tiene como limite de cantidad de archivos a subir, con el drag and drop no hay limite 
// Cambiar en php.ini → max_file_uploads = 200
function actualizarLista() {
    var tbody = $('#lista-body');
    tbody.empty();

    if (filesArray.length > 0) {
        $.each(filesArray, function (i, file) {
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
}

var filesArray = [];

$(document).ready(function () {
    var dropZone = document.getElementById('drop-zone');

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');

        var files = e.dataTransfer.files;
        for (var i = 0; i < files.length; i++) {
            filesArray.push(files[i]);
        }
        actualizarLista();
    });

    $('#btn-seleccionar').click(function () {
        $('#archivos').click();
    });

    $('#archivos').change(function () {
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            filesArray.push(files[i]);
        }
        this.value = '';
        actualizarLista();
    });

    $('form').on('submit', function (e) {
        if (filesArray.length === 0) {
            e.preventDefault();
            return;
        }

        e.preventDefault();

        var formData = new FormData();
        formData.append('_token', $('[name="_token"]').val());

        for (var i = 0; i < filesArray.length; i++) {
            formData.append('archivos[]', filesArray[i]);
        }

        $('#btn-subir').prop('disabled', true).text('Subiendo...');

        fetch(this.action, {
            method: 'POST',
            body: formData,
            redirect: 'manual'
        }).then(function (response) {
            var location = response.headers.get('Location') || '{{ route('tarjetas.carga') }}';
            window.location.href = location;
        }).catch(function () {
            $('#btn-subir').prop('disabled', false).text('Procesar Archivos');
            alert('Error al subir los archivos. Intente nuevamente.');
        });
    });
});

</script>
@endsection
