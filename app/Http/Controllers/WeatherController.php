<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $apiKey = config('services.openweathermap.key');

        if (!$apiKey) {
            return response()->json(['error' => 'no_key'], 200);
        }

        try {
            $res = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/forecast', [
                'lat'   => (float) $request->lat,
                'lon'   => (float) $request->lng,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang'  => 'ja',
                'cnt'   => 8, // 24時間 × 8 = 約3日分 (3時間毎)
            ]);

            if (!$res->successful()) {
                return response()->json(['error' => 'api_error'], 200);
            }

            $data = $res->json();

            // 日別に集約（最高/最低気温、代表天気）
            $days = [];
            foreach ($data['list'] as $item) {
                $day = date('Y-m-d', $item['dt']);
                if (!isset($days[$day])) {
                    $days[$day] = [
                        'date'    => $day,
                        'label'   => date('n/j', $item['dt']),
                        'weekday' => ['日','月','火','水','木','金','土'][date('w', $item['dt'])],
                        'temp_max' => $item['main']['temp_max'],
                        'temp_min' => $item['main']['temp_min'],
                        'icon'    => $item['weather'][0]['icon'],
                        'desc'    => $item['weather'][0]['description'],
                    ];
                } else {
                    $days[$day]['temp_max'] = max($days[$day]['temp_max'], $item['main']['temp_max']);
                    $days[$day]['temp_min'] = min($days[$day]['temp_min'], $item['main']['temp_min']);
                    // 昼時間帯（12時付近）の天気を優先
                    if (date('H', $item['dt']) === '12') {
                        $days[$day]['icon'] = $item['weather'][0]['icon'];
                        $days[$day]['desc'] = $item['weather'][0]['description'];
                    }
                }
            }

            return response()->json([
                'city' => $data['city']['name'] ?? '',
                'days' => array_values(array_slice($days, 0, 5)),
            ]);
        } catch (\Exception) {
            return response()->json(['error' => 'exception'], 200);
        }
    }
}
