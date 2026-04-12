<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    private const SESSION_KEY = 'chatbot_history';
    private const MAX_HISTORY = 20; // 最大メッセージ保持数

    private string $systemPrompt = <<<'PROMPT'
あなたは日本のキャンプ場予約サイト「CampsiteWeb」のAIサポートスタッフ「キャンプくん」です。
ユーザーのご質問に日本語で丁寧かつ簡潔にお答えください。

## サービス概要
- テントサイト・オートキャンプ・バンガロー・グランピングを予約できるサービス
- 全国25か所以上のキャンプサイトを掲載
- 料金は1泊あたり¥3,000〜¥35,000程度

## よくある質問への回答

### 予約方法
1. サイト一覧から気になるキャンプサイトを選ぶ
2. 詳細ページでチェックイン・アウト日と人数を入力
3. 「予約内容を確認する」→内容確認→「予約を確定する」

### キャンセルポリシー
- チェックイン前日まで：無料キャンセル可能
- キャンセルはマイ予約ページから操作できます

### チェックイン・アウト時間
- チェックイン：15:00〜
- チェックアウト：〜11:00
（サイトにより異なる場合があります）

### 支払い方法
- 現時点ではオンライン決済機能は実装中です。詳細はお問い合わせください

### レビュー
- チェックアウト後にマイ予約または詳細ページからレビューを投稿できます
- レビューは1〜5点の星評価とコメントで構成されます

### アメニティ
- トイレ・シャワー・炊事場・電源・Wi-Fi・BBQコンロ・焚き火台・駐車場・ゴミ捨て場・売店
- 各サイトの設備はサイト詳細ページで確認できます

### ペット
- ペット可否はサイトにより異なります。詳細ページまたは問い合わせをご確認ください

### お問い合わせ
- 複雑なご要望やシステムに関するご質問は、お問い合わせフォームをご利用ください
- URLは /contact です

