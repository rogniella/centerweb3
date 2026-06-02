@extends('template.informes')
@section('titulo','Comprobante de Arqueo de Caja')

@section('contenido')

<style>
  @media print {
    .no-print { display: none; }
    body { font-size: 12pt; }
  }
  .comprobante {
    max-width: 700px;
    margin: 0 auto;
    font-family: 'Courier New', monospace;
  }
  .comprobante h2 {
    text-align: center;
    font-size: 18pt;
    margin-bottom: 5px;
  }
  .comprobante .linea {
    border-top: 2px solid #000;
    margin: 8px 0;
  }
  .comprobante .linea-doble {
    border-top: 4px double #000;
    margin: 10px 0;
  }
  .comprobante table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
  }
  .comprobante table th {
    border-bottom: 2px solid #000;
    text-align: center;
    padding: 5px;
  }
  .comprobante table td {
    padding: 5px;
    text-align: center;
  }
  .comprobante table td.right {
    text-align: right;
  }
  .comprobante table td.left {
    text-align: left;
  }
  .comprobante .total-row td {
    border-top: 2px solid #000;
    font-weight: bold;
  }
  .comprobante .motivo {
    margin-top: 10px;
    padding: 8px;
    border: 1px solid #999;
    font-style: italic;
  }
  .btn-imprimir {
    display: block;
    width: 200px;
    margin: 20px auto;
    padding: 10px;
    font-size: 14pt;
  }
</style>

<div class="no-print" style="text-align:center; margin-bottom:20px;">
  <button class="btn btn-primary btn-imprimir" onclick="window.print()">Imprimir Comprobante</button>
  <a href="{{ route('cajas.arqueo') }}" class="btn btn-default" style="padding:10px 20px; font-size:14pt;">Nuevo Arqueo</a>
</div>

<div class="comprobante">

  <h2>ARQUEO DE CAJA</h2>

  <div class="linea-doble"></div>

  <table>
    <tr>
      <td class="left"><b>N° Cierre:</b> {{ $cierre->Cie_idWEB }}</td>
      <td class="right"><b>Fecha:</b> {{ date('d/m/Y H:i', strtotime($cierre->Cie_cierre_fecha)) }}</td>
    </tr>
    <tr>
      <td class="left"><b>Responsable:</b> {{ $cierre->Cie_cierre_usu }}</td>
      <td class="right"><b>Sucursal:</b> {{ $sucursal ?? $cierre->Cie_sucursal }}</td>
    </tr>
  </table>

  <div class="linea"></div>

  <table>
    <thead>
      <tr>
        <th>Moneda</th>
        <th>Retiro</th>
        <th>Queda</th>
        <th>Total Físico</th>
        <th>Ajuste</th>
      </tr>
    </thead>
    <tbody>
      @if($cierre->Cie_retiro_p_final > 0 || $cierre->Cie_queda_p_final > 0)
      <tr>
        <td class="left"><b>Pesos ($)</b></td>
        <td class="right">{{ number_format($cierre->Cie_retiro_p_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ number_format($cierre->Cie_queda_p_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ number_format($cierre->Cie_retiro_p_final + $cierre->Cie_queda_p_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ $cierre->Cie_ajuste_p ? number_format($cierre->Cie_ajuste_p, env('DEC_MONTO', 2), ',', '.') : '-' }}</td>
      </tr>
      @endif
      @if($cierre->Cie_retiro_r_final > 0 || $cierre->Cie_queda_r_final > 0)
      <tr>
        <td class="left"><b>Reales (R$)</b></td>
        <td class="right">{{ number_format($cierre->Cie_retiro_r_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ number_format($cierre->Cie_queda_r_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ number_format($cierre->Cie_retiro_r_final + $cierre->Cie_queda_r_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ $cierre->Cie_ajuste_r ? number_format($cierre->Cie_ajuste_r, env('DEC_MONTO', 2), ',', '.') : '-' }}</td>
      </tr>
      @endif
      @if($cierre->Cie_retiro_d_final > 0 || $cierre->Cie_queda_d_final > 0)
      <tr>
        <td class="left"><b>Dólares (USD$)</b></td>
        <td class="right">{{ number_format($cierre->Cie_retiro_d_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ number_format($cierre->Cie_queda_d_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ number_format($cierre->Cie_retiro_d_final + $cierre->Cie_queda_d_final, env('DEC_MONTO', 2), ',', '.') }}</td>
        <td class="right">{{ $cierre->Cie_ajuste_d ? number_format($cierre->Cie_ajuste_d, env('DEC_MONTO', 2), ',', '.') : '-' }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  <div class="linea"></div>

  @php
    $hayAjusteP = $cierre->Cie_ajuste_p_motivo && abs($cierre->Cie_ajuste_p) > 0.001;
    $hayAjusteR = $cierre->Cie_ajuste_r_motivo && abs($cierre->Cie_ajuste_r) > 0.001;
    $hayAjusteD = $cierre->Cie_ajuste_d_motivo && abs($cierre->Cie_ajuste_d) > 0.001;
  @endphp

  @if($hayAjusteP || $hayAjusteR || $hayAjusteD)
    <h3 style="text-align:center;">Ajustes</h3>
    @if($hayAjusteP)
      <div class="motivo">
        <b>Pesos:</b>
        @if($cierre->Cie_ajuste_p > 0)
          Sobrante (Ventas sin Anotar) - Cód. 0099
        @else
          Faltante (Ajuste - Pérdida) - Cód. 0991
        @endif
        <br><b>Motivo:</b> {{ $cierre->Cie_ajuste_p_motivo }}
      </div>
    @endif
    @if($hayAjusteR)
      <div class="motivo">
        <b>Reales:</b>
        @if($cierre->Cie_ajuste_r > 0)
          Sobrante (Ventas sin Anotar) - Cód. 0099
        @else
          Faltante (Ajuste - Pérdida) - Cód. 0991
        @endif
        <br><b>Motivo:</b> {{ $cierre->Cie_ajuste_r_motivo }}
      </div>
    @endif
    @if($hayAjusteD)
      <div class="motivo">
        <b>Dólares:</b>
        @if($cierre->Cie_ajuste_d > 0)
          Sobrante (Ventas sin Anotar) - Cód. 0099
        @else
          Faltante (Ajuste - Pérdida) - Cód. 0991
        @endif
        <br><b>Motivo:</b> {{ $cierre->Cie_ajuste_d_motivo }}
      </div>
    @endif
  @else
    <p style="text-align:center; font-weight:bold; color:green;">SIN AJUSTES</p>
  @endif

  <div class="linea-doble"></div>

  <p style="text-align:center; font-size:9pt;">
    Generado el {{ date('d/m/Y H:i:s') }} - {{ config('app.name') }}
  </p>

</div>

<script>
  function soloLectura() {
    var inputs = document.querySelectorAll('input, select, button');
    inputs.forEach(function(el) {
      if (!el.classList.contains('no-print') && el.type !== 'button') {
        el.disabled = true;
      }
    });
  }
  window.onload = soloLectura;
</script>

@endsection
