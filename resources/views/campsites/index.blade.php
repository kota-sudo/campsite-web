<x-app-layout>
    @section('title', 'キャンプサイト一覧')
    @section('og_title', 'キャンプサイトを探す | ' . config('app.name'))

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- 比較バー (Alpine store) --}}
    <div x-data x-show="$store.compare.ids.length > 0" x-cloak
         class="fixed bottom-24 left-1/2 -translate-x-1/2 z-40 bg-[#2d5a1b] text-white rounded-2xl shadow-2xl px-5 py-3 flex items-center gap-4 transition-all">
        <span class="text-sm font-semibold">
            <span x-text="$store.compare.ids.length"></span>/3件を選択中
        </span>
        <div class="flex gap-2">
            <template x-for="id in $store.compare.ids" :key="id">
                <span class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold" x-text="$store.compare.ids.indexOf(id) + 1"></span>
            </template>
        </div>
        <a :href="'{{ route('campsites.compare') }}?' + $store.compare.ids.map(id => 'ids[]=' + id).join('&')"
           x-show="$store.compare.ids.length >= 2"
           class="bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-bold px-4 py-1.5 rounded-lg transition-colors whitespace-nowrap">
            比較する
        </a>
        <button @click="$store.compare.clear()"
                class="text-green-300 hover:text-white text-xs transition-colors">クリア</button>
    </div>

    {{-- 検索バー --}}
    <div class="bg-[#1c3a0e] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <form method="GET" action="{{ route('campsites.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_0.6fr_0.8fr_auto] gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-green-200 uppercase tracking-wide mb-1.5">出発日</label>
                        <input type="date" name="check_in"
                               value="{{ request('check_in') }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full h-11 rounded-lg border-0 bg-white text-gray-800 px-3 text-sm focus:ring-2 focus:ring-[#e07b39] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-green-200 uppercase tracking-wide mb-1.5">帰着日</label>
                        <input type="date" name="check_out"
                               value="{{ request('check_out') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full h-11 rounded-lg border-0 bg-white text-gray-800 px-3 text-sm focus:ring-2 focus:ring-[#e07b39] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-green-200 uppercase tracking-wide mb-1.5">人数</label>
                        <input type="number" name="guests"
                               value="{{ request('guests') }}"
                               min="1" max="20" placeholder="1"
                               class="w-full h-11 rounded-lg border-0 bg-white text-gray-800 px-3 text-sm focus:ring-2 focus:ring-[#e07b39] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-green-200 uppercase tracking-wide mb-1.5">スタイル</label>
                        <select name="type"
                                class="w-full h-11 rounded-lg border-0 bg-white text-gray-800 px-3 text-sm focus:ring-2 focus:ring-[#e07b39] outline-none">
                            <option value="">すべて</option>
                            <option value="tent"     {{ request('type') === 'tent'     ? 'selected' : '' }}>⛺ テント</option>
                            <option value="auto"     {{ request('type') === 'auto'     ? 'selected' : '' }}>🚗 オートキャンプ</option>
                            <option value="bungalow" {{ request('type') === 'bungalow' ? 'selected' : '' }}>🏠 バンガロー</option>
                            <option value="glamping" {{ request('type') === 'glamping' ? 'selected' : '' }}>✨ グランピング</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                                class="h-11 px-7 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm transition-colors whitespace-nowrap shadow-lg shadow-black/20">
                            探す
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 自然言語AI検索 --}}
    <div class="bg-gradient-to-r from-[#0d2208] to-[#1c3a0e] border-b border-white/10"
         x-data="aiSearch()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.001 3.001 0 01-.765.515l-.02.01a3 3 0 01-2.042.05L12 17.07l-.301.081a3 3 0 01-2.043-.05l-.02-.01a3.001 3.001 0 01-.765-.515L8.343 16.3z"/>
                    </svg>
                    <span class="text-xs font-bold text-green-300 whitespace-nowrap">AI検索</span>
                </div>
                <div class="flex-1 relative">
                    <input type="text" x-model="query"
                           @keydown.enter="search()"
                           placeholder="例：家族4人でグランピング、2万円以内でBBQできる場所"
                           class="w-full h-9 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/40 text-sm px-3 pr-10 focus:bg-white/15 focus:border-white/40 outline-none transition">
                    <button @click="search()" :disabled="loading"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-white/70 hover:text-white transition disabled:opacity-40">
                        <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </div>
                <div x-show="applied" x-cloak class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-green-300 font-medium">フィルター適用中</span>
                    <button @click="clear()" class="text-xs text-white/60 hover:text-white transition">クリア</button>
                </div>
            </div>
            <div x-show="error" x-cloak class="mt-1.5 text-xs text-red-300" x-text="error"></div>
        </div>
    </div>

    {{-- 最近見たサイト --}}
    @if ($recentSites->isNotEmpty())
        <div class="bg-[#f5f0e8] border-b border-[#e0d8cc]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">最近チェックしたサイト</p>
                <div class="flex gap-3 overflow-x-auto pb-1">
                    @foreach ($recentSites as $recent)
                        <a href="{{ route('campsites.show', $recent) }}"
                           class="flex-shrink-0 flex items-center gap-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl px-3 py-2 transition-colors group">
                            @if ($recent->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $recent->images->first()->image_path) }}"
                                     alt="{{ $recent->name }}"
                                     loading="lazy"
                                     class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center text-lg flex-shrink-0">⛺</div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 group-hover:text-[#2d5a1b] truncate max-w-[140px]">{{ $recent->name }}</p>
                                <p class="text-xs text-gray-500">¥{{ number_format($recent->price_per_night) }}/泊</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)] gap-6 items-start">

            {{-- フィルターサイドバー --}}
            <aside class="lg:sticky lg:top-4">
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-5">
                    <h3 class="text-base font-bold text-gray-900 mb-5 pb-3 border-b border-[#e0d8cc] flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#2d5a1b]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.553.894l-4 2A1 1 0 016 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/></svg>
                        絞り込み
                    </h3>
                    <form method="GET" action="{{ route('campsites.index') }}" class="space-y-5">
                        <input type="hidden" name="check_in"  value="{{ request('check_in') }}">
                        <input type="hidden" name="check_out" value="{{ request('check_out') }}">
                        <input type="hidden" name="guests"    value="{{ request('guests') }}">

                        {{-- 並び替え --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">並び替え</label>
                            <select name="sort"
                                    class="w-full rounded-lg border border-gray-200 bg-white text-gray-800 text-sm h-10 px-3 focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none">
                                <option value="">おすすめ順</option>
                                <option value="price_asc"      {{ request('sort') === 'price_asc'      ? 'selected' : '' }}>料金が安い順</option>
                                <option value="price_desc"     {{ request('sort') === 'price_desc'     ? 'selected' : '' }}>料金が高い順</option>
                                <option value="capacity_desc"  {{ request('sort') === 'capacity_desc'  ? 'selected' : '' }}>人数が多い順</option>
                                <option value="rating_desc"    {{ request('sort') === 'rating_desc'    ? 'selected' : '' }}>評価が高い順</option>
                            </select>
                        </div>

                        {{-- サイトタイプ --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">サイトタイプ</label>
                            <div class="space-y-2.5">
                                @foreach ([
                                    ''         => 'すべてのタイプ',
                                    'tent'     => '⛺ テントサイト',
                                    'auto'     => '🚗 オートキャンプ',
                                    'bungalow' => '🏠 バンガロー',
                                    'glamping' => '✨ グランピング',
                                ] as $value => $label)
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="type" value="{{ $value }}"
                                               {{ (request('type') ?? '') === $value ? 'checked' : '' }}
                                               class="h-4 w-4 border-gray-300 text-[#e07b39] focus:ring-[#e07b39]">
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 価格帯 --}}
                        <div x-data="{
                            min: {{ request('price_min', 0) }},
                            max: {{ request('price_max', 50000) }},
                            minCap: 0,
                            maxCap: 50000,
                            get minPct() { return (this.min / this.maxCap) * 100 },
                            get maxPct() { return (this.max / this.maxCap) * 100 },
                        }">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">料金帯（1泊）</label>
                            <div class="flex items-center justify-between text-xs text-gray-700 font-semibold mb-3">
                                <span x-text="'¥' + min.toLocaleString()"></span>
                                <span x-text="'¥' + max.toLocaleString()"></span>
                            </div>
                            {{-- デュアルレンジスライダー --}}
                            <div class="relative h-1.5 bg-gray-200 rounded-full mb-4">
                                <div class="absolute h-1.5 bg-[#e07b39] rounded-full"
                                     :style="'left:' + minPct + '%;right:' + (100 - maxPct) + '%'"></div>
                                <input type="range" name="price_min" x-model.number="min"
                                       :min="minCap" :max="max - 1000" step="1000"
                                       class="absolute w-full h-1.5 opacity-0 cursor-pointer" style="top:0">
                                <input type="range" name="price_max" x-model.number="max"
                                       :min="min + 1000" :max="maxCap" step="1000"
                                       class="absolute w-full h-1.5 opacity-0 cursor-pointer" style="top:0">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">最小</label>
                                    <input type="number" name="price_min" x-model.number="min"
                                           min="0" :max="max - 1000" step="1000"
                                           class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-xs px-2 focus:border-[#2d5a1b] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">最大</label>
                                    <input type="number" name="price_max" x-model.number="max"
                                           :min="min + 1000" max="50000" step="1000"
                                           class="w-full h-9 rounded-lg border border-gray-200 bg-white text-gray-800 text-xs px-2 focus:border-[#2d5a1b] outline-none">
                                </div>
                            </div>
                        </div>

                        {{-- 人数 --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">最低人数</label>
                            <select name="guests"
                                    class="w-full rounded-lg border border-gray-200 bg-white text-gray-800 text-sm h-10 px-3 focus:border-[#2d5a1b] focus:ring-1 focus:ring-[#2d5a1b] outline-none">
                                <option value="">指定なし</option>
                                @foreach ([1, 2, 3, 4, 5, 6, 8, 10] as $n)
                                    <option value="{{ $n }}" {{ request('guests') == $n ? 'selected' : '' }}>{{ $n }}名以上</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 最低レビュースコア --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">最低レビュー評価</label>
                            <div class="space-y-2">
                                @foreach ([
                                    ''    => 'すべて',
                                    '4.5' => '★ 4.5以上（最高）',
                                    '4.0' => '★ 4.0以上（とても良い）',
                                    '3.5' => '★ 3.5以上（良い）',
                                    '3.0' => '★ 3.0以上（普通）',
                                ] as $value => $label)
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="min_rating" value="{{ $value }}"
                                               {{ (request('min_rating') ?? '') === $value ? 'checked' : '' }}
                                               class="h-4 w-4 border-gray-300 text-[#e07b39] focus:ring-[#e07b39]">
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900 {{ $value ? 'text-amber-700' : '' }}">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- アメニティ --}}
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                                <span>アメニティ・設備</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            @php $selectedAmenities = (array) request('amenity_ids', []); @endphp
                            {{-- 選択中バッジ --}}
                            @if (count($selectedAmenities) > 0)
                                <div class="text-xs text-[#2d5a1b] font-semibold mb-1">{{ count($selectedAmenities) }}件選択中</div>
                            @endif
                            <div x-show="open || {{ count($selectedAmenities) > 0 ? 'true' : 'false' }}"
                                 x-cloak
                                 class="grid grid-cols-2 gap-y-2 gap-x-1 mt-1">
                                @foreach ($amenities as $amenity)
                                    <label class="flex items-center gap-1.5 cursor-pointer group">
                                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                                               {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}
                                               class="h-3.5 w-3.5 rounded border-gray-300 text-[#e07b39] focus:ring-[#e07b39]">
                                        <span class="text-xs text-gray-700 group-hover:text-gray-900 leading-tight">{{ $amenity->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <button type="button" @click="open = !open" x-show="!open && {{ count($selectedAmenities) === 0 ? 'true' : 'false' }}"
                                    class="text-xs text-[#2d5a1b] hover:underline mt-1">
                                すべて表示 ▼
                            </button>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="submit"
                                    class="flex-1 h-10 rounded-lg bg-[#2d5a1b] hover:bg-[#1c3a0e] text-white font-semibold text-sm transition-colors">
                                適用する
                            </button>
                            <a href="{{ route('campsites.index') }}"
                               class="flex-1 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium inline-flex items-center justify-center transition-colors">
                                リセット
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            {{-- 検索結果 --}}
            <section x-data="{ view: 'list' }">
                {{-- ヘッダー --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 mb-4 flex flex-col gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                @if (request('check_in') && request('check_out'))
                                    {{ \Carbon\Carbon::parse(request('check_in'))->isoFormat('M月D日') }} 〜
                                    {{ \Carbon\Carbon::parse(request('check_out'))->isoFormat('M月D日') }} の空き状況
                                @else
                                    全キャンプサイト
                                @endif
                            </h2>
                            <p class="text-sm text-gray-500 mt-0.5">
                                <span class="font-semibold text-[#2d5a1b]">{{ $campsites->total() }}件</span> のサイトが見つかりました
                            </p>
                        </div>
                    <form method="GET" action="{{ route('campsites.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="check_in"   value="{{ request('check_in') }}">
                        <input type="hidden" name="check_out"  value="{{ request('check_out') }}">
                        <input type="hidden" name="guests"     value="{{ request('guests') }}">
                        <input type="hidden" name="type"       value="{{ request('type') }}">
                        <input type="hidden" name="price_min"  value="{{ request('price_min') }}">
                        <input type="hidden" name="price_max"  value="{{ request('price_max') }}">
                        <input type="hidden" name="min_rating" value="{{ request('min_rating') }}">
                        @foreach ((array) request('amenity_ids', []) as $aid)
                            <input type="hidden" name="amenity_ids[]" value="{{ $aid }}">
                        @endforeach
                        <label class="text-xs text-gray-500 whitespace-nowrap">並び替え</label>
                        <select name="sort" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-200 bg-white text-gray-700 text-sm h-9 px-3 min-w-[160px] focus:border-[#2d5a1b] outline-none">
                            <option value="">おすすめ順</option>
                            <option value="price_asc"     {{ request('sort') === 'price_asc'     ? 'selected' : '' }}>料金安い順</option>
                            <option value="price_desc"    {{ request('sort') === 'price_desc'    ? 'selected' : '' }}>料金高い順</option>
                            <option value="capacity_desc" {{ request('sort') === 'capacity_desc' ? 'selected' : '' }}>人数多い順</option>
                            <option value="rating_desc"   {{ request('sort') === 'rating_desc'   ? 'selected' : '' }}>評価高い順</option>
                        </select>
                    </form>

                        {{-- リスト/マップ切替 --}}
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <button @click="view = 'list'"
                                    :class="view === 'list' ? 'bg-[#2d5a1b] text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                                    class="px-3 py-2 text-xs font-medium transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                                リスト
                            </button>
                            <button @click="view = 'map'"
                                    :class="view === 'map' ? 'bg-[#2d5a1b] text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                                    class="px-3 py-2 text-xs font-medium transition-colors flex items-center gap-1.5 border-l border-gray-200">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                地図
                            </button>
                        </div>
                    </div>

                    {{-- 絞り込みチップ --}}
                    @php
                        $chips = [];
                        $typeNames = ['tent'=>'テントサイト','auto'=>'オートキャンプ','bungalow'=>'バンガロー','glamping'=>'グランピング'];
                        if (request('type'))       $chips['type']       = $typeNames[request('type')] ?? request('type');
                        if (request('price_min'))  $chips['price_min']  = '¥' . number_format(request('price_min')) . '〜';
                        if (request('price_max'))  $chips['price_max']  = '〜¥' . number_format(request('price_max'));
                        if (request('guests'))     $chips['guests']     = request('guests') . '名以上';
                        if (request('min_rating')) $chips['min_rating'] = '★' . request('min_rating') . '以上';
                        if (request('check_in') && request('check_out')) {
                            $chips['dates'] = \Carbon\Carbon::parse(request('check_in'))->isoFormat('M/D') . '〜' . \Carbon\Carbon::parse(request('check_out'))->isoFormat('M/D');
                        }
                        foreach ((array) request('amenity_ids', []) as $aid) {
                            $amenityName = $amenities->firstWhere('id', $aid)?->name;
                            if ($amenityName) $chips['amenity_' . $aid] = $amenityName;
                        }
                    @endphp
                    @if (count($chips) > 0)
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs text-gray-500">絞り込み中:</span>
                            @foreach ($chips as $key => $label)
                                @php
                                    // このチップだけ外したURLを生成
                                    $params = request()->except(
                                        str_starts_with($key, 'amenity_') ? [] : [$key]
                                    );
                                    if (str_starts_with($key, 'amenity_')) {
                                        $aid = substr($key, 8);
                                        $params['amenity_ids'] = array_values(array_filter((array) request('amenity_ids', []), fn($v) => $v != $aid));
                                    }
                                    unset($params['page']);
                                    $removeUrl = route('campsites.index') . '?' . http_build_query($params);
                                @endphp
                                <a href="{{ $removeUrl }}"
                                   class="inline-flex items-center gap-1 text-xs bg-[#2d5a1b]/10 text-[#2d5a1b] hover:bg-red-50 hover:text-red-600 px-2.5 py-1 rounded-full transition-colors font-medium">
                                    {{ $label }}
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            @endforeach
                            <a href="{{ route('campsites.index') }}"
                               class="text-xs text-gray-400 hover:text-red-500 transition-colors">すべてクリア</a>
                        </div>
                    @endif
                </div>

                {{-- マップビュー --}}
                <div x-show="view === 'map'" x-cloak class="mb-4">
                    <div id="campsites-map" class="w-full rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                         style="height: 520px;"></div>
                </div>

                {{-- リストビュー --}}
                <div x-show="view === 'list'" x-cloak>
                @if ($campsites->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                        <div class="text-5xl mb-4">🏕️</div>
                        <h3 class="text-lg font-bold text-gray-700 mb-2">該当するサイトが見つかりません</h3>
                        <p class="text-sm text-gray-500 mb-4">検索条件を変更してお試しください</p>
                        <a href="{{ route('campsites.index') }}"
                           class="inline-block text-sm text-[#2d5a1b] hover:underline font-medium">
                            条件をリセットする
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($campsites as $campsite)
                            @php
                                $score = $campsite->reviews->isNotEmpty() ? $campsite->averageRatingOutOf10() : null;
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

                            <div class="camp-card reveal bg-white border border-[#e0d8cc] rounded-xl shadow-sm overflow-hidden">
                                <div class="grid grid-cols-1 md:grid-cols-[280px_minmax(0,1fr)_200px]">

                                    {{-- 画像エリア --}}
                                    <div class="card-img relative bg-gray-100" style="min-height: 200px;">
                                        @auth
                                            <div class="absolute top-3 left-3 z-10"
                                                 x-data="favoriteToggle('{{ route('favorites.toggle', $campsite) }}', {{ in_array($campsite->id, $favoriteIds) ? 'true' : 'false' }})">
                                                <button @click.prevent="toggle"
                                                        class="w-9 h-9 flex items-center justify-center rounded-full bg-white/90 shadow-sm hover:bg-white transition"
                                                        :title="favorited ? 'お気に入りから削除' : 'お気に入りに追加'">
                                                    <span x-text="favorited ? '♥' : '♡'"
                                                          :class="favorited ? 'text-red-500 text-lg' : 'text-gray-400 text-lg'"></span>
                                                </button>
                                            </div>
                                        @endauth

                                        <a href="{{ route('campsites.show', $campsite) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                           class="block h-full" style="min-height: 200px;">
                                            @if ($campsite->images->isNotEmpty())
                                                <img src="{{ asset('storage/' . $campsite->images->first()->image_path) }}"
                                                     alt="{{ $campsite->name }}"
                                                     loading="lazy"
                                                     class="w-full h-full object-cover" style="min-height: 200px;">
                                            @else
                                                <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-400" style="min-height: 200px;">
                                                    <span class="text-5xl mb-2">⛺</span>
                                                    <span class="text-xs">写真なし</span>
                                                </div>
                                            @endif
                                        </a>
                                    </div>

                                    {{-- 情報エリア --}}
                                    <div class="p-5 flex flex-col">
                                        {{-- タイプバッジ --}}
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $typeColor }}">
                                                {{ $typeLabel }}
                                            </span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                                最大{{ $campsite->capacity }}名
                                            </span>
                                        </div>

                                        {{-- 名前 --}}
                                        <a href="{{ route('campsites.show', $campsite) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}">
                                            <h3 class="text-xl font-bold text-gray-900 hover:text-[#2d5a1b] transition-colors leading-tight mb-2">
                                                {{ $campsite->name }}
                                            </h3>
                                        </a>

                                        {{-- 説明 --}}
                                        <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed mb-3">
                                            {{ $campsite->description }}
                                        </p>

                                        {{-- アメニティ --}}
                                        @if ($campsite->amenities->isNotEmpty())
                                            <div class="flex flex-wrap gap-1.5 mb-3">
                                                @foreach ($campsite->amenities->take(4) as $amenity)
                                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-50 border border-gray-200 text-gray-600">
                                                        {{ $amenity->name }}
                                                    </span>
                                                @endforeach
                                                @if ($campsite->amenities->count() > 4)
                                                    <span class="text-xs text-gray-400">+{{ $campsite->amenities->count() - 4 }}件</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-auto flex items-center gap-3 text-xs text-gray-500">
                                            <span class="flex items-center gap-1 text-[#2d5a1b] font-medium">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                空きあり・今すぐ予約
                                            </span>
                                        </div>
                                    </div>

                                    {{-- 価格・CTA --}}
                                    <div class="border-t md:border-t-0 md:border-l border-[#e0d8cc] bg-[#f9f5ef] p-5 flex flex-col justify-between">
                                        {{-- 比較チェックボックス --}}
                                        <div class="mb-3 flex items-center gap-2"
                                             x-data
                                             x-bind:class="$store.compare.ids.includes({{ $campsite->id }}) ? '' : ($store.compare.ids.length >= 3 ? 'opacity-40' : '')">
                                            <input type="checkbox"
                                                   id="compare-{{ $campsite->id }}"
                                                   :checked="$store.compare.ids.includes({{ $campsite->id }})"
                                                   :disabled="!$store.compare.ids.includes({{ $campsite->id }}) && $store.compare.ids.length >= 3"
                                                   @change="$store.compare.toggle({{ $campsite->id }})"
                                                   class="w-4 h-4 rounded border-gray-300 text-[#2d5a1b] cursor-pointer">
                                            <label for="compare-{{ $campsite->id }}" class="text-xs text-gray-500 cursor-pointer select-none">比較に追加</label>
                                        </div>

                                        {{-- スコアバッジ (Agoda風) --}}
                                        @if ($score !== null)
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="flex-shrink-0 w-10 h-10 bg-[#2d5a1b] rounded-lg flex items-center justify-center">
                                                    <span class="text-white font-bold text-base leading-none">{{ $score }}</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-800">{{ $scoreLabel }}</div>
                                                    <div class="text-xs text-gray-500">{{ $campsite->reviews->count() }}件のレビュー</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-4">
                                                <span class="text-xs text-gray-400 italic">レビューなし</span>
                                            </div>
                                        @endif

                                        {{-- 価格 --}}
                                        <div class="text-right mb-4">
                                            <div class="text-xs text-gray-500 mb-0.5">1泊あたり</div>
                                            <div class="text-3xl font-bold text-gray-900">
                                                ¥{{ number_format($campsite->price_per_night) }}
                                            </div>
                                            @if ($campsite->hasWeekendSurcharge())
                                                <div class="text-xs text-amber-600 mt-0.5 font-medium">
                                                    土日 ¥{{ number_format($campsite->weekendPrice()) }}
                                                </div>
                                            @endif
                                            <div class="text-xs text-green-600 mt-1 font-medium">無料キャンセル可</div>
                                        </div>

                                        {{-- CTAボタン --}}
                                        <div class="space-y-2">
                                            <a href="{{ route('campsites.show', $campsite) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                               class="btn-ripple block w-full h-11 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold text-sm rounded-xl text-center leading-[44px] transition-all shadow-md shadow-[#e07b39]/20 hover:shadow-lg">
                                                🏕️ 空き確認・予約
                                            </a>
                                            <a href="{{ route('campsites.show', $campsite) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                               class="block w-full h-9 bg-white hover:bg-[#f0ece3] border border-[#e0d8cc] text-gray-600 text-sm rounded-xl text-center leading-9 transition-colors">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $campsites->links() }}
                    </div>
                @endif
                </div>{{-- /リストビュー --}}
            </section>
        </div>
    </div>

    {{-- Leaflet JS + マップ初期化 --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 比較ストア
        document.addEventListener('alpine:init', () => {
            Alpine.store('compare', {
                ids: [],
                toggle(id) {
                    const i = this.ids.indexOf(id);
                    if (i >= 0) {
                        this.ids.splice(i, 1);
                    } else if (this.ids.length < 3) {
                        this.ids.push(id);
                    }
                },
                clear() { this.ids = []; },
            });
        });

        // Alpine の view が 'map' になったら一度だけ初期化
        let mapInitialized = false;
        document.addEventListener('alpine:initialized', () => {
            const section = document.querySelector('section[x-data]');
            if (!section) return;
            // x-show の style display 変化を監視
            const mapEl = document.getElementById('campsites-map');
            if (!mapEl) return;
            new MutationObserver(() => {
                if (mapEl.offsetParent !== null && !mapInitialized) {
                    mapInitialized = true;
                    initMap();
                }
            }).observe(mapEl, { attributes: true, attributeFilter: ['style'] });
        });

        function initMap() {
            @php
                $mapData = $campsites->filter(fn($c) => $c->latitude && $c->longitude)->map(fn($c) => [
                    'id'    => $c->id,
                    'name'  => $c->name,
                    'price' => $c->price_per_night,
                    'type'  => $c->type,
                    'lat'   => (float) $c->latitude,
                    'lng'   => (float) $c->longitude,
                    'url'   => route('campsites.show', $c),
                    'image' => $c->images->isNotEmpty() ? asset('storage/' . $c->images->first()->image_path) : null,
                ])->values();
            @endphp
            const campsites = @json($mapData);

            // 全サイトの平均座標 or 日本の中心
            const centerLat = campsites.length ? campsites.reduce((s, c) => s + c.lat, 0) / campsites.length : 36.5;
            const centerLng = campsites.length ? campsites.reduce((s, c) => s + c.lng, 0) / campsites.length : 137.5;

            const map = L.map('campsites-map').setView([centerLat, centerLng], campsites.length ? 7 : 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(map);

            const typeColors = { tent: '#16a34a', auto: '#2563eb', bungalow: '#d97706', glamping: '#7c3aed' };

            campsites.forEach(site => {
                const color = typeColors[site.type] || '#2d5a1b';
                const icon = L.divIcon({
                    className: '',
                    html: `<div style="background:${color};color:white;font-size:11px;font-weight:700;padding:4px 8px;border-radius:6px;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,0.3);">¥${Math.round(site.price/1000)}k</div>`,
                    iconAnchor: [20, 12],
                });
                const marker = L.marker([site.lat, site.lng], { icon }).addTo(map);
                marker.bindPopup(`
                    <div style="min-width:200px">
                        ${site.image ? `<img src="${site.image}" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px">` : ''}
                        <div style="font-size:12px;color:#666">${({tent:'テント',auto:'オート',bungalow:'バンガロー',glamping:'グランピング'})[site.type] || site.type}</div>
                        <div style="font-weight:700;font-size:14px;margin:2px 0">${site.name}</div>
                        <div style="font-size:13px;color:#e07b39;font-weight:700">¥${site.price.toLocaleString()}/泊</div>
                        <a href="${site.url}" style="display:block;margin-top:8px;background:#e07b39;color:white;text-align:center;padding:6px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none">詳細・予約</a>
                    </div>
                `);
            });

            if (campsites.length === 0) {
                document.getElementById('campsites-map').innerHTML =
                    '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#999;font-size:14px;">地図情報のあるサイトがありません</div>';
            }
        }
    </script>

    {{-- AI 自然言語検索コンポーネント --}}
    <script>
        function aiSearch() {
            return {
                query: '',
                loading: false,
                applied: false,
                error: null,

                async search() {
                    if (!this.query.trim()) return;
                    this.loading = true;
                    this.error   = null;

                    try {
                        const res = await fetch('{{ route('search.natural') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                            },
                            body: JSON.stringify({ query: this.query }),
                        });

                        const data = await res.json();

                        if (data.error === 'no_key') {
                            this.error = 'AI検索を使用するには ANTHROPIC_API_KEY を設定してください。';
                            return;
                        }
                        if (data.error) {
                            this.error = 'AI検索でエラーが発生しました。通常の検索をお試しください。';
                            return;
                        }

                        const f = data.filters ?? {};
                        const params = new URLSearchParams(window.location.search);

                        if (f.type)       params.set('type', f.type);
                        if (f.guests)     params.set('guests', f.guests);
                        if (f.price_max)  params.set('price_max', f.price_max);
                        if (f.price_min)  params.set('price_min', f.price_min);
                        if (f.min_rating) params.set('min_rating', f.min_rating);
                        if (f.amenity_ids?.length) {
                            params.delete('amenity_ids[]');
                            f.amenity_ids.forEach(id => params.append('amenity_ids[]', id));
                        }

                        this.applied = true;
                        window.location.href = '{{ route('campsites.index') }}?' + params.toString();
                    } catch (e) {
                        this.error = 'ネットワークエラーが発生しました。';
                    } finally {
                        this.loading = false;
                    }
                },

                clear() {
                    this.applied = false;
                    this.query   = '';
                    window.location.href = '{{ route('campsites.index') }}';
                },
            };
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-app-layout>
