<?php

namespace App\Clases;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class correlativo
{
    public static function leo_proximo($tipocomprobante)
    {

        $consulta = 'SELECT cor_ultimo FROM correlativos  WHERE    Cor_Tipo=?';
        $datos = DB::select($consulta, [$tipocomprobante]);

        return $datos[0]->cor_ultimo + 1;

    } //    End Function leo correlativo

    public static function gravo_correlativo($tipocomprobante, $nvoid)
    {

        $cor_fecultman = fechahorahoy();

        $consulta = 'UPDATE correlativos SET  Cor_Ultimo = ?, Cor_FecUltMan = ? WHERE(    Cor_Tipo=?)';

        $datos = DB::update($consulta, [NumDec($nvoid), $cor_fecultman, $tipocomprobante]);

        return 0; // Ok

    } //    End Function

}
