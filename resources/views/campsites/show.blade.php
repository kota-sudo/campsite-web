<x-app-layout>
    @section('title', $campsite->name)
    @section('og_title', $campsite->name . ' | ' . config('app.name'))
    @section('description', mb_substr(strip_tags($campsite->description), 0, 120))
    @if ($campsite->images->isNotEmpty())
        @section('og_image', asset('storage/' . $campsite->images->first()->image_path))
    @endif

    {{-- パンくずナビ --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('campsites.index') }}" class="text-[#2d5a1b] hover:underline">サイト一覧</a>
                <span class="text-gray-400">›</span>
                <span class="text-gray-600 truncate">{{ $campsite->name }}</span>
            </nav>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->isNotEmpty())
        <div class="bg-red-50 border-b border-red-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- タイトルエリア --}}
        <div class="mb-4">
            @php
                $score      = $campsite->reviews->isNotEmpty() ? $campsite->averageRatingOutOf10() : null;
                $scoreLabel = $campsite->reviews->isNotEmpty() ? $campsite->ratingLabel() : null;
                $typeLabel = match($campsite->type) {
                    'tent'     => 'テントサイト',
                    'auto'     => 'オートキャンプ',
                    'bungalow' => 'バンガロー',
                    'glamping' => 'グランピング',
                };
                $typeColor = match($campsite->type) {
                    'tent'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'auto'     => 'bg-green-50 text-blue-700 border-blue-200',
                    'bungalow' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'glamping' => 'bg-purple-50 text-purple-700 border-purple-200',
                };
            @endphp
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $typeColor }}">
                            {{ $typeLabel }}
                        </span>
                        @if ($isAvailable === true)
                            <span class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">空きあり</span>
                        @elseif ($isAvailable === false)
                            <span class="text-xs px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-200">満室</span>
                        @endif
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $campsite->name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        @if ($score !== null)
                            <div class="flex items-center gap-1.5">
                                <div class="w-8 h-8 bg-[#2d5a1b] rounded flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ $score }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-800">{{ $scoreLabel }}</span>
                                <span class="text-sm text-gray-500">{{ $campsite->reviews->count() }}件のレビュー</span>
                            </div>
                        @endif
                        <span class="text-sm text-gray-500">最大{{ $campsite->capacity }}名</span>
                    </div>
                </div>
                @auth
                    <div x-data="favoriteToggle('{{ route('favorites.toggle', $campsite) }}', {{ $isFavorited ? 'true' : 'false' }})">
                        <button @click="toggle"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-lg border text-sm font-medium transition"
                                :class="favorited ? 'border-red-300 bg-red-50 text-red-500' : 'border-gray-300 bg-white text-gray-600 hover:border-red-300 hover:text-red-400'">
                            <span x-text="favorited ? '♥' : '♡'" class="text-base leading-none"></span>
                            <span x-text="favorited ? 'お気に入り済み' : 'お気に入り'"></span>
                        </button>
                    </div>
                @endauth
            </div>
        </div>

        {{-- 画像ギャラリー (Agoda/Airbnb風グリッド) --}}
        @if ($campsite->images->isNotEmpty())
            @php
                $images    = $campsite->images;
                $imgUrls   = $images->map(fn($i) => asset('storage/' . $i->image_path))->values();
                $main      = $imgUrls[0];
                $sub       = $imgUrls->slice(1, 4)->values();
            @endphp
            {{-- モーダルオーバーレイ付きギャラリー --}}
            <div x-data="{ lightbox: false, current: 0, images: {{ $imgUrls->toJson() }} }"
                 class="mb-6 select-none"
                 @keydown.escape.window="lightbox = false"
                 @keydown.arrow-left.window="if(lightbox) current = (current - 1 + images.length) % images.length"
                 @keydown.arrow-right.window="if(lightbox) current = (current + 1) % images.length">

                {{-- グリッド本体 --}}
                <div class="relative rounded-xl overflow-hidden"
                     style="height: 420px;">

                    @if ($images->count() === 1)
                        {{-- 1枚だけのときはフル表示 --}}
                        <img src="{{ $main }}" alt="{{ $campsite->name }}"
                             class="w-full h-full object-cover cursor-zoom-in"
                             @click="current = 0; lightbox = true"
                             loading="eager">
                    @else
                        {{-- メイン + サブグリッド --}}
                        <div class="grid h-full gap-1.5
                            {{ $sub->count() >= 4 ? 'grid-cols-[1fr_0.55fr]' : 'grid-cols-[1fr_0.55fr]' }}">

                            {{-- メイン画像 --}}
                            <div class="relative overflow-hidden rounded-l-xl cursor-zoom-in"
                                 @click="current = 0; lightbox = true">
                                <img src="{{ $main }}" alt="{{ $campsite->name }}"
                                     loading="eager"
                                     class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300">
                            </div>

                            {{-- サブ2×2 --}}
                            <div class="grid gap-1.5
                                {{ $sub->count() >= 3 ? 'grid-rows-2' : 'grid-rows-1' }}">
                                @foreach ($sub->take(4) as $j => $url)
                                    <div class="relative overflow-hidden
                                        {{ $j === 1 ? 'rounded-tr-xl' : '' }}
                                        {{ $j === 3 || ($j === 1 && $sub->count() <= 2) ? 'rounded-br-xl' : '' }}
                                        cursor-zoom-in"
                                         @click="current = {{ $j + 1 }}; lightbox = true">
                                        <img src="{{ $url }}"
                                             class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300"
                                             loading="lazy"
                                             alt="">
                                        {{-- 最後のコマに「+N枚」バッジ --}}
                                        @if ($j === 3 && $images->count() > 5)
                                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-br-xl">
                                                <span class="text-white font-bold text-lg">+{{ $images->count() - 5 }}枚</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 「全ての写真を見る」ボタン --}}
                    @if ($images->count() > 1)
                        <button @click="current = 0; lightbox = true"
                                class="absolute bottom-4 right-4 flex items-center gap-1.5 bg-white/90 hover:bg-white text-gray-800 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition-colors border border-gray-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            全 {{ $images->count() }} 枚
                        </button>
                    @endif
                </div>

                {{-- ライトボックス --}}
                <div x-show="lightbox" x-cloak
                     class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
                     @click.self="lightbox = false">
                    <button @click="lightbox = false"
                            class="absolute top-4 right-4 text-white/70 hover:text-white text-3xl w-12 h-12 flex items-center justify-center rounded-full hover:bg-white/10 transition">✕</button>
                    <button @click="current = (current - 1 + images.length) % images.length"
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white text-5xl w-12 h-12 flex items-center justify-center rounded-full hover:bg-white/10 transition">‹</button>
                    <div class="max-w-5xl max-h-[85vh] mx-16">
                        <img :src="images[current]"
                             class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
                        <div class="text-center text-white/60 text-sm mt-3">
                            <span x-text="(current + 1) + ' / ' + images.length"></span>
                        </div>
                    </div>
                    <button @click="current = (current + 1) % images.length"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white text-5xl w-12 h-12 flex items-center justify-center rounded-full hover:bg-white/10 transition">›</button>
                </div>
            </div>
        @else
            <div class="mb-6 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center" style="height: 300px;">
                <div class="text-center text-gray-400">
                    <div class="text-6xl mb-2">⛺</div>
                    <div class="text-sm">写真なし</div>
                </div>
            </div>
        @endif

        {{-- 2カラムレイアウト --}}
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_340px] gap-6 items-start">

            {{-- 左: 詳細情報 --}}
            <div class="space-y-5">

                {{-- 概要 --}}
                <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">サイト概要</h2>
                    @if ($campsite->address)
                        <div class="flex items-center gap-1.5 text-sm text-gray-500 mb-3">
                            <svg class="w-4 h-4 text-[#e07b39] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $campsite->address }}
                        </div>
                    @endif
                    <p class="text-gray-600 leading-relaxed text-sm">{{ $campsite->description }}</p>
                </div>

                {{-- 地図 --}}
                @if ($campsite->latitude && $campsite->longitude)
                    <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900">アクセス・地図</h2>
                        </div>
                        <div id="detail-map" style="height: 280px;"></div>
                    </div>
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const map = L.map('detail-map').setView([{{ $campsite->latitude }}, {{ $campsite->longitude }}], 13);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                            }).addTo(map);
                            L.marker([{{ $campsite->latitude }}, {{ $campsite->longitude }}])
                                .addTo(map)
                                .bindPopup('<strong>{{ $campsite->name }}</strong>')
                                .openPopup();
                        });
                    </script>
                @endif

                {{-- 天気予報ウィジェット --}}
                @if ($campsite->latitude && $campsite->longitude)
                    <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6"
                         x-data="weatherWidget({{ $campsite->latitude }}, {{ $campsite->longitude }})"
                         x-init="load()">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900">周辺の天気予報</h2>
                            <span x-text="city" class="text-xs text-gray-400"></span>
                        </div>
                        {{-- ローディング --}}
                        <div x-show="loading" class="flex gap-2">
                            <template x-for="i in 5">
                                <div class="flex-1 h-20 bg-gray-100 rounded-lg animate-pulse"></div>
                            </template>
                        </div>
                        {{-- APIキーなし --}}
                        <div x-show="!loading && noKey" class="text-sm text-gray-400 py-2">
                            天気予報を表示するには <code class="bg-gray-100 px-1 rounded">OPENWEATHERMAP_API_KEY</code> を設定してください。
                        </div>
                        {{-- エラー --}}
                        <div x-show="!loading && error && !noKey" class="text-sm text-gray-400 py-2">
                            天気情報を取得できませんでした。
                        </div>
                        {{-- 天気カード --}}
                        <div x-show="!loading && days.length > 0" class="flex gap-2">
                            <template x-for="day in days" :key="day.date">
                                <div class="flex-1 flex flex-col items-center bg-[#f0f7eb] rounded-xl p-2.5 text-center">
                                    <p class="text-xs font-semibold text-gray-500 leading-tight" x-text="day.label"></p>
                                    <p class="text-xs text-gray-400 mb-1" x-text="day.weekday"></p>
                                    <img :src="'https://openweathermap.org/img/wn/' + day.icon + '.png'"
                                         :alt="day.desc" class="w-10 h-10 -my-1">
                                    <p class="text-xs font-bold text-red-500" x-text="Math.round(day.temp_max) + '°'"></p>
                                    <p class="text-xs text-[#2d5a1b]/60" x-text="Math.round(day.temp_min) + '°'"></p>
                                    <p class="text-xs text-gray-500 mt-1 leading-tight truncate w-full" x-text="day.desc"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif

                {{-- 周辺スポット（観光・登山情報） --}}
                @if ($campsite->latitude && $campsite->longitude)
                    <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6"
                         x-data="nearbySpots({{ $campsite->latitude }}, {{ $campsite->longitude }})"
                         x-init="load()">
                        <h2 class="text-lg font-bold text-gray-900 mb-1">周辺の観光・登山スポット</h2>
                        <p class="text-xs text-gray-400 mb-4">半径20km以内のスポット（OpenStreetMapデータ）</p>

                        {{-- ローディング --}}
                        <div x-show="loading" class="space-y-2">
                            <template x-for="i in 4">
                                <div class="h-14 bg-gray-100 rounded-xl animate-pulse"></div>
                            </template>
                        </div>

                        {{-- エラー --}}
                        <div x-show="!loading && error" class="text-sm text-gray-400 py-2">
                            周辺スポット情報を取得できませんでした。
                        </div>

                        {{-- スポットなし --}}
                        <div x-show="!loading && !error && spots.length === 0" class="text-sm text-gray-400 py-2">
                            このキャンプ場の周辺に登録されたスポットはありません。
                        </div>

                        {{-- カテゴリフィルタ --}}
                        <div x-show="!loading && spots.length > 0" class="mb-4 flex flex-wrap gap-1.5">
                            <template x-for="cat in categories" :key="cat">
                                <button @click="activeCategory = cat"
                                        class="px-2.5 py-1 rounded-full text-xs font-medium transition-colors"
                                        :class="activeCategory === cat
                                            ? 'bg-[#2d5a1b] text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                        x-text="categoryLabel(cat)">
                                </button>
                            </template>
                        </div>

                        {{-- スポット一覧 --}}
                        <div x-show="!loading && !error" class="space-y-2">
                            <template x-for="spot in filteredSpots" :key="spot.osmUrl">
                                <a :href="spot.osmUrl" target="_blank" rel="noopener"
                                   class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                                    {{-- アイコン --}}
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-white transition-colors">
                                        <span x-text="spot.icon"></span>
                                    </div>
                                    {{-- 情報 --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="spot.name"></p>
                                            <template x-if="spot.elevation">
                                                <span class="text-xs text-gray-400 shrink-0"
                                                      x-text="spot.elevation + 'm'"></span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-3 mt-0.5">
                                            <span class="text-xs text-gray-400" x-text="spot.label"></span>
                                            <span class="text-xs text-[#2d5a1b] font-medium"
                                                  x-text="spot.distance + 'km'"></span>
                                        </div>
                                        <template x-if="spot.description">
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1" x-text="spot.description"></p>
                                        </template>
                                    </div>
                                    {{-- 外部リンクアイコン --}}
                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </template>
                        </div>

                        <p x-show="!loading && spots.length > 0"
                           class="text-xs text-gray-400 mt-3">
                            データ提供: <a href="https://www.openstreetmap.org" target="_blank" rel="noopener" class="underline hover:text-gray-600">OpenStreetMap</a>
                        </p>
                    </div>
                @endif

                {{-- 設備・サービス --}}
                @if ($campsite->amenities->isNotEmpty())
                    <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">設備・サービス</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach ($campsite->amenities as $amenity)
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $amenity->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 空き状況カレンダー --}}
                <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6"
                     x-data="availabilityCalendar('{{ route('campsites.booked-dates', $campsite) }}')"
                     x-init="fetchBooked()">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">空き状況カレンダー</h2>
                    <p class="text-xs text-gray-400 mb-4">赤は予約済み・濃いグレーはブラックアウト（予約不可）です</p>
                    <div class="max-w-sm">
                        {{-- 月ナビ --}}
                        <div class="flex items-center justify-between mb-3">
                            <button @click="prevMonth()"
                                    :disabled="!canGoPrev"
                                    :class="canGoPrev ? 'text-gray-600 hover:bg-gray-100' : 'text-gray-200 cursor-not-allowed'"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg transition text-lg">
                                ‹
                            </button>
                            <span class="text-sm font-bold text-gray-800" x-text="year + '年' + (month + 1) + '月'"></span>
                            <button @click="nextMonth()"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600 transition text-lg">
                                ›
                            </button>
                        </div>
                        {{-- 曜日 --}}
                        <div class="grid grid-cols-7 text-center text-xs font-medium mb-1">
                            <template x-for="d in ['日','月','火','水','木','金','土']">
                                <div x-text="d" :class="d === '日' ? 'text-red-400' : d === '土' ? 'text-[#2d5a1b]/60' : 'text-gray-400'"></div>
                            </template>
                        </div>
                        {{-- 日付セル --}}
                        <div class="grid grid-cols-7 gap-0.5 text-center text-xs" x-show="!loading">
                            <template x-for="blank in startBlank"><div></div></template>
                            <template x-for="day in daysInMonth" :key="day">
                                <div class="rounded-md py-1 px-0.5 flex flex-col items-center leading-tight transition-colors"
                                     :class="{
                                        'bg-gray-100 text-gray-300 line-through cursor-not-allowed': isPast(day),
                                        'bg-red-50 text-red-300 line-through cursor-not-allowed': !isPast(day) && isBooked(day),
                                        'bg-gray-400 text-white line-through cursor-not-allowed': !isPast(day) && !isBooked(day) && isBlocked(day),
                                        'bg-[#2d5a1b] text-white font-bold ring-2 ring-[#2d5a1b]': isSelectedIn(day) || isSelectedOut(day),
                                        'bg-green-50 text-blue-700': isInRange(day) && !isBooked(day) && !isBlocked(day),
                                        'bg-amber-50 text-amber-700 ring-1 ring-amber-200 cursor-pointer hover:bg-amber-100': !isPast(day) && !isBooked(day) && !isBlocked(day) && isSpecialPrice(day) && !isSelectedIn(day) && !isSelectedOut(day) && !isInRange(day),
                                        'bg-green-50 text-green-700 cursor-pointer hover:bg-green-100': !isPast(day) && !isBooked(day) && !isBlocked(day) && !isSpecialPrice(day) && !isSelectedIn(day) && !isSelectedOut(day) && !isInRange(day),
                                     }"
                                     :title="isBlocked(day) ? (blockoutReason(day) ? blockoutReason(day) : '予約不可期間') : ''"
                                     @mouseenter="hoverDay = day"
                                     @mouseleave="hoverDay = null">
                                    <span x-text="day" class="font-semibold"></span>
                                    <span x-show="!isPast(day) && !isBooked(day) && !isBlocked(day)"
                                          x-text="'¥' + Math.round(dayPrice(day)/1000) + 'k'"
                                          class="text-[9px] opacity-70 leading-none mt-0.5"></span>
                                </div>
                            </template>
                        </div>
                        <div x-show="loading" class="text-center py-4 text-gray-400 text-xs">読み込み中...</div>
                        <div class="flex flex-wrap gap-3 mt-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-green-50 border border-green-200 inline-block"></span>空き・通常料金
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-amber-50 border border-amber-300 inline-block"></span>特別料金
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-red-50 border border-red-200 inline-block"></span>予約済み
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-gray-400 inline-block"></span>予約不可（ブラックアウト）
                            </span>
                        </div>
                    </div>
                </div>

                {{-- レビュー投稿フォーム (チェックアウト済み・未レビューの予約がある場合) --}}
                @if ($userReviewableReservation)
                    <div class="bg-white rounded-xl border border-[#e07b39]/30 shadow-sm p-6"
                         x-data="{ rating: 0, hover: 0 }">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-[#e07b39] flex items-center justify-center">
                                <span class="text-white text-sm">★</span>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900">レビューを投稿する</h2>
                                <p class="text-xs text-gray-500">
                                    {{ $userReviewableReservation->check_in_date->isoFormat('YYYY年M月D日') }}〜{{ $userReviewableReservation->check_out_date->isoFormat('M月D日') }}のご滞在
                                </p>
                            </div>
                        </div>
                        @if ($errors->has('rating'))
                            <p class="text-sm text-red-600 mb-3">{{ $errors->first('rating') }}</p>
                        @endif
                        <form method="POST" action="{{ route('reviews.store', $userReviewableReservation) }}">
                            @csrf
                            <input type="hidden" name="rating" :value="rating">
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-gray-500 mb-2">評価</p>
                                <div class="flex gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                @click="rating = {{ $i }}"
                                                @mouseenter="hover = {{ $i }}"
                                                @mouseleave="hover = 0"
                                                class="text-4xl transition-transform hover:scale-110"
                                                :class="(hover || rating) >= {{ $i }} ? 'text-yellow-400' : 'text-gray-200'">★</button>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-500 self-center"
                                          x-text="['','★1 残念','★2 普通','★3 良い','★4 とても良い','★5 最高！'][rating]"></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">コメント（任意・最大500字）</label>
                                <textarea name="comment" rows="3" maxlength="500"
                                          class="w-full rounded-lg border border-gray-200 text-sm text-gray-800 px-3 py-2 focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none resize-none"
                                          placeholder="ご滞在のご感想をお聞かせください">{{ old('comment') }}</textarea>
                            </div>
                            <button type="submit"
                                    :disabled="rating === 0"
                                    class="w-full h-11 rounded-lg bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                レビューを投稿する
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Q&A --}}
                <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">よくある質問 / Q&amp;A</h2>

                    {{-- 質問一覧 --}}
                    @if ($campsite->questions->isNotEmpty())
                        <div class="space-y-4 mb-6">
                            @foreach ($campsite->questions as $question)
                                <div class="rounded-lg border border-gray-100 overflow-hidden">
                                    <div class="flex gap-3 px-4 py-3 bg-gray-50">
                                        <span class="text-[#2d5a1b] font-bold text-sm shrink-0 mt-0.5">Q.</span>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-800">{{ $question->body }}</p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ $question->user->name }}・{{ $question->created_at->isoFormat('M月D日') }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($question->isAnswered())
                                        <div class="flex gap-3 px-4 py-3 bg-white border-t border-gray-100">
                                            <span class="text-[#e07b39] font-bold text-sm shrink-0 mt-0.5">A.</span>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-700">{{ $question->answer_body }}</p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    ホスト・{{ $question->answered_at->isoFormat('M月D日') }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        {{-- ホスト/管理者向け回答フォーム --}}
                                        @auth
                                            @if (auth()->id() === $campsite->user_id || auth()->user()->is_admin)
                                                <div class="px-4 py-3 bg-amber-50 border-t border-amber-100"
                                                     x-data="{ open: false }">
                                                    <button @click="open = !open"
                                                            class="text-xs font-semibold text-amber-700 hover:text-amber-900 transition-colors">
                                                        <span x-text="open ? '▲ キャンセル' : '▼ 回答する'"></span>
                                                    </button>
                                                    <div x-show="open" x-cloak class="mt-2">
                                                        <form method="POST"
                                                              action="{{ route('campsite.questions.answer', [$campsite, $question]) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <textarea name="answer_body" rows="3" required maxlength="1000"
                                                                      class="w-full rounded-lg border border-amber-200 bg-white text-gray-800 text-sm px-3 py-2 focus:border-amber-400 outline-none resize-none"
                                                                      placeholder="回答を入力してください..."></textarea>
                                                            <button type="submit"
                                                                    class="mt-2 px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition-colors">
                                                                回答を送信
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="px-4 py-3 bg-white border-t border-gray-100">
                                                    <p class="text-xs text-gray-400 italic">回答待ちです</p>
                                                </div>
                                            @endif
                                        @else
                                            <div class="px-4 py-3 bg-white border-t border-gray-100">
                                                <p class="text-xs text-gray-400 italic">回答待ちです</p>
                                            </div>
                                        @endauth
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 mb-6">まだ質問はありません。お気軽にご質問ください。</p>
                    @endif

                    {{-- 質問投稿フォーム (ログイン済みのみ) --}}
                    @auth
                        <div class="border-t border-gray-100 pt-5" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 text-sm font-semibold text-[#2d5a1b] hover:text-[#1c3a0e] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="open ? '閉じる' : 'ホストに質問する'"></span>
                            </button>
                            <div x-show="open" x-cloak class="mt-3">
                                <form method="POST" action="{{ route('campsite.questions.store', $campsite) }}">
                                    @csrf
                                    <textarea name="body" rows="3" required maxlength="500"
                                              class="w-full rounded-lg border border-gray-200 text-sm text-gray-800 px-3 py-2 focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none resize-none"
                                              placeholder="サイトについて気になることを質問してください（最大500字）">{{ old('body') }}</textarea>
                                    @error('body')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                                    <button type="submit"
                                            class="mt-2 px-5 py-2 bg-[#2d5a1b] hover:bg-[#1c3a0e] text-white text-sm font-bold rounded-lg transition-colors">
                                        質問を送信する
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="border-t border-gray-100 pt-5">
                            <p class="text-sm text-gray-500">
                                <a href="{{ route('login') }}" class="text-[#2d5a1b] hover:underline">ログイン</a>するとホストに質問できます。
                            </p>
                        </div>
                    @endauth
                </div>

                {{-- レビュー一覧 --}}
                @if ($campsite->reviews->isNotEmpty())
                    <div class="reveal bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">口コミ・レビュー</h2>
                                <p class="text-sm text-gray-500">{{ $campsite->reviews->count() }}件のレビュー</p>
                            </div>
                            @if ($score !== null)
                                <div class="ml-auto text-center">
                                    <div class="w-16 h-16 bg-[#2d5a1b] rounded-xl flex items-center justify-center mb-1">
                                        <span class="text-white font-bold text-2xl">{{ $score }}</span>
                                    </div>
                                    <div class="text-xs font-bold text-gray-700">{{ $scoreLabel }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4">
                            @foreach ($campsite->reviews as $review)
                                <div class="pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-[#2d5a1b] flex items-center justify-center text-white text-sm font-bold">
                                                {{ mb_substr($review->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $review->user->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $review->created_at->isoFormat('YYYY年M月D日') }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <div class="w-7 h-7 bg-[#2d5a1b] rounded flex items-center justify-center">
                                                <span class="text-white font-bold text-xs">{{ $review->rating * 2 }}</span>
                                            </div>
                                            <div class="flex">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <span class="text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    @if ($review->comment)
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- 右: スティッキー予約パネル --}}
            <div class="lg:sticky lg:top-4 space-y-4">

                {{-- ▼ プランがある場合: プランカード一覧 --}}
                @if ($campsite->activePlans->isNotEmpty())
                    <div class="reveal bg-white rounded-xl border border-gray-200 shadow-md overflow-hidden">
                        <div class="bg-[#1c3a0e] px-5 py-4">
                            <h2 class="text-white font-bold text-base">プランを選ぶ</h2>
                            <p class="text-green-200 text-xs mt-0.5">{{ $campsite->activePlans->count() }}種類のプランがあります</p>
                        </div>

                        <div class="divide-y divide-gray-100"
                             x-data="{
                                 checkIn:  '{{ request('check_in') }}',
                                 checkOut: '{{ request('check_out') }}',
                                 guests:   {{ request('guests', 1) }},
                             }">

                            {{-- 日程入力バー --}}
                            <div class="px-4 py-3 bg-gray-50 space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">出発日</label>
                                        <input type="date" x-model="checkIn"
                                               min="{{ date('Y-m-d') }}"
                                               class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-xs px-2 focus:border-[#2d5a1b] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">帰着日</label>
                                        <input type="date" x-model="checkOut"
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                               class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-xs px-2 focus:border-[#2d5a1b] outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">人数</label>
                                    <input type="number" x-model.number="guests"
                                           min="1" max="50"
                                           class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-xs px-2 focus:border-[#2d5a1b] outline-none">
                                </div>
                            </div>

                            {{-- プランカード --}}
                            @foreach ($campsite->activePlans as $plan)
                                <div class="p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex gap-3">
                                        {{-- プラン画像 --}}
                                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                            @if ($plan->image_path)
                                                <img src="{{ asset('storage/' . $plan->image_path) }}"
                                                     alt="{{ $plan->name }}"
                                                     loading="lazy"
                                                     class="w-full h-full object-cover">
                                            @elseif ($campsite->images->isNotEmpty())
                                                <img src="{{ asset('storage/' . $campsite->images->first()->image_path) }}"
                                                     alt="{{ $plan->name }}"
                                                     loading="lazy"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-2xl">⛺</div>
                                            @endif
                                        </div>

                                        {{-- プラン情報 --}}
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-bold text-gray-900 leading-tight">{{ $plan->name }}</h3>
                                            @if ($plan->description)
                                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed line-clamp-2">{{ $plan->description }}</p>
                                            @endif
                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                                    </svg>
                                                    最大{{ $plan->capacity }}名
                                                </span>
                                                @if ($plan->stock > 1)
                                                    <span class="inline-flex items-center text-xs text-blue-600 bg-green-50 px-2 py-0.5 rounded-full">
                                                        {{ $plan->stock }}区画
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 料金 + 予約ボタン --}}
                                    <div class="flex items-center justify-between mt-3">
                                        <div>
                                            <span class="text-xs text-gray-400">1泊</span>
                                            <span class="text-xl font-bold text-gray-900 ml-1">¥{{ number_format($plan->price_per_night) }}</span>
                                        </div>
                                        @auth
                                            <a :href="checkIn && checkOut
                                                    ? '{{ route('reservations.create') }}?campsite_id={{ $campsite->id }}&plan_id={{ $plan->id }}&check_in=' + checkIn + '&check_out=' + checkOut + '&guests=' + guests
                                                    : '{{ route('campsites.show', $campsite) }}'"
                                               class="btn-ripple px-4 py-2 bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">
                                                このプランを予約
                                            </a>
                                        @else
                                            <a href="{{ route('login') }}"
                                               class="btn-ripple px-4 py-2 bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">
                                                ロ���インして予約
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach

                            <div class="px-4 py-3 bg-gray-50">
                                <p class="text-xs text-green-600 font-medium">✓ 無料キャンセル可（チェックイン前日まで）</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ▼ プランがない場合 or プランに加えてサイト全体の予約パネル --}}
                @if ($campsite->activePlans->isEmpty())
                <div class="reveal bg-white rounded-xl border border-gray-200 shadow-md overflow-hidden">

                    {{-- 価格ヘッダー --}}
                    <div class="relative bg-[#1c3a0e] px-5 py-4 overflow-hidden">
                        {{-- 小型焚き火アニメーション --}}
                        <div class="absolute right-4 bottom-0 pointer-events-none select-none" style="width:38px;">
                            {{-- グロー --}}
                            <div class="fire-glow absolute" style="bottom:0;left:50%;transform:translateX(-50%);width:60px;height:20px;border-radius:50%;background:radial-gradient(ellipse,rgba(245,120,20,.5),transparent 70%);"></div>
                            {{-- 火の粉 --}}
                            <div class="ember" style="bottom:18px;left:45%;--em-dur:2s;  --em-delay:0s;  --ex:-4px;--ex2:-8px;--ex3:-2px;width:2px;height:2px;"></div>
                            <div class="ember" style="bottom:16px;left:58%;--em-dur:1.6s;--em-delay:.7s; --ex: 5px;--ex2: 9px;--ex3: 3px;width:2px;height:2px;"></div>
                            <div class="ember" style="bottom:20px;left:50%;--em-dur:2.3s;--em-delay:1.2s;--ex:-3px;--ex2: 5px;--ex3: 1px;width:2px;height:2px;background:#ffee88;"></div>
                            {{-- 煙 --}}
                            <div style="position:absolute;bottom:34px;left:50%;transform:translateX(-50%);">
                                <div class="smoke-particle" style="left:-1px;width:5px;height:5px;"></div>
                                <div class="smoke-particle" style="left: 2px;width:5px;height:5px;"></div>
                            </div>
                            {{-- 炎: 外 --}}
                            <div class="flame-outer absolute left-1/2 -translate-x-1/2" style="bottom:8px;width:14px;height:28px;background:radial-gradient(ellipse at 50% 100%,#c4621a 0%,#e07b39 35%,#f5a623 65%,rgba(255,220,80,.3) 85%,transparent 100%);border-radius:60% 60% 30% 30%/80% 80% 20% 20%;"></div>
                            {{-- 炎: 中 --}}
                            <div class="flame-inner absolute left-1/2 -translate-x-1/2" style="bottom:8px;width:8px;height:21px;background:radial-gradient(ellipse at 50% 100%,#f5c94a 0%,#f5a623 50%,transparent 100%);border-radius:60% 60% 30% 30%/80% 80% 20% 20%;"></div>
                            {{-- 炎: 芯 --}}
                            <div class="flame-core absolute left-1/2 -translate-x-1/2" style="bottom:8px;width:4px;height:13px;background:radial-gradient(ellipse at 50% 100%,#fff,#fffde0 50%,transparent);border-radius:60% 60% 30% 30%/80% 80% 20% 20%;filter:blur(.3px);"></div>
                            {{-- 丸太 --}}
                            <div class="absolute" style="bottom:6px;left:50%;width:26px;height:5px;background:linear-gradient(180deg,#6b3d14,#3d2408);border-radius:3px;transform:translateX(-50%) rotate(22deg);"></div>
                            <div class="absolute" style="bottom:6px;left:50%;width:26px;height:5px;background:linear-gradient(180deg,#7a4518,#4a2e0a);border-radius:3px;transform:translateX(-50%) rotate(-22deg);"></div>
                        </div>
                        <div class="text-green-200 text-xs mb-0.5">1泊あたりの料金</div>
                        <div class="text-white text-3xl font-bold">
                            ¥{{ number_format($campsite->price_per_night) }}
                        </div>
                        @if ($campsite->hasWeekendSurcharge())
                            <div class="text-amber-300 text-xs mt-0.5 font-medium">
                                土日・祝日 ¥{{ number_format($campsite->weekendPrice()) }}
                                <span class="text-amber-400/70">（×{{ number_format($campsite->weekend_multiplier, 1) }}）</span>
                            </div>
                        @endif
                        <div class="text-green-300 text-xs mt-1 font-medium">✓ 無料キャンセル可（チェックイン前）</div>
                    </div>

                    @auth
                        {{-- 予約フォーム --}}
                        <div class="p-5"
                             x-data="reservationForm({{ $campsite->price_per_night }}, '{{ request('check_in') }}', '{{ request('check_out') }}', {{ request('guests', 1) }})">
                            <form method="GET" action="{{ route('reservations.create') }}">
                                <input type="hidden" name="campsite_id" value="{{ $campsite->id }}">

                                <div class="space-y-3 mb-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">出発日</label>
                                            <input type="date" name="check_in" x-model="checkIn"
                                                   @change="onCheckInChange"
                                                   min="{{ date('Y-m-d') }}" required
                                                   class="w-full h-10 rounded-lg border border-gray-200 text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">帰着日</label>
                                            <input type="date" name="check_out" x-model="checkOut"
                                                   :min="minCheckOut" required
                                                   class="w-full h-10 rounded-lg border border-gray-200 text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">
                                            人数（最大{{ $campsite->capacity }}名）
                                        </label>
                                        <input type="number" name="guests" x-model="guests"
                                               min="1" max="{{ $campsite->capacity }}" required
                                               class="w-full h-10 rounded-lg border border-gray-200 text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none">
                                    </div>
                                </div>

                                {{-- 価格内訳 --}}
                                <div x-show="nights > 0"
                                     class="bg-gray-50 rounded-lg p-3 mb-4 space-y-2 text-sm">
                                    <div class="flex justify-between text-gray-600">
                                        <span x-text="'¥' + pricePerNight.toLocaleString() + ' × ' + nights + '泊'"></span>
                                        <span x-text="'¥' + totalPrice.toLocaleString()"></span>
                                    </div>
                                    <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-200">
                                        <span>合計</span>
                                        <span x-text="'¥' + totalPrice.toLocaleString()"></span>
                                    </div>
                                </div>

                                <button type="submit"
                                        :disabled="nights <= 0"
                                        {{ $isAvailable === false ? 'disabled' : '' }}
                                        class="btn-ripple w-full h-12 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    @if ($isAvailable === false)
                                        この日程は満室です
                                    @else
                                        🏕️ 予約内容を確認する
                                    @endif
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <p class="text-sm text-gray-600 mb-4">予約するにはログインが必要です</p>
                            <a href="{{ route('login') }}"
                               class="btn-ripple block w-full h-12 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm leading-[48px] transition-colors">
                                ログインして予約する
                            </a>
                            <a href="{{ route('register') }}"
                               class="block mt-2 text-sm text-[#2d5a1b] hover:underline">
                                アカウントをお持ちでない方はこちら
                            </a>
                        </div>
                    @endauth

                    {{-- 安心保証 --}}
                    <div class="px-5 pb-5 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            チェックイン前まで無料でキャンセル可能
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            予約後すぐに確認メールをお送りします
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            即時予約確定・待ち時間なし
                        </div>
                    </div>
                </div>
                @endif {{-- /プランなし予約パネル --}}
            </div>
        </div>
    </div>

    {{-- 天気ウィジェット Alpine コンポーネント --}}
    <script>
    function weatherWidget(lat, lng) {
        return {
            loading: true,
            noKey: false,
            error: false,
            city: '',
            days: [],
            async load() {
                try {
                    const res = await fetch(`/api/weather?lat=${lat}&lng=${lng}`);
                    const data = await res.json();
                    if (data.error === 'no_key') { this.noKey = true; }
                    else if (data.error) { this.error = true; }
                    else {
                        this.city = data.city;
                        this.days = data.days;
                    }
                } catch { this.error = true; }
                finally { this.loading = false; }
            },
        };
    }
    </script>
</x-app-layout>
