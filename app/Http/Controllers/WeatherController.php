<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WeatherData;
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
                    // 1. Obter coordenadas da cidade
                    $geoResponse = Http::get("https://api.openweathermap.org/data/2.5/weather", [
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

                if (isset($geoResponse) && $geoResponse->successful()) {
                    $geoData = $geoResponse->json();
                    if (!isset($geoData['coord']['lat']) || !isset($geoData['coord']['lon'])) {
                        return [
                            'error' => true,
                            'message' => 'Coordinates not found for the city',
                            'body' => $geoData
                        ];
                    }
                    $lat = $geoData['coord']['lat'];
                    $lon = $geoData['coord']['lon'];

                    // 2. Obter dados detalhados com onecall
                    try {
                        $oneCallResponse = Http::get("https://api.openweathermap.org/data/3.0/onecall", [
                            'lat' => $lat,
                            'lon' => $lon,
                            'exclude' => 'minutely,alerts', // customize as needed
                            'appid' => $apiKey,
                            'units' => 'metric',
                            'lang' => 'pt_br'
                        ]);
                    } catch (Exception $e) {
                        return [
                            'error' => true,
                            'message' => 'HTTP request to OneCall API failed',
                            'details' => $e->getMessage()
                        ];
                    }

                    if (isset($oneCallResponse) && $oneCallResponse->successful()) {
                        $data = $oneCallResponse->json();

                        try {
                            // Save to database
                            WeatherData::create([
                                'city' => $city,
                                'data' => json_encode($data),
                                'temperature' => isset($data['current']['temp']) ? $data['current']['temp'] : null,
                                'conditions' => isset($data['current']['weather'][0]['description']) ? $data['current']['weather'][0]['description'] : null
                            ]);
                        } catch (Exception $e) {
                            $data['db_error'] = 'Failed to save weather data to database: ' . $e->getMessage();
                        }

                        return $data;
                    }

                    // Falha na resposta da OneCall API
                    return [
                        'error' => true,
                        'message' => 'OneCall API returned an unsuccessful response',
                        'status' => isset($oneCallResponse) ? $oneCallResponse->status() : null,
                        'body' => isset($oneCallResponse) ? $oneCallResponse->body() : null
                    ];
                }

                // Falha na resposta da API de coordenadas
                return [
                    'error' => true,
                    'message' => 'Weather API returned an unsuccessful response',
                    'status' => isset($geoResponse) ? $geoResponse->status() : null,
                    'body' => isset($geoResponse) ? $geoResponse->body() : null
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
