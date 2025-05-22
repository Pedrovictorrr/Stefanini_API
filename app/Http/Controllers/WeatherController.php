<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Validation\ValidationException;

class WeatherController extends Controller
{
    public function getWeather(Request $request)
    {
        try {
            $request->validate([
                'city' => 'sometimes|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid input', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unexpected error during validation', 'error' => $e->getMessage()], 500);
        }

        try {
            $city = $request->input('city', 'São Paulo');
            $cacheKey = 'weather_' . strtolower($city);

            // Cache for 1 hour
            $weatherData = Cache::remember($cacheKey, 3600, function() use ($city) {
                try {
                    $apiKey = "4351a63614c4ba37966a3faa03b72dd8";
                    // Obter dados do tempo diretamente
                    $weatherResponse = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                        'q' => $city,
                        'appid' => $apiKey,
                        'units' => 'metric',
                        'lang' => 'pt_br'
                    ]);
                } catch (Exception $e) {
                    return [
                        'error' => true,
                        'message' => 'HTTP request to weather API failed',
                        'details' => $e->getMessage()
                    ];
                }

                if (isset($weatherResponse) && $weatherResponse->successful()) {
                    $data = $weatherResponse->json();

                    return [
                        'city' => $city,
                        'temperature' => $data['main']['temp'] ?? null,
                        'conditions' => $data['weather'][0]['description'] ?? null,
                        'humidity' => $data['main']['humidity'] ?? null,
                        'wind_speed' => $data['wind']['speed'] ?? null,
                        'raw' => $data
                    ];
                }

                // Falha na resposta da API
                return [
                    'error' => true,
                    'message' => 'Weather API returned an unsuccessful response',
                    'status' => isset($weatherResponse) ? $weatherResponse->status() : null,
                    'body' => isset($weatherResponse) ? $weatherResponse->body() : null
                ];
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unexpected error during weather data retrieval',
                'error' => $e->getMessage()
            ], 500);
        }

        // Checa se houve erro na requisição HTTP ou resposta da API
        if (is_array($weatherData) && isset($weatherData['error']) && $weatherData['error'] === true) {
            return response()->json([
                'message' => $weatherData['message'] ?? 'Failed to fetch weather data',
                'details' => $weatherData['details'] ?? null,
                'status' => $weatherData['status'] ?? null,
                'body' => $weatherData['body'] ?? null
            ], 502);
        }

        if (!$weatherData) {
            return response()->json(['message' => 'Failed to fetch weather data'], 500);
        }

        return response()->json($weatherData);
    }
}
