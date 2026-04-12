<x-app-layout>
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('campsites.index') }}" class="text-[#2d5a1b] hover:underline">サイト一覧</a>
                <span class="text-gray-400">›</span>
                <span class="text-gray-600">サイト比較</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-[#2d5a1b]">サイト比較</h1>
            <a href="{{ route('campsites.index') }}"
               class="text-sm text-[#2d5a1b] hover:underline">← 一覧に戻る</a>
        </div>

        @php
            $colCount = $campsites->count();
            $gridClass = $colCount === 2 ? 'grid-cols-[200px_1fr_1fr]' : 'grid-cols-[200px_1fr_1fr_1fr]';
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="w-full text-sm">

                {{-- サイト画像・名前ヘッダー --}}
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="p-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-44 bg-gray-50">
                            比較項目
                        </th>
                        @foreach ($campsites as $c)
                            <th class="p-4 text-center align-top">
                                @if ($c->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $c->images->first()->image_path) }}"
                                         alt="{{ $c->name }}"
                                         class="w-full h-36 object-cover rounded-lg mb-3">
                                @else
                                    <div class="w-full h-36 bg-gray-100 rounded-lg mb-3 flex items-center justify-center text-3xl">⛺</div>
                                @endif
                                <a href="{{ route('campsites.show', $c) }}"
                                   class="font-bold text-[#2d5a1b] hover:underline text-base block leading-snug mb-1">
                                    {{ $c->name }}
                                </a>
                                <p class="text-xs text-gray-500 truncate">{{ $c->address }}</p>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">

                    {{-- タイプ --}}
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-medium text-gray-600 bg-gray-50 text-xs">タイプ</td>
                        @foreach ($campsites as $c)
                            @php
                                $typeLabel = match($c->type) {
                                    'tent' => 'テントサイト', 'auto' => 'オートキャンプ',
                                    'bungalow' => 'バンガロー', 'glamping' => 'グランピング',
                                };
                            @endphp
                            <td class="p-4 text-center text-gray-700">{{ $typeLabel }}</td>
                        @endforeach
                    </tr>

                    {{-- 料金 --}}
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-medium text-gray-600 bg-gray-50 text-xs">基本料金（1泊）</td>
                        @foreach ($campsites as $c)
                            <td class="p-4 text-center">
                                <span class="text-xl font-bold text-gray-900">¥{{ number_format($c->price_per_night) }}</span>
                                @if ($c->hasWeekendSurcharge())
                                    <div class="text-xs text-amber-600 mt-0.5">
                                        土日 ¥{{ number_format($c->weekendPrice()) }}
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- 定員 --}}
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-medium text-gray-600 bg-gray-50 text-xs">定員</td>
                        @foreach ($campsites as $c)
                            <td class="p-4 text-center text-gray-700">最大 {{ $c->capacity }}名</td>
                        @endforeach
                    </tr>

                    {{-- 評価 --}}
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-medium text-gray-600 bg-gray-50 text-xs">総合評価</td>
                        @foreach ($campsites as $c)
                            <td class="p-4 text-center">
                                @if ($c->reviews->isNotEmpty())
                                    @php $score = $c->averageRatingOutOf10(); @endphp
                                    <div class="inline-flex items-center gap-2">
                                        <span class="w-10 h-10 bg-[#2d5a1b] rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                            {{ $score }}
                                        </span>
                                        <div class="text-left">
                                            <div class="text-xs font-semibold text-gray-700">{{ $c->ratingLabel() }}</div>
                                            <div class="text-xs text-gray-400">{{ $c->reviews->count() }}件</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">レビューなし</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- アメニティ --}}
                    @foreach ($allAmenities as $amenity)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 text-gray-600 bg-gray-50 text-xs">{{ $amenity->name }}</td>
                            @foreach ($campsites as $c)
                                <td class="p-4 text-center">
                                    @if ($c->amenities->contains('id', $amenity->id))
                                        <span class="text-green-500 text-lg">✓</span>
                                    @else
                                        <span class="text-gray-200 text-lg">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    {{-- CTA --}}
                    <tr class="bg-gray-50">
                        <td class="p-4"></td>
                        @foreach ($campsites as $c)
                            <td class="p-4 text-center">
                                <a href="{{ route('campsites.show', $c) }}"
                                   class="inline-block w-full bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-bold py-2.5 rounded-lg transition-colors">
                                    このサイトを予約
                                </a>
                            </td>
                        @endforeach
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
