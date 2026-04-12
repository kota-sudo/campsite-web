<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NearbySpotController extends Controller
{
    // 検索半径 (km)
    private const RADIUS_KM = 20;

    // カテゴリ定義: [Overpassタグ条件 => [label, icon, priority]]
    private const CATEGORIES = [
        'peak'       => ['label' => '山頂・登山', 'icon' => '⛰️'],
        'viewpoint'  => ['label' => '展望スポット', 'icon' => '🔭'],
        'attraction' => ['label' => '観光スポット', 'icon' => '🏛️'],
        'waterfall'  => ['label' => '滝', 'icon' => '💧'],
        'hot_spring' => ['label' => '温泉', 'icon' => '♨️'],
        'camp_site'  => ['label' => 'キャンプ場', 'icon' => '⛺'],
        'museum'     => ['label' => '博物館・資料館', 'icon' => '🏛️'],
        'park'       => ['label' => '公園・自然', 'icon' => '🌲'],
        'beach'      => ['label' => 'ビーチ', 'icon' => '🏖️'],
        'other'      => ['label' => 'その他', 'icon' => '📍'],
    ];

    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radiusM = self::RADIUS_KM * 1000;

        // Overpass QL クエリ: 自然地物、観光スポット、温泉を取得
        $overpassQuery = <<<EOQ
[out:json][timeout:15];
(
  node["natural"="peak"](around:{$radiusM},{$lat},{$lng});
  node["tourism"="viewpoint"](around:{$radiusM},{$lat},{$lng});
  node["tourism"="attraction"](around:{$radiusM},{$lat},{$lng});
  node["waterway"="waterfall"](around:{$radiusM},{$lat},{$lng});
  node["amenity"="spa"](around:{$radiusM},{$lat},{$lng});
  node["natural"="hot_spring"](around:{$radiusM},{$lat},{$lng});
  node["tourism"="museum"](around:{$radiusM},{$lat},{$lng});
  node["leisure"="park"](around:{$radiusM},{$lat},{$lng});
  node["natural"="beach"](around:{$radiusM},{$lat},{$lng});
);
out body 30;
EOQ;

        try {
            $res = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'CampsiteWebApp/1.0'])
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $overpassQuery,
                ]);

            if (! $res->successful()) {
                return response()->json(['spots' => [], 'error' => 'api_error']);
            }

            $elements = $res->json('elements', []);
            $spots = [];

            foreach ($elements as $el) {
                $tags = $el['tags'] ?? [];
                $name = $tags['name'] ?? ($tags['name:ja'] ?? null);
                if (! $name) {
                    continue;
                }

                $elLat = $el['lat'] ?? null;
                $elLng = $el['lon'] ?? null;
                if ($elLat === null || $elLng === null) {
                    continue;
                }

                $category = $this->resolveCategory($tags);
                $distance = $this->haversineKm($lat, $lng, $elLat, $elLng);

                $spots[] = [
                    'name'       => $name,
                    'name_en'    => $tags['name:en'] ?? null,
                    'category'   => $category,
                    'icon'       => self::CATEGORIES[$category]['icon'],
                    'label'      => self::CATEGORIES[$category]['label'],
                    'distance'   => round($distance, 1),
                    'elevation'  => isset($tags['ele']) ? (int) $tags['ele'] : null,
                    'osmUrl'     => "https://www.openstreetmap.org/node/{$el['id']}",
                    'lat'        => $elLat,
                    'lng'        => $elLng,
                    'wikipedia'  => $tags['wikipedia'] ?? null,
                    'description'=> $tags['description'] ?? ($tags['description:ja'] ?? null),
                ];
            }

            // 距離順にソートして最大20件
            usort($spots, fn ($a, $b) => $a['distance'] <=> $b['distance']);
            $spots = array_slice($spots, 0, 20);

            return response()->json(['spots' => $spots]);
        } catch (\Exception) {
            return response()->json(['spots' => [], 'error' => 'exception']);
        }
    }

    private function resolveCategory(array $tags): string
    {
        if (isset($tags['natural']) && $tags['natural'] === 'peak') return 'peak';
        if (isset($tags['natural']) && $tags['natural'] === 'hot_spring') return 'hot_spring';
        if (isset($tags['natural']) && $tags['natural'] === 'beach') return 'beach';
        if (isset($tags['waterway']) && $tags['waterway'] === 'waterfall') return 'waterfall';
        if (isset($tags['tourism'])) {
            return match($tags['tourism']) {
                'viewpoint'  => 'viewpoint',
                'attraction' => 'attraction',
                'camp_site'  => 'camp_site',
                'museum'     => 'museum',
                default      => 'other',
            };
        }
        if (isset($tags['amenity']) && $tags['amenity'] === 'spa') return 'hot_spring';
        if (isset($tags['leisure']) && $tags['leisure'] === 'park') return 'park';
        return 'other';
    }

    /**
     * Haversine 公式で2点間の距離 (km) を計算
     */
    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * asin(sqrt($a));
    }
}
