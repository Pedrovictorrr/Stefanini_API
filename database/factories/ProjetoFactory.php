<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projeto>
 */
class ProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->sentence(3),
            'descricao' => $this->faker->optional()->paragraph(),
            'data_inicio' => $this->faker->date(),
            'data_termino' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['ativo', 'inativo', 'concluido']),
            // 'user_id' será preenchido nos testes
        ];
    }
}
