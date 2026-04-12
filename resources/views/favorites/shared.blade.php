<x-app-layout>
    <div class="bg-[#2d5a1b] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <p class="text-green-300 text-xs mb-1">共有リスト</p>
            <h1 class="text-xl font-bold text-white">{{ $user->name }} さんのお気に入りサイト</h1>
            <p class="text-sm text-green-300 mt-0.5">{{ $campsites->total() }}件のキャンプサイトを保存しています</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        @if ($campsites->isEmpty())
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-14 text-center">
                <div class="text-5xl mb-4">⛺</div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">まだお気に入りがありません</h3>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($campsites as $campsite)
                    @php
                        $score = $campsite->reviews->isNotEmpty() ? $campsite->averageRatingOutOf10() : null;
                        $typeLabel = match($campsite->type) {
                            'tent' => 'テントサイト', 'auto' => 'オートキャンプ',
                            'bungalow' => 'バンガロー', 'glamping' => 'グランピング',
                        };
                        $typeColor = match($campsite->type) {
                            'tent' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'auto' => 'bg-green-50 text-blue-700 border-blue-200',
                            'bungalow' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'glamping' => 'bg-purple-50 text-purple-700 border-purple-200',
                        };
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <div class="relative">
                            @if ($campsite->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $campsite->images->first()->image_path) }}"
                                     alt="{{ $campsite->name }}"
                                     class="w-full h-44 object-cover">
                            @else
                                <div class="w-full h-44 bg-gray-100 flex items-center justify-center text-4xl">⛺</div>
                            @endif
                            @if ($score !== null)
                                <div class="absolute top-3 right-3 w-9 h-9 bg-[#2d5a1b] rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xs">{{ $score }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $typeColor }}">{{ $typeLabel }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-1 leading-snug">{{ $campsite->name }}</h3>
                            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $campsite->description }}</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs text-gray-400">1泊</span>
                                    <span class="text-lg font-bold text-gray-900 ml-1">¥{{ number_format($campsite->price_per_night) }}</span>
                                </div>
                                <a href="{{ route('campsites.show', $campsite) }}"
                                   class="text-sm bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold px-4 py-1.5 rounded-lg transition-colors">
                                    詳細・予約
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $campsites->links() }}</div>
        @endif

        <div class="mt-8 text-center">
            <a href="{{ route('campsites.index') }}"
               class="inline-block text-sm text-[#2d5a1b] hover:underline">
                サイト一覧で他のキャンプサイトを探す →
            </a>
        </div>
    </div>
</x-app-layout>
