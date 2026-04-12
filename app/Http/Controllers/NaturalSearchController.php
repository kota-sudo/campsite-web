<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NaturalSearchController extends Controller
{
    public function parse(Request $request): JsonResponse
    {
        $request->validate(['query' => ['required', 'string', 'max:300']]);

        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            return response()->json(['error' => 'no_key'], 200);
        }

        $amenities     = Amenity::pluck('name', 'id');
        $amenityList   = $amenities->map(fn ($n, $id) => "{$id}:{$n}")->implode(', ');

        $systemPrompt = <<<PROMPT
あなたはキャンプサイト検索の自然言語クエリをJSONフィルターに変換するアシスタントです。
ユーザーのクエリから以下のフィールドを抽出し、JSONのみを返してください（説明不要）。

フィールド定義:
- type: "tent"|"auto"|"bungalow"|"glamping"|null
- guests: 整数|null (何名か)
- price_max: 整数|null (1泊上限円)
- price_min: 整数|null (1泊下限円)
- min_rating: "3.0"|"3.5"|"4.0"|"4.5"|null
- amenity_ids: 整数配列|[] (以下のIDリストから一致するもの: {$amenityList})

例: {"type":"glamping","guests":2,"price_max":20000,"price_min":null,"min_rating":"4.0","amenity_ids":[]}
PROMPT;

        try {
            $res = Http::timeout(15)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 256,
                    'system'     => $systemPrompt,
                    'messages'   => [['role' => 'user', 'content' => $request->query]],
                ]);

            if (!$res->successful()) {
                return response()->json(['error' => 'api_error'], 200);
            }

            $text = $res->json('content.0.text', '{}');
            // JSON部分だけ抽出（前後に余計な文字があっても対応）
            preg_match('/\{.*\}/s', $text, $m);
            $filters = json_decode($m[0] ?? '{}', true) ?? [];

            return response()->json(['filters' => $filters]);
        } catch (\Exception) {
            return response()->json(['error' => 'exception'], 200);
        }
    }
}
