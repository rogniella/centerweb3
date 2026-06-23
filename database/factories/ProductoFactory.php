<?php

namespace Database\Factories;

use App\Models\producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = producto::class;

    public function definition(): array
    {
        $familias = ['REC', 'SOL', 'CRI', 'ACC', 'CEL'];

        return [
            'Prod_Familia' => fake()->randomElement($familias),
            'Prod_Id' => fake()->unique()->numerify('#####'),
            'Prod_Categoria' => fake()->word(),
            'Prod_Descripcion' => fake()->sentence(3),
            'Prod_Precio' => fake()->randomFloat(2, 100, 50000),
            'Prod_Precio2' => fake()->randomFloat(2, 50, 25000),
            'Prod_Costo' => fake()->randomFloat(2, 50, 5000),
            'Prod_Estado' => '',
            'Prod_Marca' => fake()->randomElement(['NIKE', 'RAYBAN', 'POLAROID', 'VOGUE', 'ARMANI']),
        ];
    }
}
