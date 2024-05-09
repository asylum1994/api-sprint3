<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Juego>
 */
class JuegoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodo = ['diario', 'semanal', 'mensual'];
        $estado = ['pendiente', 'iniciado', 'finalizado'];
        return [
            'usuario'=> $this->faker->email,
            'nombre'=> $this->faker->company,
            'descripcion'=> $this->faker->sentence,
            'monto'=> $this->faker->randomNumber(5),
            'periodo'=>$this->faker->randomElement($periodo),
            'fecha_inicio'=>$this->faker->date(),
            'estado_juego'=>$this->faker->randomElement($estado),
        ];
    }
}
