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
        if ($esf === null && $cil === null) {
            return true;
        }

        return $registros->contains(function (mixed $registro) use ($esf, $cil): bool {
            return $this->estaDentroDelRango($esf, $registro->Cri_esf_neg, $registro->Cri_esf)
                && $this->estaDentroDelRango($cil, $registro->Cri_cil_neg, $registro->Cri_cil);
        });
    }

    private function estaDentroDelRango(?float $graduacion, mixed $limiteUno, mixed $limiteDos): bool
    {
        if ($graduacion === null) {
            return true;
        }

        if ($limiteUno === null || $limiteDos === null || $limiteUno === '' || $limiteDos === '') {
            return false;
        }

        $minimo = min((float) $limiteUno, (float) $limiteDos);
        $maximo = max((float) $limiteUno, (float) $limiteDos);

        return $graduacion >= $minimo && $graduacion <= $maximo;
    }
}
