<?php   namespace App\Clases;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class correlativo {

    public static function leo_proximo($tipocomprobante) {

        $consulta = "SELECT cor_ultimo FROM Correlativos  WHERE    Cor_Tipo=?" ;
        $datos = DB::select($consulta,[ $tipocomprobante] );
        return $datos[0]->cor_ultimo + 1;

    } //    End Function leo correlativo

    public static function gravo_correlativo($tipocomprobante,$nvoid) {
        
        $cor_fecultman = fechahorahoy();

        $consulta  = "UPDATE Correlativos SET  Cor_Ultimo =" . NumDec($nvoid) . ", Cor_FecUltMan ='" . $cor_fecultman .
                 "' WHERE(    Cor_Tipo='" . $tipocomprobante . "')";
        
        $datos = DB::update($consulta );
        
        return 0; //Ok
  
    } //    End Function 
    
}
