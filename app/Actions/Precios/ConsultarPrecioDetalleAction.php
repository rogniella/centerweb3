<?php

namespace App\Actions\Precios;

use App\Models\cotizacion;
use App\Models\producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultarPrecioDetalleAction
{
    public function __invoke(Request $request): void
    {
        $cotreal = 0;
        cotizacion::mtoEnPesos('R', 100, '', $cotreal);

        $oproducto = producto::findCodigo($request->familia, $request->codigo);

        if (! $oproducto) {
            $this->retornarError($request->familia . '&nbsp;' . $request->codigo, 'Error al buscar producto');
            exit;
        }

        $oproducto->Prod_UsuUltMan = auth()->user()?->name ?? 'Sin Conecc';
        $oproducto->insertHistoria('WEBConPrecio', '', '');

        echo "<div class='panel panel-success'>";
        echo "<div class='panel-heading'>";
        echo "<h3 class='panel-title'>" . $oproducto->Prod_Familia . '&nbsp;' . $oproducto->Prod_Id . ':&nbsp;' . $oproducto->Prod_Descripcion . '</h3> </div>';
        echo "<div class='panel-body'>";

        echo "<table class='table table-striped'>";
        echo '<tr>';
        echo '<td>Precio  $</td>';
        echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio, 2, ',', '.') . '</b></td>';
        echo "<td align='right'><button type='button' onClick='vender(" . $oproducto->Prod_Precio . ",0)' class='btn btn-success'>Vender</button></td>";
        echo '</tr>';
        echo '<tr>';
        echo '<td>Reales</td>';
        echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio / $cotreal, 2, ',', '.') . '</b></td>';
        echo '<td></td>';
        echo '</tr>';
        echo '</table>';

        echo 'Cotizacion del Real ' . number_format($cotreal, 2, ',', '.');
        echo '<br>';

        $descuento = $oproducto->Prod_Precio - $oproducto->Prod_Precio2;
        $porc_descuento = $oproducto->Prod_Precio > 0 ? ($descuento / $oproducto->Prod_Precio * 100) : 0;

        echo '<br>';
        echo '<h4><b>Al Contado ' . number_format($porc_descuento, 0, ',', '.') . '% desc: </b> $ ' . number_format($descuento, 2, ',', '.') . ' </h4>';
        echo "<table class='table table-striped'>";
        echo '<tr>';
        echo '<td>Precio  $</td>';
        echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio2, 2, ',', '.') . '</b></td>';
        echo "<td align='right'><button type='button' onClick='vender(" . $oproducto->Prod_Precio . ',' . $descuento . " )'  class='btn btn-success'>Vender</button></td>";
        echo '</tr>';
        echo '<tr>';
        echo '<td>Reales</td>';
        echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio2 / $cotreal, 2, ',', '.') . '</b></td>';
        echo '<td></td>';
        echo '</tr>';
        echo '</table>';

        echo '<h4><b>Plan Cuotas con Tarjetas:</b></h4>';
        echo "<table class='table table-striped  table-bordered'>";
        echo '<thead>';
        echo '<tr>';
        echo '<th>Cuotas</th>';
        echo '<th>Valor Cuota</th>';
        echo '<th>Total</th>';
        echo '<th>Coefi</th>';
        echo '</thead>';
        echo '<tbody>';

        $datos = DB::select("SELECT TARCUO_CUOTA, TARCUO_INTERES FROM tarjetacuotas WHERE TARCUO_ID = 'VI' ORDER BY TARCUO_CUOTA");
        foreach ($datos as $row) {
            echo '<tr>';
            echo "<td align='center'>" . $row->TARCUO_CUOTA . '</td>';
            echo "<td align='center'>" . number_format($oproducto->Prod_Precio * $row->TARCUO_INTERES / $row->TARCUO_CUOTA, 2, ',', '.') . '</td>';
            echo "<td align='right'>" . number_format($oproducto->Prod_Precio * $row->TARCUO_INTERES, 2, ',', '.') . '</td>';
            echo "<td align='right'>" . $row->TARCUO_INTERES . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    private function retornarError(string $titulo, string $msg): void
    {
        echo "<div class='alert alert-danger'>";
        echo "<div class='panel-heading'>";
        echo "   <h3 class='panel-title'><b>" . $titulo . '</b></h3> </div>';
        echo "<div class='panel-body'>";
        echo $msg;
        echo '</div>';
    }
}
