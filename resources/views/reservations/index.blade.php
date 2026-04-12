<x-app-layout>

    <div class="bg-[#2d5a1b] border-b border-white/10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <h1 class="text-xl font-bold text-white">マイ予約</h1>
            <p class="text-sm text-green-300 mt-0.5">予約の確認・キャンセルはこちらから</p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($reservations->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-14 text-center">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">予約履歴がありません</h3>
                <p class="text-sm text-gray-500 mb-5">お気に入りのキャンプサイトを見つけて予約しましょう</p>
                <a href="{{ route('campsites.index') }}"
                   class="inline-block px-6 py-2.5 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm transition-colors">
                    サイトを探す
                </a>
            </div>
        @else
            {{-- ステータスタブ --}}
            @php
                $all       = $reservations->total();
                $upcoming  = $reservations->filter(fn($r) => in_array($r->status, ['pending','confirmed']) && $r->check_in_date->isFuture())->count();
                $past      = $reservations->filter(fn($r) => $r->check_out_date->isPast() && $r->status !== 'cancelled')->count();
                $cancelled = $reservations->filter(fn($r) => $r->status === 'cancelled')->count();
            @endphp

            <div class="space-y-3">
                @foreach ($reservations as $reservation)
                    @php
                        $isUpcoming  = in_array($reservation->status, ['pending','confirmed']) && $reservation->check_in_date->isFuture();
                        $isPast      = $reservation->check_out_date->isPast() && $reservation->status !== 'cancelled';
                        $isCancelled = $reservation->status === 'cancelled';
                        $img         = $reservation->campsite->images->first();
                    @endphp
                    <a href="{{ route('reservations.show', $reservation) }}"
                       class="block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-[120px_minmax(0,1fr)_auto] items-stretch">

                            {{-- サムネイル --}}
                            <div class="h-32 sm:h-auto bg-gray-100">
                                @if ($img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}"
                                         class="w-full h-full object-cover" alt="{{ $reservation->campsite->name }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl text-gray-300">⛺</div>
                                @endif
                            </div>

                            {{-- 予約情報 --}}
                            <div class="p-4 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                            {{ match($reservation->status) {
                                                'confirmed' => 'bg-green-100 text-green-700',
                                                'pending'   => 'bg-yellow-100 text-yellow-700',
                                                'cancelled' => 'bg-gray-100 text-gray-500',
                                            } }}">
                                            {{ match($reservation->status) {
                                                'confirmed' => '予約確定',
                                                'pending'   => '確認中',
                                                'cancelled' => 'キャンセル済',
                                            } }}
                                        </span>
                                        @if ($isPast && $reservation->status === 'confirmed' && !$reservation->review)
                                            <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-medium">
                                                レビュー未投稿
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ $reservation->campsite->name }}</h3>
                                    <p class="text-xs text-gray-500">
                                        {{ match($reservation->campsite->type) {
                                            'tent'     => 'テントサイト',
                                            'auto'     => 'オートキャンプ',
                                            'bungalow' => 'バンガロー',
                                            'glamping' => 'グランピング',
                                        } }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $reservation->check_in_date->isoFormat('M月D日(ddd)') }}
                                        〜
                                        {{ $reservation->check_out_date->isoFormat('M月D日(ddd)') }}
                                    </span>
                                    <span class="text-gray-400">{{ $reservation->nights() }}泊 / {{ $reservation->num_guests }}名</span>
                                </div>
                            </div>

                            {{-- 金額 + 矢印 --}}
                            <div class="px-5 py-4 border-t sm:border-t-0 sm:border-l border-gray-100 bg-gray-50 flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 min-w-[120px]">
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">合計</div>
                                    <div class="text-xl font-bold text-gray-900">¥{{ number_format($reservation->total_price) }}</div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $reservations->links() }}</div>
        @endif
    </div>
</x-app-layout>
