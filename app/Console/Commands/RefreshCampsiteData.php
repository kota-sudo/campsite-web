<?php

namespace App\Console\Commands;

use App\Models\Campsite;
use App\Models\CampsiteImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RefreshCampsiteData extends Command
{
    protected $signature   = 'campsite:refresh';
    protected $description = '最初の6件のキャンプサイトに本格的な名前・説明・座標・写真を設定する';

    // picsum.photos の自然/アウトドア系写真ID
    private array $photoIds = [
        [167, 28,  534],  // site 1: 森林・草原・川
        [15,  57,  360],  // site 2: 山・自然・渓谷
        [114, 292, 175],  // site 3: 森・草原・山
        [428, 672, 1015], // site 4: 高原・自然・キャンプ
        [337, 401, 218],  // site 5: 小屋・木・湖
        [543, 248, 679],  // site 6: 草原・夕暮れ・山岳
    ];

    private array $data = [
        [
            'name'            => '八ヶ岳 森のテントサイト',
            'description'     => '標高1,400mの八ヶ岳南麓に広がる、緑豊かなテントサイトです。白樺やカラマツの森に囲まれた静寂の空間で、澄んだ高原の空気と満天の星空をお楽しみいただけます。日中は八ヶ岳連峰の雄大な稜線を望みながらのんびりと過ごし、夜は焚き火を囲んで特別な時間を。周辺にはトレッキングコースや清流もあり、アウトドアをとことん満喫できる環境です。区画は十分な間隔を確保しており、プライベート感あふれるサイトです。',
            'type'            => 'tent',
            'capacity'        => 4,
            'price_per_night' => 4500,
            'address'         => '長野県諏訪郡原村',
            'latitude'        => 35.9234,
            'longitude'       => 138.2015,
        ],
        [
            'name'            => '奥多摩 渓谷リバーサイドキャンプ',
            'description'     => '清流・多摩川の源流域に位置するリバーサイドサイトです。サイトのすぐそばまで川が流れており、夏場は子どもたちの川遊びにも最適。せせらぎの音が自然の子守唄となり、日頃の疲れを癒してくれます。秋は紅葉が渓谷を彩り、一年を通じて異なる表情を楽しめます。サイトは川沿いに横並びで配置されており、どの区画からも水の音を間近に感じることができます。釣り愛好家にも人気のスポットです。',
            'type'            => 'tent',
            'capacity'        => 6,
            'price_per_night' => 5200,
            'address'         => '東京都西多摩郡奥多摩町',
            'latitude'        => 35.8089,
            'longitude'       => 139.0956,
        ],
        [
            'name'            => '富士山麓 オートキャンプ East',
            'description'     => '富士山の南麓、標高900mに広がる広大なオートキャンプ場です。駐車区画に直接乗り入れ可能で、荷物の多いファミリーや車中泊にも対応。全区画AC電源付きで、電気毛布やホットプレートが使えるため、春・秋・冬でも快適にキャンプを楽しめます。晴れた日には富士山の雄姿を目の前に望む絶景サイト。場内には充実した炊事棟・シャワー棟があり、初めてのキャンプにもおすすめです。',
            'type'            => 'auto',
            'capacity'        => 6,
            'price_per_night' => 7800,
            'address'         => '静岡県富士宮市猪之頭',
            'latitude'        => 35.3254,
            'longitude'       => 138.5678,
        ],
        [
            'name'            => '信州 蓼科高原オートキャンプ West',
            'description'     => '蓼科高原の爽やかな空気に包まれた、ゆとりある区画のオートキャンプサイトです。1区画あたり120㎡以上を確保しており、大型テントやタープを余裕を持って設営できます。全区画Wi-Fi対応で、テレワークキャンプにも対応。場内には源泉かけ流しの温泉施設（別途料金）もあり、キャンプの後は疲れた体を癒せます。標高1,600mの高原から望む朝の雲海は、一度見たら忘れられない絶景です。',
            'type'            => 'auto',
            'capacity'        => 8,
            'price_per_night' => 9000,
            'address'         => '長野県茅野市北山蓼科',
            'latitude'        => 36.0981,
            'longitude'       => 138.2934,
        ],
        [
            'name'            => '那須高原 バンガロー「木の葉」',
            'description'     => '那須高原の深い森の中に佇む、国産杉・ヒノキをふんだんに使った木造バンガローです。内部は12畳の広々としたロフト付き構造で、最大6名が快適に宿泊できます。テントや寝袋は不要で、布団や照明が完備。秋には周囲の紅葉が美しく、冬には那須岳の雪景色を眺めながらの滞在も格別です。バンガロー専用のBBQデッキが付いており、食材は場内の売店でも購入可能。手ぶらでも充実したアウトドア体験が楽しめます。',
            'type'            => 'bungalow',
            'capacity'        => 6,
            'price_per_night' => 14000,
            'address'         => '栃木県那須郡那須町高久乙',
            'latitude'        => 37.0123,
            'longitude'       => 140.0456,
        ],
        [
            'name'            => '北軽井沢 グランピングドーム「星の穹」',
            'description'     => '浅間山を望む北軽井沢の高原に立つ、直径8mの大型グランピングドームです。ダブルベッドとシングルベッドを完備し、最上級のリネンと羽毛布団で自然の中でも上質な眠りを。室内には冷暖房・テレビ・冷蔵庫・ウォシュレット付きトイレを設置。専任スタッフが薪の補充やBBQの準備を代行するフルサービス制。地元食材を使ったディナーコース（要予約・別料金）もご用意。特別な記念日やカップルでの旅行に最適な最高峰のアウトドア体験をご提供します。',
            'type'            => 'glamping',
            'capacity'        => 2,
            'price_per_night' => 35000,
            'address'         => '群馬県吾妻郡長野原町北軽井沢',
            'latitude'        => 36.4567,
            'longitude'       => 138.5012,
        ],
    ];

    public function handle(): int
    {
        $campsites = Campsite::orderBy('id')->take(6)->get();

        foreach ($campsites as $i => $campsite) {
            $d = $this->data[$i] ?? null;
            if (! $d) continue;

            $this->info("[$i] 更新中: {$d['name']}");

            $campsite->update([
                'name'            => $d['name'],
                'description'     => $d['description'],
                'address'         => $d['address'],
                'latitude'        => $d['latitude'],
                'longitude'       => $d['longitude'],
                'price_per_night' => $d['price_per_night'],
                'capacity'        => $d['capacity'],
            ]);

            // 既存画像を削除
            $campsite->images()->delete();

            // 写真をダウンロード
            foreach (($this->photoIds[$i] ?? []) as $order => $photoId) {
                $this->info("  写真ダウンロード中 (picsum ID: {$photoId})...");
                $url      = "https://picsum.photos/id/{$photoId}/900/600";
                $contents = $this->downloadImage($url);

                if ($contents === null) {
                    $this->warn("  スキップ (ダウンロード失敗)");
                    continue;
                }

                $filename = "campsites/site_{$campsite->id}_{$order}.jpg";
                Storage::disk('public')->put($filename, $contents);

                CampsiteImage::create([
                    'campsite_id' => $campsite->id,
                    'image_path'  => $filename,
                    'sort_order'  => $order,
                ]);

                $this->info("  保存: storage/app/public/{$filename}");
            }
        }

        $this->info('');
        $this->info('✅ 完了！最初の6件のキャンプサイトを更新しました。');
        return self::SUCCESS;
    }

    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(20)->get($url);
            return $response->successful() ? $response->body() : null;
        } catch (\Exception $e) {
            $this->warn("  エラー: {$e->getMessage()}");
            return null;
        }
    }
}
