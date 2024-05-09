<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participa>
 */
class ParticipaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $estado = ['pendiente', 'aceptado', 'rechazado'];
        return [
            //
            'id_juego'=>$this->faker->numberBetween(1,5),
            'telefono'=>$this->faker->e164PhoneNumber,
            'email'=>$this->faker->email,
            'estado_participa'=>$this->faker->randomElement($estado),
        ];
    }
}
