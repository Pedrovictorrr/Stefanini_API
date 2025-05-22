<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\WeatherData;

class WeatherController extends Controller
{
    public function getWeather(Request $request)
    {
        try {
            // Validação dos parâmetros obrigatórios
            $validated = $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
            ]);

            $lat = $validated['lat'];
            $lon = $validated['lon'];

            $apiKey = env("WEATHERAPI_KEY");
            if (!$apiKey) {
                return response()->json(['error' => 'API key not configured.'], 500);
            }

            // WeatherAPI usa latitude e longitude no parâmetro 'q'
            $url = 'https://api.weatherapi.com/v1/current.json';
            $query = [
                'key' => $apiKey,
                'q' => "{$lat},{$lon}",
            ];

            try {
                $response = Http::timeout(10)->get($url, $query);

                if ($response->failed()) {
                    return response()->json(['error' => 'Failed to fetch weather data', 'details' => $response->json()], $response->status());
                }

                $weatherData = $response->json();

                // Salvar dados no banco de dados
                WeatherData::create([
                    'city' => $weatherData['location']['name'] ?? null,
                    'data' => $weatherData,
                    'temperature' => $weatherData['current']['temp_c'] ?? null,
                    'conditions' => $weatherData['current']['condition']['text'] ?? null,
                ]);
            } catch (Exception $e) {
                return response()->json(['error' => 'Exception occurred', 'message' => $e->getMessage()], 500);
            }

            return response()->json($weatherData);
        } catch (ValidationException $ve) {
            return response()->json(['error' => 'Validation failed', 'messages' => $ve->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
        }
    }
}
