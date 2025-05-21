<?php

namespace Database\Seeders;

use App\Models\Projeto;
use App\Models\User;
use App\Models\WeatherData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuário fictício
        $user = User::factory()->create([
            'name' => 'Usuario',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Suporte',
            'email' => 'suporte@stefanini.com',
            'password' => bcrypt('suporte123'),
        ]);

        // Projetos fictícios
        Projeto::create([
            'user_id' => $user->id,
            'nome' => 'Sistema de Gestão',
            'descricao' => 'Projeto para gerenciar processos internos.',
            'data_inicio' => '2024-05-01',
            'data_termino' => '2024-08-01',
            'status' => 'Em andamento'
        ]);

        Projeto::create([
            'user_id' => $user->id,
            'nome' => 'Portal do Cliente',
            'descricao' => 'Portal para clientes acompanharem seus pedidos.',
            'data_inicio' => '2024-06-01',
            'data_termino' => '2024-09-15',
            'status' => 'Planejado'
        ]);

        // WeatherData fictício
        WeatherData::create([
            'city' => 'São Paulo',
            'data' => ['humidity' => 80, 'wind' => 10],
            'temperature' => 25.5,
            'conditions' => 'Ensolarado'
        ]);

        WeatherData::create([
            'city' => 'Rio de Janeiro',
            'data' => ['humidity' => 70, 'wind' => 8],
            'temperature' => 28.2,
            'conditions' => 'Parcialmente nublado'
        ]);
    }
}
