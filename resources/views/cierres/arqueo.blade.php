@extends('template.informes')
@section('titulo','Arqueo de Caja')

@section('contenido')

<form class="form-inline" role="form">

  <div class="row">
   <div class="col-sm-12">
        <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title">Arqueo de Caja</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <input type="text" class="form-control" value="{{ $sucursalDescripcion }}" readonly style="width:300px;">
                    <input type="hidden" id="sucursal" value="{{ $sucursalCodigo }}">
                </div>

            </div>
        </div>
     </div>
  </div>

  <div id="panel-monedas" style="display:none;">

    <div class="row" id="fila-monedas"></div>

    <div class="row" style="margin-top:15px;">
      <div class="col-sm-12">
        <button type="button" class="btn btn-success" id="btn-registrar" onclick="registrar()" disabled>Registrar</button>
        <span id="msg-input" style="margin-left:10px;"></span>
      </div>
    </div>

    <div id="panel-resultados" style="display:none; margin-top:20px;">
      <div class="panel panel-success">
        <div class="panel-heading"><h3 class="panel-title">Resultado del Arqueo</h3></div>
        <div class="panel-body">
          <div class="row" id="fila-resultados"></div>

          <div class="row">
            <div class="col-sm-12">
              <div class="form-group" id="grupo-motivo" style="display:none;">
                <label class="control-label">Motivo del Ajuste (obligatorio):</label>
                <input type="text" id="motivo-ajuste" class="form-control" style="width:400px;" maxlength="40" placeholder="Ingrese el motivo del ajuste...">
              </div>
            </div>
          </div>

          <div class="row" style="margin-top:15px;">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary" onclick="window.location.href='arqueo-comprobante?id=' + $cieId">Ver Comprobante</button>
              <a href="{{ route('cajas.arqueo') }}" class="btn btn-default">Nuevo Arqueo</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

</form>

@endsection

@section('scrip')

