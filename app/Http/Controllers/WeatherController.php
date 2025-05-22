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
        // Validação dos parâmetros obrigatórios
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'exclude' => 'sometimes|string',
            'units' => 'sometimes|string|in:standard,metric,imperial',
            'lang' => 'sometimes|string'
        ]);

        $lat = $validated['lat'];
        $lon = $validated['lon'];
        $exclude = $validated['exclude'] ?? null;
        $units = $validated['units'] ?? null;
        $lang = $validated['lang'] ?? null;

        $apiKey = env('OPENWEATHER_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'API key not configured.'], 500);
        }

        $url = 'https://api.openweathermap.org/data/3.0/onecall';
        $query = [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
        ];
        if ($exclude) $query['exclude'] = $exclude;
        if ($units) $query['units'] = $units;
        if ($lang) $query['lang'] = $lang;

        try {
            $response = Http::timeout(10)->get($url, $query);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch weather data', 'details' => $response->json()], $response->status());
            }

            $weatherData = $response->json();
        } catch (Exception $e) {
            return response()->json(['error' => 'Exception occurred', 'message' => $e->getMessage()], 500);
        }

        return response()->json($weatherData);
    }
}
