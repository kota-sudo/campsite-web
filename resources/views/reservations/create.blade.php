<x-app-layout>
    {{-- パンくず --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('campsites.index') }}" class="text-[#2d5a1b] hover:underline">サイト一覧</a>
                <span class="text-gray-400">›</span>
                <a href="{{ route('campsites.show', $campsite) }}" class="text-[#2d5a1b] hover:underline">{{ $campsite->name }}</a>
                <span class="text-gray-400">›</span>
                <span class="text-gray-600">予約内容の確認</span>
            </nav>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ステップインジケーター --}}
        <div class="flex items-center justify-center gap-0 mb-8">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-[#2d5a1b] text-white text-sm font-bold flex items-center justify-center">1</div>
                <span class="ml-2 text-sm font-semibold text-[#2d5a1b] hidden sm:inline">日程・条件入力</span>
            </div>
            <div class="w-12 h-0.5 bg-[#e07b39] mx-2"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-[#e07b39] text-white text-sm font-bold flex items-center justify-center">2</div>
                <span class="ml-2 text-sm font-semibold text-[#e07b39] hidden sm:inline">内容確認</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200 mx-2"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 text-sm font-bold flex items-center justify-center">3</div>
                <span class="ml-2 text-sm text-gray-400 hidden sm:inline">予約確定</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_280px] gap-5">

            {{-- 予約フォーム --}}
            <div class="space-y-4">
                {{-- サイト情報 --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-base font-bold text-gray-900 mb-4">予約するサイト</h2>
                    <div class="flex gap-4">
                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if ($campsite->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $campsite->images->first()->image_path) }}"
                                     alt="{{ $campsite->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl">⛺</div>
                            @endif
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 mb-0.5">
                                {{ match($campsite->type) {
                                    'tent'     => 'テントサイト',
                                    'auto'     => 'オートキャンプ',
                                    'bungalow' => 'バンガロー',
                                    'glamping' => 'グランピング',
                                } }}
                            </div>
                            <h3 class="font-bold text-gray-900">{{ $campsite->name }}</h3>
                            @if (isset($plan) && $plan)
                                <p class="text-sm font-semibold text-[#e07b39] mt-0.5">{{ $plan->name }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">最大{{ $plan->capacity }}名 · ¥{{ number_format($plan->price_per_night) }}/泊</p>
                            @else
                                <p class="text-sm text-gray-500 mt-1">最大{{ $campsite->capacity }}名 · ¥{{ number_format($campsite->price_per_night) }}/泊</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 予約詳細 --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-base font-bold text-gray-900 mb-4">予約詳細</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <dt class="text-sm text-gray-500">チェックイン</dt>
                            <dd class="text-sm font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($checkIn)->isoFormat('YYYY年M月D日(ddd)') }}
                            </dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <dt class="text-sm text-gray-500">チェックアウト</dt>
                            <dd class="text-sm font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($checkOut)->isoFormat('YYYY年M月D日(ddd)') }}
                            </dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <dt class="text-sm text-gray-500">宿泊数</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ $nights }}泊</dd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <dt class="text-sm text-gray-500">人数</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ $guests }}名</dd>
                        </div>
                    </dl>
                </div>

                {{-- 備考 --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-base font-bold text-gray-900 mb-1">備考・特記事項</h2>
                    <p class="text-xs text-gray-500 mb-3">アレルギー・到着予定時間など（任意）</p>
                    <form method="POST" action="{{ route('reservations.store') }}" id="booking-form">
                        @csrf
                        <input type="hidden" name="campsite_id"    value="{{ $campsite->id }}">
                        @if (isset($plan) && $plan)
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        @endif
                        <input type="hidden" name="check_in_date"  value="{{ $checkIn }}">
                        <input type="hidden" name="check_out_date" value="{{ $checkOut }}">
                        <input type="hidden" name="num_guests"     value="{{ $guests }}">
                        <textarea name="notes" rows="3" maxlength="500"
                                  class="w-full rounded-lg border border-gray-200 text-gray-800 px-3 py-2 text-sm focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none resize-none"
                                  placeholder="任意">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </form>
                </div>

                <div class="flex gap-3">
                    <button type="submit" form="booking-form"
                            onclick="this.disabled=true; this.innerText='処理中...'; this.form.submit();"
                            class="flex-1 h-12 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm transition-colors">
                        ⛺ 予約を確定する
                    </button>
                    <a href="{{ route('campsites.show', $campsite) }}"
                       class="flex-1 h-12 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center justify-center transition-colors">
                        戻る
                    </a>
                </div>
            </div>

            {{-- 料金サマリー --}}
            <div class="lg:sticky lg:top-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-[#2d5a1b] px-5 py-4">
                        <div class="text-green-200 text-xs mb-0.5">お支払い金額</div>
                        <div class="text-white text-2xl font-bold">¥{{ number_format($totalPrice) }}</div>
                        <div class="text-green-200 text-xs mt-1">税込 · {{ $nights }}泊分</div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between text-sm">
                            @if (isset($plan) && $plan)
                                <span class="text-gray-500">¥{{ number_format($plan->price_per_night) }} × {{ $nights }}泊</span>
                            @else
                                <span class="text-gray-500">¥{{ number_format($campsite->price_per_night) }} × {{ $nights }}泊</span>
                            @endif
                            <span class="font-medium text-gray-900">¥{{ number_format($totalPrice) }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 flex justify-between text-sm font-bold">
                            <span class="text-gray-900">合計</span>
                            <span class="text-gray-900">¥{{ number_format($totalPrice) }}</span>
                        </div>
                    </div>
                    <div class="px-5 pb-5 space-y-2">
                        <div class="flex items-start gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            チェックイン前まで無料キャンセル
                        </div>
                        <div class="flex items-start gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            即時予約確定
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