## 回答スタイル
- 簡潔に3〜5文でまとめる
- 箇条書きを適宜使う
- わからない場合は正直に伝え、お問い合わせフォームへ誘導する
- 絵文字は1メッセージに1〜2個まで
PROMPT;

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $userMessage = $request->input('message');

        // セッションから会話履歴を取得
        $history = session(self::SESSION_KEY, []);

        // ANTHROPIC_API_KEY があればClaude APIを使用、なければルールベース
        $apiKey = config('services.anthropic.key');

        if ($apiKey) {
            $reply = $this->callClaudeApi($userMessage, $history, $apiKey);
        } else {
            $reply = $this->ruleBasedReply($userMessage);
        }

        // 会話履歴を更新（最大件数を超えたら古いものを削除）
        $history[] = ['role' => 'user',      'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        if (count($history) > self::MAX_HISTORY) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }

        session([self::SESSION_KEY => $history]);

        return response()->json(['reply' => $reply]);
    }

    public function reset(): JsonResponse
    {
        session()->forget(self::SESSION_KEY);
        return response()->json(['ok' => true]);
    }

    private function callClaudeApi(string $userMessage, array $history, string $apiKey): string
    {
        // historyにユーザーメッセージを追加してAPIへ送信
        $messages = array_merge($history, [
            ['role' => 'user', 'content' => $userMessage],
        ]);

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 512,
                    'system'     => $this->systemPrompt,
                    'messages'   => $messages,
                ]);

            if ($response->successful()) {
                return $response->json('content.0.text')
                    ?? 'すみません、うまく回答できませんでした。';
            }

            return $this->ruleBasedReply($userMessage);
        } catch (\Exception) {
            return $this->ruleBasedReply($userMessage);
        }
    }

    private function ruleBasedReply(string $message): string
    {
        $msg = mb_strtolower($message);

        // キャンセル
        if (preg_match('/キャンセル|cancel|取り消/', $msg)) {
            return "キャンセルについてですね 🏕️\n\nチェックイン前日まで**無料でキャンセル**できます。\nマイ予約ページ → 予約詳細 → 「予約をキャンセルする」から操作してください。";
        }

        // 予約方法
        if (preg_match('/予約.*(方法|仕方|やり方|手順)|どうやって.*予約|予約.*方法/', $msg)) {
            return "予約の手順はこちらです ✨\n\n1. サイト一覧で気になるサイトを選ぶ\n2. 詳細ページで日程・人数を入力\n3. 「予約内容を確認する」→「予約を確定する」\n\nご不明な点があればお気軽にどうぞ！";
        }

        // 料金・価格
        if (preg_match('/料金|価格|値段|いくら|費用|コスト/', $msg)) {
            return "料金はサイトにより異なります 💰\n\n- テントサイト：¥3,000〜¥6,000/泊\n- オートキャンプ：¥6,000〜¥10,000/泊\n- バンガロー：¥10,000〜¥15,000/泊\n- グランピング：¥20,000〜¥35,000/泊\n\n各サイトの詳細ページで正確な料金をご確認ください。";
        }

        // チェックイン・アウト
        if (preg_match('/チェックイン|チェックアウト|何時|時間/', $msg)) {
            return "チェックイン・アウトの時間についてです 🕐\n\n- **チェックイン**：15:00〜\n- **チェックアウト**：〜11:00\n\nサイトにより異なる場合がありますので、詳細ページでご確認ください。";
        }

        // アメニティ・設備
        if (preg_match('/設備|アメニティ|トイレ|シャワー|電源|wifi|wi-fi|炊事|BBQ|焚き火|駐車/', $msg)) {
            return "設備・アメニティについてですね 🔧\n\n各サイトによって異なりますが、主な設備：\nトイレ・シャワー・炊事場・電源・Wi-Fi・BBQコンロ・焚き火台・駐車場・ゴミ捨て場・売店\n\nサイト詳細ページの「設備・サービス」欄でご確認いただけます。";
        }

        // ペット
        if (preg_match('/ペット|犬|猫|動物/', $msg)) {
            return "ペットについてのご質問ですね 🐕\n\nペット可否はサイトにより異なります。詳細ページの説明文をご確認いただくか、直接お問い合わせフォームからご質問ください。\n\nお問い合わせは → /contact";
        }

        // レビュー
        if (preg_match('/レビュー|口コミ|評価|感想/', $msg)) {
            return "レビューについてですね ⭐\n\nチェックアウト後に以下の場所からレビューを投稿できます：\n- マイ予約 → 予約詳細\n- キャンプサイト詳細ページ\n\n1〜5点の星評価とコメントで投稿できます。";
        }

        // お問い合わせ
        if (preg_match('/問い合わせ|連絡|メール|contact|サポート/', $msg)) {
            return "お問い合わせについてですね 📨\n\nお問い合わせフォームからご連絡いただけます。\n→ /contact\n\n担当スタッフが対応いたします。お気軽にどうぞ！";
        }

        // 挨拶
        if (preg_match('/^(こんにちは|こんばんは|おはよう|hello|hi|はじめまして|よろしく)/', $msg)) {
            return "こんにちは！CampsiteWebサポートの「キャンプくん」です ⛺\n\n予約・キャンセル・設備など、なんでもお気軽にご質問ください！";
        }

        // ありがとう
        if (preg_match('/ありがとう|ありがと|感謝|助かり/', $msg)) {
            return "ご利用ありがとうございます！😊\n他にご不明な点があればいつでもどうぞ。\n素敵なキャンプをお楽しみください 🌿";
        }

        // デフォルト
        return "ご質問ありがとうございます。\n\nこちらのご質問については詳しい情報をお伝えできないかもしれません。\n\n**お問い合わせフォーム**（/contact）からお問い合わせいただくと、スタッフが詳しくご案内いたします。\n\n他にご不明な点はありますか？";
    }
}
