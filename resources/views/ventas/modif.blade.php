<!-- Archivo Blade modificado: create-ori.blade.php -->

<!-- Asegurate de incluir en tu layout o cabecera global -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- Campo Cliente con Select2 -->
<div class="form-group">
  <label for="cliente_id">Cliente</label>
  <div class="input-group">
    <select id="cliente_id" name="cliente_id" class="form-control"></select>
    <span class="input-group-btn">
      <button class="btn btn-default" id="btnBuscarCliente" type="button">Buscar</button>
    </span>
  </div>
</div>

<!-- Campo Producto con Select2 -->
<div class="form-group">
  <label for="producto_id">Producto</label>
  <div class="input-group">
    <select id="producto_id" name="producto_id" class="form-control"></select>
    <span class="input-group-btn">
      <button class="btn btn-default" id="btnBuscarProducto" type="button">Buscar</button>
    </span>
  </div>
</div>

<!-- Scripts para inicializar Select2 -->
<script>
  function inicializarSelect2(id, url, botonId) {
    let $el = $('#' + id);

    $el.select2({
      language: {
        inputTooShort: () => 'Escriba al menos 2 caracteres...',
        noResults: () => 'No se encontraron resultados.',
        searching: () => 'Buscando...'
      },
      placeholder: 'Escriba para buscar...',
      minimumInputLength: 2,
      ajax: {
        url: url,
        dataType: 'json',
        delay: 250,
        data: params => ({ q: params.term }),
        processResults: data => ({
          results: data.map(item => ({
            id: item.id,
            text: item.nombre
          }))
        }),
        cache: true
      }
    });

    $('#' + botonId).on('click', function () {
      const input = $el.data('select2').$container.find('input.select2-search__field');
      const val = input.val();
      if (val.length >= 2) {
        $el.select2('open');
      } else {
        alert("Escriba al menos 2 caracteres para buscar.");
      }
    });

    $el.on('keypress', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        const input = $el.data('select2').$container.find('input.select2-search__field');
        if (input.val().length >= 2) {
          $el.select2('open');
        }
      }
    });
  }

  $(document).ready(function () {
    inicializarSelect2('cliente_id', '/api/clientes', 'btnBuscarCliente');
    inicializarSelect2('producto_id', '/api/productos', 'btnBuscarProducto');
  });
</script>
