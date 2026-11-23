<?php

namespace Database\Factories;

use App\Models\cristal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<cristal>
 */
class CristalFactory extends Factory
{
    protected $model = cristal::class;

    public function definition(): array
    {
        return [
            'Cri_familia' => 'LEN',
            'Cri_id' => fake()->unique()->numerify('#####'),
            'Cri_esf' => fake()->randomFloat(2, -10, 10),
            'Cri_esf_neg' => fake()->randomFloat(2, -10, 0),
            'Cri_cil' => fake()->randomFloat(2, -4, 4),
            'Cri_cil_neg' => fake()->randomFloat(2, -4, 0),
            'Cri_add' => fake()->randomFloat(2, 0, 4),
            'Cri_rango' => null,
            'Cri_metodo' => null,
        ];
    }
}
