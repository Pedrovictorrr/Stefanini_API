<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_get_weather()
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'weather' => [['description' => 'clear sky']],
                'main' => ['temp' => 25.5],
            ], 200)
        ]);

        $response = $this->getJson('/api/v1/weather?city=São Paulo');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'weather',
                'main'
            ]);
    }
}