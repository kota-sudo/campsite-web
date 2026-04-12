<x-app-layout>

    {{-- ステップバー --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-center gap-0">
                @foreach ([
                    [1, '日程・条件入力', 'done'],
                    [2, '内容確認',       'done'],
                    [3, '予約確定',       'active'],
                ] as [$n, $label, $state])
                    @if ($n > 1)
                        <div class="w-12 h-0.5 mx-2 {{ $state === 'active' ? 'bg-[#e07b39]' : 'bg-[#e07b39]' }}"></div>
                    @endif
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full text-white text-sm font-bold flex items-center justify-center
                            {{ $state === 'active' ? 'bg-green-500' : 'bg-[#e07b39]' }}">
                            {{ $state === 'done' ? '✓' : $n }}
                        </div>
                        <span class="ml-2 text-sm font-semibold hidden sm:inline
                            {{ $state === 'active' ? 'text-green-600' : 'text-[#e07b39]' }}">
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- 完了メッセージ --}}
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">🏕️</div>
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-100 mb-4">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">予約が確定しました！</h1>
            <p class="text-gray-500 text-sm">あとは荷造りするだけ。最高のキャンプを楽しんできてください。</p>
            <p class="text-xs text-gray-400 mt-1">予約番号 <span class="font-bold text-[#2d5a1b]">#{{ $reservation->id }}</span></p>
        </div>

        {{-- 予約サマリーカード --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-5">

            {{-- サイト画像 + 名前 --}}
            <div class="flex items-center gap-4 p-5 border-b border-gray-100">
                @php $img = $reservation->campsite->images->first(); @endphp
                @if ($img)
                    <img src="{{ asset('storage/' . $img->image_path) }}"
                         class="w-20 h-16 object-cover rounded-xl flex-shrink-0" alt="">
                @else
                    <div class="w-20 h-16 bg-gray-100 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">⛺</div>
                @endif
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 mb-0.5">
                        {{ match($reservation->campsite->type) {
                            'tent'     => 'テントサイト',
                            'auto'     => 'オートキャンプ',
                            'bungalow' => 'バンガロー',
                            'glamping' => 'グランピング',
                        } }}
                    </p>
                    <h2 class="text-lg font-bold text-gray-900 leading-snug">{{ $reservation->campsite->name }}</h2>
                    @if ($reservation->campsite->address)
                        <p class="text-xs text-gray-500 mt-0.5">📍 {{ $reservation->campsite->address }}</p>
                    @endif
                </div>
            </div>

            {{-- 日程・人数・金額 --}}
            <dl class="divide-y divide-gray-50 text-sm">
                <div class="flex justify-between items-center px-5 py-3">
                    <dt class="text-gray-500">チェックイン</dt>
                    <dd class="font-semibold text-gray-900">
                        {{ $reservation->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}
                        <span class="text-xs text-gray-400 ml-1">15:00〜</span>
                    </dd>
                </div>
                <div class="flex justify-between items-center px-5 py-3">
                    <dt class="text-gray-500">チェックアウト</dt>
                    <dd class="font-semibold text-gray-900">
                        {{ $reservation->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}
                        <span class="text-xs text-gray-400 ml-1">〜11:00</span>
                    </dd>
                </div>
                <div class="flex justify-between items-center px-5 py-3">
                    <dt class="text-gray-500">宿泊数 / 人数</dt>
                    <dd class="font-semibold text-gray-900">{{ $reservation->nights() }}泊 / {{ $reservation->num_guests }}名</dd>
                </div>
                <div class="flex justify-between items-center px-5 py-4 bg-gray-50">
                    <dt class="font-bold text-gray-800">お支払い合計</dt>
                    <dd class="text-2xl font-bold text-[#2d5a1b]">¥{{ number_format($reservation->total_price) }}</dd>
                </div>
            </dl>
        </div>

        {{-- インフォメーション --}}
        <div class="bg-[#f0f7eb] border border-[#c8ddb8] rounded-xl p-4 mb-6 text-sm text-[#2d5a1b] space-y-1.5">
            <div class="flex items-start gap-2">
                <span class="mt-0.5">🌲</span>
                <span>確認メールを登録メールアドレスにお送りしました。</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="mt-0.5">✅</span>
                <span>チェックイン前日までキャンセル無料でご変更いただけます。</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="mt-0.5">🔔</span>
                <span>チェックイン3日前にリマインダーメールをお送りします。</span>
            </div>
        </div>

        {{-- アクションボタン --}}
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('reservations.show', $reservation) }}"
               class="flex items-center justify-center h-12 rounded-xl border border-gray-200 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors">
                予約詳細を見る
            </a>
            <a href="{{ route('campsites.index') }}"
               class="flex items-center justify-center h-12 rounded-xl bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold text-sm transition-colors">
                他のサイトを探す
            </a>
        </div>
    </div>
</x-app-layout>
