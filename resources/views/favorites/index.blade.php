<x-app-layout>

    <div class="bg-[#2d5a1b] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-start justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-white">お気に入りサイト</h1>
                <p class="text-sm text-green-300 mt-0.5">保存したキャンプサイト一覧</p>
            </div>
            {{-- 共有ボタン --}}
            <div x-data="{ copied: false }" class="flex items-center gap-3">
                @if (auth()->user()->favorites_share_token)
                    @php $shareUrl = route('favorites.shared', auth()->user()->favorites_share_token); @endphp
                    <input type="text" readonly value="{{ $shareUrl }}"
                           class="hidden" id="share-url-input">
                    <button @click="navigator.clipboard.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="flex items-center gap-1.5 text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copied ? 'コピーしました！' : 'リンクをコピー'"></span>
                    </button>
                    <form method="POST" action="{{ route('favorites.share.revoke') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-green-300 hover:text-red-300 transition-colors">
                            共有を停止
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('favorites.share.generate') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-1.5 text-xs bg-[#e07b39] hover:bg-[#c4621a] text-white px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            リストを共有する
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if (session('share_url'))
        <div class="bg-green-50 border-b border-green-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3 text-sm text-blue-700">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span>共有リンクを作成しました：</span>
                <a href="{{ session('share_url') }}" target="_blank"
                   class="font-medium underline break-all">{{ session('share_url') }}</a>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-green-700">{{ session('success') }}</div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        @if ($campsites->isEmpty())
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-14 text-center">
                <div class="text-5xl mb-4">♡</div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">お気に入りがありません</h3>
                <p class="text-sm text-gray-500 mb-5">気になるサイトの ♡ をタップして保存しましょう</p>
                <a href="{{ route('campsites.index') }}"
                   class="inline-block px-6 py-2.5 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm transition-colors">
                    サイトを探す
                </a>
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4">
                <span class="font-semibold text-[#2d5a1b]">{{ $campsites->total() }}件</span> のお気に入り
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($campsites as $campsite)
                    @php
                        $score = $campsite->reviews->isNotEmpty() ? $campsite->averageRatingOutOf10() : null;
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
                    <div class="relative bg-white rounded-xl border border-[#e0d8cc] shadow-sm hover:shadow-md transition-shadow overflow-hidden">

                        {{-- ハートボタン --}}
                        <div class="absolute top-3 left-3 z-10"
                             x-data="favoriteToggle('{{ route('favorites.toggle', $campsite) }}', true)">
                            <button @click.prevent="toggle"
                                    class="w-9 h-9 flex items-center justify-center rounded-full bg-white/90 shadow-sm hover:bg-white transition"
                                    :title="favorited ? 'お気に入りから削除' : 'お気に入りに追加'">
                                <span x-text="favorited ? '♥' : '♡'"
                                      :class="favorited ? 'text-red-500 text-lg' : 'text-gray-400 text-lg'"></span>
                            </button>
                        </div>

                        {{-- スコアバッジ --}}
                        @if ($score !== null)
                            <div class="absolute top-3 right-3 z-10 w-9 h-9 bg-[#2d5a1b] rounded-lg flex items-center justify-center shadow">
                                <span class="text-white font-bold text-sm">{{ $score }}</span>
                            </div>
                        @endif

                        <a href="{{ route('campsites.show', $campsite) }}" class="block">
                            {{-- 画像 --}}
                            <div class="h-44 bg-gray-100">
                                @if ($campsite->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $campsite->images->first()->image_path) }}"
                                         alt="{{ $campsite->name }}" loading="lazy" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-4xl text-gray-300">⛺</div>
                                @endif
                            </div>

                            <div class="p-4">
                                {{-- タイプバッジ --}}
                                <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $typeColor }}">{{ $typeLabel }}</span>

                                <h3 class="mt-2 font-bold text-gray-900 leading-snug hover:text-[#2d5a1b] transition-colors">
                                    {{ $campsite->name }}
                                </h3>

                                @if ($campsite->address)
                                    <p class="text-xs text-gray-500 mt-1 truncate">📍 {{ $campsite->address }}</p>
                                @endif

                                <p class="text-xs text-gray-500 mt-1.5 line-clamp-2 leading-relaxed">{{ $campsite->description }}</p>

                                <div class="flex items-end justify-between mt-3">
                                    <div>
                                        <div class="text-xs text-gray-400">1泊あたり</div>
                                        <div class="text-xl font-bold text-gray-900">¥{{ number_format($campsite->price_per_night) }}</div>
                                    </div>
                                    @if ($campsite->reviews->isNotEmpty())
                                        <div class="text-right">
                                            <div class="text-xs text-gray-400">{{ $campsite->reviews->count() }}件</div>
                                            <div class="flex">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <span class="text-xs {{ $i <= round($score / 2) ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                                                @endfor
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4">
                            <a href="{{ route('campsites.show', $campsite) }}"
                               class="block w-full h-10 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold text-sm rounded-lg text-center leading-10 transition-colors">
                                空き確認・予約
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">{{ $campsites->links() }}</div>
        @endif
    </div>
</x-app-layout>
