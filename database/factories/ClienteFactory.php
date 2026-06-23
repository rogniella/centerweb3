<?php

namespace Database\Factories;

use App\Models\cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = cliente::class;

    public function definition(): array
    {
        return [
            'Cli_Id' => fake()->unique()->numberBetween(1, 99999),
            'Cli_ApeNom' => fake()->name(),
            'Cli_Documento' => fake()->numerify('########'),
            'Cli_Telefono' => fake()->phoneNumber(),
            'Cli_Pais' => 'AR',
            'Cli_Calle' => fake()->address(),
            'Cli_CodRespIVA' => fake()->randomElement(['RI', 'CF', 'EX', 'MT']),
            'Cli_Cuil' => fake()->numerify('###########'),
            'Cli_CodDocumento' => fake()->randomElement(['DNI', 'CUIT', 'CUIL']),
        ];
    }
}