<script>

  var $monedasData = {!! $monedasJson !!};
  var $sucursal = $('#sucursal').val();
  var $cieId = 0;
  var $decMonto = {{ env('DEC_MONTO', 2) }};

  function parseNumero(valor) {
    if (!valor) return 0;
    var s = valor.trim().replace(/\s/g, '');
    if (s.indexOf(',') >= 0) {
      s = s.replace(/\./g, '');
      s = s.replace(',', '.');
    }
    return parseFloat(s) || 0;
  }

  function formatearNumeroConSeparadorDeMiles(numero, decimales) {
    if (numero === undefined || numero === null) return '0';
    var partes = parseFloat(numero).toFixed(decimales).split('.');
    var entero = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return decimales > 0 ? entero + ',' + partes[1] : entero;
  }

  var labels = { 'P': 'Pesos $', 'R': 'Reales R$', 'D': 'Dólares USD$' };

  function renderMonedas(monedas) {
    var html = '';

    $.each(monedas, function(moneda, data) {
      if (moneda !== 'P' && !data.tieneMov) return;

      var label = labels[moneda] || moneda;

      html += '<div class="col-sm-4">';
      html += '<div class="panel panel-default">';
      html += '<div class="panel-heading"><b>' + label + '</b></div>';
      html += '<div class="panel-body">';
      html += '<div class="form-group">';
      html += '<label>Monto a Retirar:</label>';
      html += '<input type="text" class="form-control input-monto retiro" data-moneda="' + moneda + '" id="retiro-' + moneda + '" onkeyup="verificarRegistrar()">';
      html += '</div>';
      html += '<div class="form-group">';
      html += '<label>Monto que Queda:</label>';
      html += '<input type="text" class="form-control input-monto queda" data-moneda="' + moneda + '" id="queda-' + moneda + '" onkeyup="verificarRegistrar()">';
      html += '</div>';
      html += '</div>';
      html += '</div>';
      html += '</div>';
    });

    $('#fila-monedas').html(html);
    $('#btn-registrar').prop('disabled', true);
    $('#msg-input').html('');
  }

  function verificarRegistrar() {
    var todosCompletos = true;

    $.each($monedasData, function(moneda, data) {
      var retiro = $('#retiro-' + moneda);
      var queda = $('#queda-' + moneda);
      if (retiro.length && queda.length) {
        if (retiro.val().trim() === '' || queda.val().trim() === '') {
          todosCompletos = false;
        }
      }
    });

    $('#btn-registrar').prop('disabled', !todosCompletos);
    if (!todosCompletos) {
      $('#msg-input').html('<span style="color:#888;">Complete retiro y queda de todas las monedas</span>');
    } else {
      $('#msg-input').html('');
    }
  }

  function registrar() {
    var monedas = [];
    $sucursal = $('#sucursal').val();

    $.each($monedasData, function(moneda, data) {
      var retiro = parseNumero($('#retiro-' + moneda).val());
      var queda = parseNumero($('#queda-' + moneda).val());

      monedas.push({
        moneda: moneda,
        retiro: retiro,
        queda: queda,
        saldoEsperado: data.saldoEsperado,
        ultId: data.ultId,
        maxId: data.maxId
      });
    });

    $.blockUI({ message: '<h3>Registrando...</h3>' });

    $.ajax({
      dataType: "json",
      data: {
        sucursal: $sucursal,
        monedas: JSON.stringify(monedas)
      },
      url: 'arqueo-guardar',
      type: 'post',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      success: function(data) {
        $.unblockUI();
        if (data.success) {
          $cieId = data.cie_id;
          mostrarResultados(data.monedas);
        } else {
          msgerror(data.msg || 'Error al registrar el arqueo');
        }
      },
      error: function(xhr, err) {
        $.unblockUI();
        if (xhr.readyState == 401) {
          document.location.reload();
        } else {
          msgerror('Error al registrar: ' + xhr.responseText);
        }
      }
    });
  }

  function mostrarResultados(monedas) {
    var html = '';
    var tieneAjuste = false;

    $.each(monedas, function(moneda, data) {
      if (moneda !== 'P' && !data.tieneMov) return;

      var label = labels[moneda] || moneda;
      var fisico = data.retiro + data.queda;
      var diff = data.saldoEsperado - fisico;
      var diffClass = 'green';
      var diffText = '0';
      var montoAjuste = 0;
      var tipoAjuste = '';

      if (Math.abs(diff) > 0.01) {
        if (diff > 0) {
          diffClass = 'red';
          diffText = 'FALTA ' + formatearNumeroConSeparadorDeMiles(Math.abs(diff), $decMonto);
          tipoAjuste = '0991 (Ajuste - Pérdida)';
        } else {
          diffClass = 'orange';
          diffText = 'SOBRA ' + formatearNumeroConSeparadorDeMiles(Math.abs(diff), $decMonto);
          tipoAjuste = '0099 (Ventas Sin Anotar)';
        }
        montoAjuste = Math.abs(diff);
        tieneAjuste = true;
      }

      html += '<div class="col-sm-4">';
      html += '<div class="panel panel-default">';
      html += '<div class="panel-heading"><b>' + label + '</b></div>';
      html += '<div class="panel-body">';
      html += '<p>Saldo Anterior: <b>' + formatearNumeroConSeparadorDeMiles(data.saldoAnt, $decMonto) + '</b></p>';
      html += '<p>Movimientos: <b>' + formatearNumeroConSeparadorDeMiles(data.totalMov, $decMonto) + '</b></p>';
      html += '<p>Saldo Esperado: <b>' + formatearNumeroConSeparadorDeMiles(data.saldoEsperado, $decMonto) + '</b></p>';

      if (data.fecUltCierre) {
        html += '<p style="font-size:11px;color:#888;">Últ. cierre: ' + data.fecUltCierre + '</p>';
      }

      html += '<hr>';
      html += '<p>Retiro: <b>' + formatearNumeroConSeparadorDeMiles(data.retiro, $decMonto) + '</b></p>';
      html += '<p>Queda: <b>' + formatearNumeroConSeparadorDeMiles(data.queda, $decMonto) + '</b></p>';
      html += '<p>Total Físico: <b>' + formatearNumeroConSeparadorDeMiles(fisico, $decMonto) + '</b></p>';
      html += '<p>Diferencia: <b id="diff-' + moneda + '" style="color:' + diffClass + ';">' + diffText + '</b></p>';

      if (montoAjuste > 0) {
        html += '<p style="color:' + diffClass + ';"><b>Ajuste:</b> ' + formatearNumeroConSeparadorDeMiles(montoAjuste, $decMonto) + ' - ' + tipoAjuste + '</p>';
      }

      html += '</div>';
      html += '</div>';
      html += '</div>';
    });

    $('#fila-resultados').html(html);

    if (tieneAjuste) {
      $('#grupo-motivo').show();
    } else {
      $('#grupo-motivo').hide();
    }

    $('#panel-resultados').show();
    $('#btn-registrar').prop('disabled', true).hide();
    $('#msg-input').html('');
  }

  $(document).ready(function() {
    renderMonedas($monedasData);
    $('#panel-monedas').show();
  });

</script>

<style>
.input-monto {
  text-align: right;
  font-weight: bold;
}
</style>

@endsection
