<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('host.campsites.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← サイト一覧</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">編集: {{ $campsite->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @include('host.campsites._form', [
                'campsite'  => $campsite,
                'amenities' => $amenities,
                'action'    => route('host.campsites.update', $campsite),
                'method'    => 'PUT',
            ])

            {{-- 特別料金・日付別価格管理 --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">特別料金の設定</h3>
                    <p class="text-xs text-gray-500 mt-0.5">GW・お盆など特定期間に異なる料金を設定できます</p>
                </div>

                @if (session('success'))
                    <div class="bg-green-50 px-6 py-3 text-sm text-green-700">{{ session('success') }}</div>
                @endif

                {{-- 既存の料金ルール --}}
                @if ($campsite->prices->isNotEmpty())
                    <div class="divide-y divide-[#e0d8cc]">
                        @foreach ($campsite->prices as $price)
                            <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                                <div>
                                    @if ($price->label)
                                        <p class="text-sm font-semibold text-gray-800">{{ $price->label }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $price->start_date->isoFormat('YYYY年M月D日') }} 〜 {{ $price->end_date->isoFormat('M月D日') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-bold text-[#2d5a1b]">¥{{ number_format($price->price_per_night) }}/泊</span>
                                    <form method="POST"
                                          action="{{ route('host.campsites.prices.destroy', [$campsite, $price]) }}"
                                          onsubmit="return confirm('この特別料金を削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                            削除
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-6 py-4 text-sm text-gray-400">特別料金はまだ設定されていません。</p>
                @endif

                {{-- 追加フォーム --}}
                <div class="border-t border-gray-100 px-6 py-5 bg-gray-50">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">新しい特別料金を追加</p>
                    <form method="POST" action="{{ route('host.campsites.prices.store', $campsite) }}"
                          class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_1fr_1fr_auto] gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">ラベル（任意）</label>
                            <input type="text" name="label" placeholder="GW特別料金"
                                   maxlength="50"
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">開始日</label>
                            <input type="date" name="start_date" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">終了日</label>
                            <input type="date" name="end_date" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">料金（円/泊）</label>
                            <input type="number" name="price_per_night" required
                                   min="100" step="100"
                                   placeholder="{{ $campsite->price_per_night }}"
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <button type="submit"
                                class="h-9 px-4 bg-[#2d5a1b] hover:bg-[#1c3a0e] text-white font-bold text-sm rounded-lg transition-colors whitespace-nowrap">
                            追加
                        </button>
                    </form>
                    @error('price_per_night')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    @error('start_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    @error('end_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- プラン管理 --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">プランの管理</h3>
                    <p class="text-xs text-gray-500 mt-0.5">テントサイトA・バンガロー棟など区画ごとにプランを設定できます。プランがあると予約ページに選択肢として表示されます。</p>
                </div>

                {{-- 既存プラン --}}
                @if ($campsite->plans->isNotEmpty())
                    @foreach ($campsite->plans as $plan)
                        <div class="border-b border-gray-100 last:border-0" x-data="{ editing: false }">
                            {{-- 表示行 --}}
                            <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50">
                                {{-- 画像サムネ --}}
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                    @if ($plan->image_path)
                                        <img src="{{ asset('storage/' . $plan->image_path) }}" alt="{{ $plan->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-xl">⛺</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $plan->name }}</p>
                                        @if (!$plan->is_active)
                                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">非公開</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">最大{{ $plan->capacity }}名・{{ $plan->stock }}区画 / ¥{{ number_format($plan->price_per_night) }}泊</p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button @click="editing = !editing"
                                            class="text-xs text-[#2d5a1b] hover:text-[#1c3a0e] font-medium px-2 py-1 rounded hover:bg-green-50 transition-colors">
                                        編集
                                    </button>
                                    <form method="POST"
                                          action="{{ route('host.campsites.plans.destroy', [$campsite, $plan]) }}"
                                          onsubmit="return confirm('「{{ $plan->name }}」を削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                            削除
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- 編集フォーム (折りたたみ) --}}
                            <div x-show="editing" x-cloak class="px-5 py-4 bg-green-50 border-t border-green-100">
                                <form method="POST"
                                      action="{{ route('host.campsites.plans.update', [$campsite, $plan]) }}"
                                      enctype="multipart/form-data"
                                      class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1 font-semibold">プラン名 *</label>
                                            <input type="text" name="name" required maxlength="100"
                                                   value="{{ $plan->name }}"
                                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1 font-semibold">料金（円/泊） *</label>
                                            <input type="number" name="price_per_night" required min="100" step="100"
                                                   value="{{ $plan->price_per_night }}"
                                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1 font-semibold">最大人数 *</label>
                                            <input type="number" name="capacity" required min="1" max="50"
                                                   value="{{ $plan->capacity }}"
                                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1 font-semibold">区画数（同時予約可能数） *</label>
                                            <input type="number" name="stock" required min="1" max="99"
                                                   value="{{ $plan->stock }}"
                                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">説明文（任意）</label>
                                        <textarea name="description" rows="2" maxlength="500"
                                                  class="w-full rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 py-2 focus:border-[#2d5a1b] outline-none resize-none">{{ $plan->description }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">プラン画像（任意・更新する場合のみ）</label>
                                        <input type="file" name="image" accept="image/*"
                                               class="text-xs text-gray-600">
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1"
                                                   {{ $plan->is_active ? 'checked' : '' }}
                                                   class="rounded text-[#e07b39] focus:ring-[#e07b39]">
                                            <span class="text-xs text-gray-700">公開する</span>
                                        </label>
                                        <button type="submit"
                                                class="ml-auto px-4 py-1.5 bg-[#2d5a1b] hover:bg-[#1c3a0e] text-white text-xs font-bold rounded-lg transition-colors">
                                            更新する
                                        </button>
                                        <button type="button" @click="editing = false"
                                                class="px-3 py-1.5 text-gray-500 hover:text-gray-700 text-xs transition-colors">
                                            キャンセル
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="px-6 py-4 text-sm text-gray-400">プランはまだ登録されていませ���。</p>
                @endif

                {{-- 新規プラン追加フォーム --}}
                <div class="border-t border-gray-100 px-6 py-5 bg-gray-50">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">新しいプランを追加</p>
                    <form method="POST"
                          action="{{ route('host.campsites.plans.store', $campsite) }}"
                          enctype="multipart/form-data"
                          class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">プラン名 *</label>
                                <input type="text" name="name" required maxlength="100"
                                       placeholder="例: テントサイトA、コテージ棟1"
                                       class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">料金（円/泊） *</label>
                                <input type="number" name="price_per_night" required min="100" step="100"
                                       placeholder="{{ $campsite->price_per_night }}"
                                       class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">最大人数 *</label>
                                <input type="number" name="capacity" required min="1" max="50"
                                       value="{{ $campsite->capacity }}"
                                       class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">区画数（同時予約可能数） *</label>
                                <input type="number" name="stock" required min="1" max="99" value="1"
                                       class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">説明文（任意）</label>
                            <textarea name="description" rows="2" maxlength="500"
                                      placeholder="プランの特徴・含まれる設備など"
                                      class="w-full rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 py-2 focus:border-[#2d5a1b] outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">プラン画像（任意）</label>
                            <input type="file" name="image" accept="image/*"
                                   class="text-xs text-gray-600">
                        </div>
                        <button type="submit"
                                class="px-5 py-2 bg-[#2d5a1b] hover:bg-[#1c3a0e] text-white font-bold text-sm rounded-lg transition-colors">
                            プランを追加する
                        </button>
                    </form>
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    @error('price_per_night')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ブラックアウト日（予約不可期間）管理 --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">予約不可期間の設定</h3>
                    <p class="text-xs text-gray-500 mt-0.5">メンテナンス・貸切・休業など、予約を受け付けたくない期間を設定します。カレンダーにグレーで表示されます。</p>
                </div>

                {{-- 既存ブラックアウト --}}
                @if ($campsite->blockouts->isNotEmpty())
                    <div class="divide-y divide-[#e0d8cc]">
                        @foreach ($campsite->blockouts as $blockout)
                            <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                                <div>
                                    @if ($blockout->reason)
                                        <p class="text-sm font-semibold text-gray-800">{{ $blockout->reason }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $blockout->start_date->isoFormat('YYYY年M月D日') }}
                                        〜
                                        {{ $blockout->end_date->isoFormat('M月D日') }}
                                    </p>
                                </div>
                                <form method="POST"
                                      action="{{ route('host.campsites.blockouts.destroy', [$campsite, $blockout]) }}"
                                      onsubmit="return confirm('この予約不可期間を削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                        削除
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-6 py-4 text-sm text-gray-400">予約不可期間は設定されていません。</p>
                @endif

                {{-- 追加フォーム --}}
                <div class="border-t border-gray-100 px-6 py-5 bg-gray-50">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">新しい予約不可期間を追加</p>
                    <form method="POST"
                          action="{{ route('host.campsites.blockouts.store', $campsite) }}"
                          class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_1fr_auto] gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">理由（任意）</label>
                            <input type="text" name="reason" placeholder="例: メンテナンス"
                                   maxlength="100"
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">開始日</label>
                            <input type="date" name="start_date" required
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">終了日</label>
                            <input type="date" name="end_date" required
                                   class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-sm px-3 focus:border-[#2d5a1b] outline-none">
                        </div>
                        <button type="submit"
                                class="h-9 px-4 bg-gray-700 hover:bg-gray-900 text-white font-bold text-sm rounded-lg transition-colors whitespace-nowrap">
                            追加
                        </button>
                    </form>
                    @error('start_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    @error('end_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
