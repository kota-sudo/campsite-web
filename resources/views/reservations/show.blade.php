<x-app-layout>

    <div class="bg-[#2d5a1b] border-b border-white/10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('reservations.index') }}" class="text-green-300 hover:text-white transition">← マイ予約</a>
                <span class="text-[#2d5a1b]">›</span>
                <span class="text-white font-medium">予約詳細</span>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->isNotEmpty())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        {{-- ステータスバナー --}}
        <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
            <div class="px-6 py-4
                {{ match($reservation->status) {
                    'confirmed' => 'bg-green-600',
                    'pending'   => 'bg-yellow-500',
                    'cancelled' => 'bg-gray-400',
                } }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-white text-2xl">
                            {{ match($reservation->status) { 'confirmed' => '✓', 'pending' => '⏳', 'cancelled' => '✕' } }}
                        </span>
                        <div>
                            <div class="text-white font-bold text-lg">
                                {{ match($reservation->status) { 'confirmed' => '予約確定', 'pending' => '確認中', 'cancelled' => 'キャンセル済み' } }}
                            </div>
                            <div class="text-white/80 text-xs">予約ID: #{{ $reservation->id }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- サイト情報 --}}
            <div class="bg-white px-6 py-4 flex items-center gap-4">
                @php $img = $reservation->campsite->images->first(); @endphp
                @if ($img)
                    <img src="{{ asset('storage/' . $img->image_path) }}"
                         class="w-20 h-16 object-cover rounded-lg flex-shrink-0" alt="">
                @endif
                <div class="min-w-0">
                    <div class="text-xs text-gray-500 mb-0.5">
                        {{ match($reservation->campsite->type) {
                            'tent'     => 'テントサイト',
                            'auto'     => 'オートキャンプ',
                            'bungalow' => 'バンガロー',
                            'glamping' => 'グランピング',
                        } }}
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 truncate">{{ $reservation->campsite->name }}</h2>
                    @if ($reservation->campsite->address)
                        <p class="text-xs text-gray-500 mt-0.5">📍 {{ $reservation->campsite->address }}</p>
                    @endif
                </div>
                <a href="{{ route('campsites.show', $reservation->campsite) }}"
                   class="ml-auto flex-shrink-0 text-xs text-[#2d5a1b] hover:underline whitespace-nowrap">
                    サイト詳細 →
                </a>
            </div>
        </div>

        {{-- 予約詳細カード --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-bold text-gray-700">予約内容</h3>
            </div>
            <dl class="divide-y divide-gray-50">
                <div class="flex justify-between items-center px-6 py-3 text-sm">
                    <dt class="text-gray-500">チェックイン</dt>
                    <dd class="font-semibold text-gray-900">{{ $reservation->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}</dd>
                </div>
                <div class="flex justify-between items-center px-6 py-3 text-sm">
                    <dt class="text-gray-500">チェックアウト</dt>
                    <dd class="font-semibold text-gray-900">{{ $reservation->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}</dd>
                </div>
                <div class="flex justify-between items-center px-6 py-3 text-sm">
                    <dt class="text-gray-500">宿泊数</dt>
                    <dd class="font-semibold text-gray-900">{{ $reservation->nights() }}泊</dd>
                </div>
                <div class="flex justify-between items-center px-6 py-3 text-sm">
                    <dt class="text-gray-500">人数</dt>
                    <dd class="font-semibold text-gray-900">{{ $reservation->num_guests }}名</dd>
                </div>
                @if ($reservation->notes)
                    <div class="flex justify-between items-start px-6 py-3 text-sm">
                        <dt class="text-gray-500">備考</dt>
                        <dd class="font-semibold text-gray-900 text-right max-w-xs">{{ $reservation->notes }}</dd>
                    </div>
                @endif
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50">
                    <dt class="font-bold text-gray-800">合計金額</dt>
                    <dd class="text-2xl font-bold text-[#2d5a1b]">¥{{ number_format($reservation->total_price) }}</dd>
                </div>
            </dl>
        </div>

        {{-- レビューカード --}}
        @if ($reservation->review)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="text-yellow-400">★</span> 投稿済みレビュー
                </h3>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-[#2d5a1b] rounded flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ $reservation->review->rating * 2 }}</span>
                    </div>
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="text-lg {{ $i <= $reservation->review->rating ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                        @endfor
                    </div>
                </div>
                @if ($reservation->review->comment)
                    <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">{{ $reservation->review->comment }}</p>
                @endif
                <form method="POST" action="{{ route('reviews.destroy', $reservation->review) }}" class="mt-3">
                    @csrf @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('レビューを削除しますか？')"
                            class="text-xs text-red-500 hover:text-red-700 hover:underline">
                        レビューを削除する
                    </button>
                </form>
            </div>
        @elseif ($reservation->isReviewable())
            <div class="bg-white rounded-xl border border-[#e07b39]/30 shadow-sm p-6"
                 x-data="{ rating: 0, hover: 0 }">
                <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-[#e07b39] flex items-center justify-center text-white text-xs">★</span>
                    レビューを投稿する
                </h3>
                <form method="POST" action="{{ route('reviews.store', $reservation) }}">
                    @csrf
                    <input type="hidden" name="rating" :value="rating">
                    <div class="mb-4">
                        <div class="flex gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                        @click="rating = {{ $i }}"
                                        @mouseenter="hover = {{ $i }}"
                                        @mouseleave="hover = 0"
                                        class="text-4xl transition-transform hover:scale-110"
                                        :class="(hover || rating) >= {{ $i }} ? 'text-yellow-400' : 'text-gray-200'">★</button>
                            @endfor
                            <span class="ml-2 text-sm text-gray-500 self-center"
                                  x-text="['','残念','普通','良い','とても良い','最高！'][rating]"></span>
                        </div>
                        <x-input-error :messages="$errors->get('rating')" class="mt-1" />
                    </div>
                    <textarea name="comment" rows="3" maxlength="500"
                              class="w-full rounded-lg border border-gray-200 text-sm text-gray-800 px-3 py-2 focus:border-[#2d5a1b] outline-none resize-none mb-3"
                              placeholder="ご滞在のご感想をお聞かせください（任意）">{{ old('comment') }}</textarea>
                    <button type="submit"
                            :disabled="rating === 0"
                            class="w-full h-11 rounded-lg bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        レビューを投稿する
                    </button>
                </form>
            </div>
        @endif

        {{-- キャンセルポリシー --}}
        @if (in_array($reservation->status, ['pending', 'confirmed']))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">キャンセルポリシー</h3>

                @if ($reservation->isCancellable())
                    {{-- キャンセル可能 --}}
                    <div class="flex items-start gap-2 mb-4 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <span class="font-medium text-green-700">無料キャンセル可</span>
                            <span class="text-gray-500 ml-1">
                                キャンセル期限:
                                <strong class="text-gray-700">{{ $reservation->cancellationDeadline()->isoFormat('M月D日(ddd)') }}</strong>
                                まで
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('reservations.destroy', $reservation) }}"
                          onsubmit="return confirm('予約をキャンセルしますか？\nキャンセル確認メールが届きます。\n\nこの操作は取り消せません。')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full h-11 rounded-lg border border-red-300 text-red-600 font-semibold text-sm hover:bg-red-50 transition-colors">
                            予約をキャンセルする
                        </button>
                    </form>
                @else
                    {{-- キャンセル期限切れ --}}
                    <div class="flex items-start gap-2 text-sm">
                        <svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <span class="font-medium text-red-600">キャンセル期限を過ぎています</span>
                            <p class="text-gray-500 mt-0.5">
                                無料キャンセルはチェックイン前日（{{ $reservation->cancellationDeadline()->isoFormat('M月D日') }}）までに限り受け付けています。
                                ご不明な点は<a href="{{ route('contact.create') }}" class="text-[#2d5a1b] underline">お問い合わせ</a>ください。
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-app-layout>
