<?php

namespace Tests\Feature;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjetoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_get_projetos()
    {
        Projeto::factory()->count(3)->create(['user_id' => auth()->id()]);

        $response = $this->getJson('/api/v1/projetos');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_create_projeto()
    {
        $data = [
            'nome' => 'Novo Projeto',
            'descricao' => 'Descrição do projeto',
            'data_inicio' => '2023-01-01',
            'status' => 'ativo'
        ];

        $response = $this->postJson('/api/v1/projetos', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    public function test_update_projeto()
    {
        $projeto = Projeto::factory()->create(['user_id' => auth()->id()]);

        $data = [
            'nome' => 'Projeto Atualizado',
            'status' => 'concluido'
        ];

        $response = $this->putJson("/api/v1/projetos/{$projeto->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);
    }

    public function test_delete_projeto()
    {
        $projeto = Projeto::factory()->create(['user_id' => auth()->id()]);

        $response = $this->deleteJson("/api/v1/projetos/{$projeto->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('projetos', ['id' => $projeto->id]);
    }
}