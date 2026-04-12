<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('host.dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm">← ダッシュボード</a>
                <span class="text-gray-400">/</span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">登録サイト管理</h2>
            </div>
            <a href="{{ route('host.campsites.create') }}"
               class="px-4 py-2 bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-bold rounded-lg transition-colors">
                + 新規登録
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($campsites->isEmpty())
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-12 text-center">
                    <div class="text-5xl mb-4">⛺</div>
                    <h3 class="text-lg font-bold text-gray-700 mb-2">サイトが登録されていません</h3>
                    <a href="{{ route('host.campsites.create') }}"
                       class="inline-block mt-3 px-6 py-2 bg-[#e07b39] text-white font-bold rounded-lg text-sm hover:bg-[#c4621a] transition">
                        最初のサイトを登録する
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($campsites as $site)
                        <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden hover:shadow-md transition">
                            <div class="grid grid-cols-1 md:grid-cols-[140px_minmax(0,1fr)_auto] items-start">
                                <div class="h-32 md:h-full bg-gray-100">
                                    @if ($site->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $site->images->first()->image_path) }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-4xl text-gray-300">⛺</div>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs px-2 py-0.5 rounded font-medium
                                            {{ $site->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $site->is_active ? '公開中' : '非公開' }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ match($site->type) { 'tent'=>'テント', 'auto'=>'オート', 'bungalow'=>'バンガロー', 'glamping'=>'グランピング' } }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $site->name }}</h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span>¥{{ number_format($site->price_per_night) }}/泊</span>
                                        <span>最大{{ $site->capacity }}名</span>
                                        <span class="text-[#2d5a1b] font-medium">{{ $site->reservations->count() }}件の有効予約</span>
                                    </div>
                                    @if ($site->address)
                                        <p class="mt-1 text-xs text-gray-400">📍 {{ $site->address }}</p>
                                    @endif
                                </div>
                                <div class="px-5 pb-5 md:pt-5 flex flex-row md:flex-col gap-2">
                                    <a href="{{ route('host.campsites.reservations', $site) }}"
                                       class="text-xs px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 text-center transition">
                                        予約一覧
                                    </a>
                                    <a href="{{ route('host.campsites.edit', $site) }}"
                                       class="text-xs px-4 py-2 rounded-lg bg-[#2d5a1b] hover:bg-[#142e09] text-white text-center transition">
                                        編集する
                                    </a>
                                    @if ($site->is_active || !$site->is_active)
                                        <form method="POST"
                                              action="{{ route('host.campsites.toggle-active', $site) }}"
                                              onsubmit="return confirm('{{ $site->is_active ? 'このサイトを非公開にしますか？' : 'このサイトを公開しますか？' }}')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="w-full text-xs px-4 py-2 rounded-lg border transition text-center
                                                        {{ $site->is_active
                                                            ? 'border-red-200 text-red-500 hover:bg-red-50'
                                                            : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                                                {{ $site->is_active ? '非公開にする' : '公開する' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $campsites->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
