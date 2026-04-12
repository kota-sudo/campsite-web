<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Campsite;
use App\Models\CampsiteImage;
use Illuminate\Database\Seeder;

class CampsiteSeeder extends Seeder
{
    public function run(): void
    {
        $amenities = Amenity::all();

        $sites = [
            [
                'name'            => '森のテントサイトA',
                'description'     => '緑豊かな森に囲まれた静かなテントサイトです。夜は満天の星空をお楽しみいただけます。',
                'type'            => 'tent',
                'capacity'        => 4,
                'price_per_night' => 4000,
                'is_active'       => true,
                'amenities'       => ['トイレ', '炊事場', '駐車場', 'ゴミ捨て場'],
            ],
            [
                'name'            => '川沿いテントサイトB',
                'description'     => 'せせらぎの音に癒されるリバーサイドサイト。夏は川遊びも楽しめます。',
                'type'            => 'tent',
                'capacity'        => 6,
                'price_per_night' => 5000,
                'is_active'       => true,
                'amenities'       => ['トイレ', 'シャワー', '炊事場', '駐車場'],
            ],
            [
                'name'            => 'オートキャンプサイト East',
                'description'     => '車の横にテントを張れるオートサイト。電源付きで快適にお過ごしいただけます。',
                'type'            => 'auto',
                'capacity'        => 6,
                'price_per_night' => 7000,
                'is_active'       => true,
                'amenities'       => ['トイレ', 'シャワー', '炊事場', '電源', '駐車場', 'ゴミ捨て場'],
            ],
            [
                'name'            => 'オートキャンプサイト West',
                'description'     => '広めのオートサイト。ファミリーにも人気の設備充実エリアです。',
                'type'            => 'auto',
                'capacity'        => 8,
                'price_per_night' => 8000,
                'is_active'       => true,
                'amenities'       => ['トイレ', 'シャワー', '炊事場', '電源', 'Wi-Fi', '駐車場', '売店'],
            ],
            [
                'name'            => 'バンガロー「杉」',
                'description'     => '杉材をふんだんに使った温かみのあるバンガロー。6名までご利用いただけます。',
                'type'            => 'bungalow',
                'capacity'        => 6,
                'price_per_night' => 12000,
                'is_active'       => true,
                'amenities'       => ['トイレ', 'シャワー', '炊事場', '電源', '駐車場'],
            ],
            [
                'name'            => 'グランピングドーム α',
                'description'     => '高級ベッドやアメニティが揃ったラグジュアリーなドームテント。特別な思い出を。',
                'type'            => 'glamping',
                'capacity'        => 2,
                'price_per_night' => 30000,
                'is_active'       => true,
                'amenities'       => ['トイレ', 'シャワー', '電源', 'Wi-Fi', 'BBQコンロ', '駐車場'],
            ],
        ];

        foreach ($sites as $data) {
            $amenityNames = $data['amenities'];
            unset($data['amenities']);

            $campsite = Campsite::firstOrCreate(['name' => $data['name']], $data);

            $ids = $amenities->whereIn('name', $amenityNames)->pluck('id');
            $campsite->amenities()->sync($ids);
        }
    }
}
