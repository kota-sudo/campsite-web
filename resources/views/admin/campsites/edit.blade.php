<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.campsites.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← サイト一覧</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">編集: {{ $campsite->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @include('admin.campsites._form', [
                'campsite'  => $campsite,
                'amenities' => $amenities,
                'action'    => route('admin.campsites.update', $campsite),
                'method'    => 'PUT',
            ])

            {{-- 特別価格管理セクション --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">特別価格設定</h3>
                <p class="text-xs text-gray-500 mb-5">GW・夏休みなど繁忙期の特別料金を設定できます。期間が重複した場合は高い方が優先されます。</p>

                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- 既存の特別価格一覧 --}}
                @if ($campsite->prices->isNotEmpty())
                    <div class="mb-5 space-y-2">
                        @foreach ($campsite->prices as $price)
                            <div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                                <div class="flex items-center gap-3 text-sm">
                                    @if ($price->label)
                                        <span class="font-semibold text-amber-800">{{ $price->label }}</span>
                                        <span class="text-amber-600">|</span>
                                    @endif
                                    <span class="text-gray-700">
                                        {{ $price->start_date->format('Y/m/d') }} 〜 {{ $price->end_date->format('Y/m/d') }}
                                    </span>
                                    <span class="font-bold text-amber-700">¥{{ number_format($price->price_per_night) }}/泊</span>
                                </div>
                                <form method="POST"
                                      action="{{ route('admin.campsites.prices.destroy', [$campsite, $price]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('削除しますか？')"
                                            class="text-xs text-red-500 hover:text-red-700 transition">削除</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- 追加フォーム --}}
                <form method="POST" action="{{ route('admin.campsites.prices.store', $campsite) }}"
                      class="grid grid-cols-2 gap-3">
                    @csrf
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">ラベル（例: GW特別料金）</label>
                        <input type="text" name="label" placeholder="任意"
                               class="w-full h-10 rounded-lg border border-gray-200 px-3 text-sm focus:border-[#2d5a1b] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">開始日</label>
                        <input type="date" name="start_date" required
                               class="w-full h-10 rounded-lg border border-gray-200 px-3 text-sm focus:border-[#2d5a1b] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">終了日</label>
                        <input type="date" name="end_date" required
                               class="w-full h-10 rounded-lg border border-gray-200 px-3 text-sm focus:border-[#2d5a1b] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">1泊あたりの料金（¥）</label>
                        <input type="number" name="price_per_night" min="100" step="100" required
                               placeholder="{{ $campsite->price_per_night }}"
                               class="w-full h-10 rounded-lg border border-gray-200 px-3 text-sm focus:border-[#2d5a1b] outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full h-10 bg-[#e07b39] hover:bg-[#c4621a] text-white font-semibold text-sm rounded-lg transition-colors">
                            追加する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
