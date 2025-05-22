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
                'exclude' => 'sometimes|string' // novo parâmetro opcional
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid input', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unexpected error during validation', 'error' => $e->getMessage()], 500);
        }

        try {
            $city = $request->input('city', 'São Paulo');
            $exclude = $request->input('exclude', null);
            $cacheKey = 'weather_onecall_' . strtolower($city) . ($exclude ? '_ex_' . md5($exclude) : '');

            // Cache for 10 minutes (recommended by OpenWeather)
            $weatherData = Cache::remember($cacheKey, 600, function() use ($city, $exclude) {
                $apiKey = "4351a63614c4ba37966a3faa03b72dd8";
                // 1. Get lat/lon from city name using Geocoding API
                try {
                    $geoResponse = Http::get("https://api.openweathermap.org/geo/1.0/direct", [
                        'q' => $city,
                        'limit' => 1,
                        'appid' => $apiKey
                    ]);
                } catch (Exception $e) {
                    return [
                        'error' => true,
                        'message' => 'HTTP request to geocoding API failed',
                        'details' => $e->getMessage()
                    ];
                }

                if (!$geoResponse->successful() || empty($geoResponse[0])) {
                    return [
                        'error' => true,
                        'message' => 'Failed to resolve city to coordinates',
                        'status' => $geoResponse->status(),
                        'body' => $geoResponse->body()
                    ];
                }

                $geo = $geoResponse[0];
                $lat = $geo['lat'];
                $lon = $geo['lon'];

                // 2. Get weather data from One Call API 3.0
                $params = [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang' => 'pt_br'
                ];
                if ($exclude) {
                    $params['exclude'] = $exclude;
                }

                try {
                    $weatherResponse = Http::get("https://api.openweathermap.org/data/3.0/onecall", $params);
                } catch (Exception $e) {
                    return [
                        'error' => true,
                        'message' => 'HTTP request to One Call API failed',
                        'details' => $e->getMessage()
                    ];
                }

                if (!$weatherResponse->successful()) {
                    return [
                        'error' => true,
                        'message' => 'One Call API returned an unsuccessful response',
                        'status' => $weatherResponse->status(),
                        'body' => $weatherResponse->body()
                    ];
                }

                $data = $weatherResponse->json();

                // 3. Structure the response
                return [
                    'city' => $city,
                    'lat' => $lat,
                    'lon' => $lon,
                    'timezone' => $data['timezone'] ?? null,
                    'current' => $data['current'] ?? null,
                    'hourly' => $data['hourly'] ?? null,
                    'daily' => $data['daily'] ?? null,
                    'alerts' => $data['alerts'] ?? [],
                    'raw' => $data
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
