<?php

namespace App\Actions\Cristales;

use Illuminate\Support\Collection;

/**
 * Comprueba si alguno de los rangos del tipo cubre las graduaciones pedidas.
 */
class FiltrarGraduacionesCristalAction
{
    /**
     * @param  Collection<int, mixed>  $registros
     */
    public function __invoke(Collection $registros, ?float $esf, ?float $cil): bool
    {
        // Llegan todos los rangos del tipo
        if ($esf === null && $cil === null) {
            return true; //no se filtra por graduación
        }
         
        return $registros->contains(function (mixed $registro) use ($esf, $cil): bool {
             return $this->ValidaDentroDelRango($esf,$cil, $registro);
        });
    }

    private function ValidaDentroDelRango(?float $esf, ?float $cil, $registro): bool
    {

        // Valido 1 rango 
        $minimoEsf = min((float) $registro->Cri_esf_neg, (float) $registro->Cri_esf);
        $maximoEsf = max((float) $registro->Cri_esf_neg, (float) $registro->Cri_esf);
        $minimoCil = min((float) $registro->Cri_cil_neg, (float) $registro->Cri_cil);
        $maximoCil = max((float) $registro->Cri_cil_neg, (float) $registro->Cri_cil);       

        // Veo si es necesario convertir el cilindrico a positivo para comparar con el rango
        if ($cil !== null) {
            if ($minimoCil < 0 OR $maximoCil < 0) {
                if ($cil > 0) {
                    // Si el rango es negativo y la graduación es positiva, convierto la graduación a negativa    
                    $esf = $esf + $cil ;
                    $cil = -$cil;
                    displaylog("Id: $registro->Cri_id Cilindrico convertido a neg : $cil, esf: $esf"); 
                }
            }        
            if ($minimoCil > 0 OR $maximoCil > 0) {
                if ($cil < 0) {
                    // Si el rango es negativo y la graduación es positiva, convierto la graduación a negativa    
                    $esf = $esf + $cil ; 
                    $cil = $cil * -1;
                    displaylog("Id: $registro->Cri_id Cilindrico convertido a pos : $cil, esf: $esf"); 
                }
            }        
            if( $registro->Cri_metodo == 1) {
                // Si tiene que validar la suma
                $suma = $esf + $cil ;
                if( $suma < $minimoEsf OR $suma > $maximoEsf) {
                    // Suma fuera del rango
                    displaylog("Id: $registro->Cri_id Suma Fuera de Rango  Cil: $cil, esf: $esf") ;
                    return false;
                }
            }
        }
        if ($esf !== null) {
            if( $esf < $minimoEsf OR $esf > $maximoEsf) {
                // Esferico fuera del rango
                displaylog("Id: $registro->Cri_id Esferico Fuera de Rango  Cil: $cil, esf: $esf") ;
                return false;
            }
        }
        if ($cil !== null) {
            if( $cil < $minimoCil OR $cil > $maximoCil) {
                // Cilindrico fuera del rango
                displaylog("Id: $registro->Cri_id Cilindrico Fuera de Rango  Cil: $cil, esf: $esf") ;
                return false;
            }
        }

      //  dd($esf,$cil, $registro );

        return true;

    }

}
