<?php

namespace Database\Factories;

use App\Models\proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = proveedor::class;

    public function definition(): array
    {
        return [
            'Prov_id' => fake()->unique()->numberBetween(1, 9999),
            'Prov_RazSocial' => fake()->company(),
            'Prov_NomFant' => fake()->company(),
            'Prov_Telefono' => fake()->phoneNumber(),
            'Prov_Calle' => fake()->address(),
            'Prov_EMail' => fake()->companyEmail(),
            'Prov_Cuit' => fake()->numerify('##-########-#'),
            'Prov_CtaCon' => fake()->randomElement(['01', '02', '03']),
            'Prov_TipoProv' => fake()->randomElement(['NAC', 'INT', 'LOC']),
            'Prov_Observ' => fake()->sentence(),
            'Prov_FormaPago' => fake()->randomElement(['CONTADO', 'CTA CTE', 'CHEQUE']),
        ];
    }
}
