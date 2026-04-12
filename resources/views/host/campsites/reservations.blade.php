<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('host.campsites.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← サイト一覧</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">予約一覧: {{ $campsite->name }}</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- サマリー --}}
            @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-3 gap-4 mb-6">
                @php
                    $total     = $reservations->total();
                    $confirmed = $reservations->where('status', 'confirmed')->count();
                    $revenue   = $reservations->where('status', 'confirmed')->sum('total_price');
                @endphp
                @foreach ([
                    ['総予約数', $total,                        '#2d5a1b'],
                    ['確定済み', $confirmed . '件',             '#16a34a'],
                    ['売上合計', '¥' . number_format($revenue), '#e07b39'],
                ] as [$label, $value, $color])
                    <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-4 text-center">
                        <div class="text-xl font-bold" style="color:{{ $color }}">{{ $value }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                @if ($reservations->isEmpty())
                    <div class="p-10 text-center text-gray-400">
                        <div class="text-4xl mb-3">📋</div>
                        <p class="text-sm">このサイトへの予約はまだありません</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                                    <th class="px-6 py-3 text-left font-semibold">予約者</th>
                                    <th class="px-6 py-3 text-left font-semibold">チェックイン</th>
                                    <th class="px-6 py-3 text-left font-semibold">チェックアウト</th>
                                    <th class="px-6 py-3 text-center font-semibold">人数</th>
                                    <th class="px-6 py-3 text-right font-semibold">金額</th>
                                    <th class="px-6 py-3 text-center font-semibold">ステータス</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($reservations as $res)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $res->user->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $res->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $res->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $res->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-700">{{ $res->num_guests }}名</td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            ¥{{ number_format($res->total_price) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if ($res->status === 'pending')
                                                <form method="POST"
                                                      action="{{ route('host.reservations.approve', $res) }}"
                                                      class="inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                            class="text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 hover:bg-green-100 hover:text-green-700 transition-colors border border-yellow-200 hover:border-green-200">
                                                        保留 → 承認
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                                    {{ match($res->status) {
                                                        'confirmed' => 'bg-green-100 text-green-700',
                                                        'cancelled' => 'bg-red-100 text-red-700',
                                                        default     => 'bg-gray-100 text-gray-600',
                                                    } }}">
                                                    {{ match($res->status) { 'confirmed'=>'確定', 'cancelled'=>'キャンセル', default=>$res->status } }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $reservations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
