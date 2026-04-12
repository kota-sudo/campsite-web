<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ホストダッシュボード</h2>
            <a href="{{ route('host.campsites.create') }}"
               class="px-4 py-2 bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-bold rounded-lg transition-colors">
                + 新しいサイトを登録
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- KPI --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([
                    ['登録サイト数',   $campsites->count(),                   '⛺', '#2d5a1b'],
                    ['対応待ち予約',   $pendingCount,                         '⏳', '#d97706'],
                    ['累計売上',       '¥' . number_format($totalRevenue),    '💰', '#e07b39'],
                    ['直近の予約',     $recentReservations->count(),          '📋', '#16a34a'],
                ] as [$label, $value, $icon, $color])
                    <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">{{ $icon }}</span>
                            <span class="w-2 h-2 rounded-full" style="background:{{ $color }}"></span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            {{-- 月別売上グラフ --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">月別売上（過去6ヶ月）</h3>
                <div style="position:relative;height:200px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- サイト一覧 --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">登録サイト</h3>
                    <a href="{{ route('host.campsites.index') }}" class="text-sm text-[#2d5a1b] hover:underline">管理 →</a>
                </div>
                @if ($campsites->isEmpty())
                    <div class="p-10 text-center text-gray-400">
                        <div class="text-4xl mb-3">⛺</div>
                        <p class="text-sm">まだサイトが登録されていません</p>
                        <a href="{{ route('host.campsites.create') }}" class="mt-3 inline-block text-sm text-[#e07b39] font-semibold hover:underline">
                            最初のサイトを登録する
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach ($campsites as $site)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                        @if ($site->images->isNotEmpty())
                                            <img src="{{ asset('storage/' . $site->images->first()->image_path) }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-xl">⛺</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $site->name }}</div>
                                        <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                            <span>¥{{ number_format($site->price_per_night) }}/泊</span>
                                            <span>·</span>
                                            <span>{{ $site->active_reservations }}件の有効予約</span>
                                            <span>·</span>
                                            <span class="{{ $site->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                {{ $site->is_active ? '公開中' : '審査中' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('host.campsites.reservations', $site) }}"
                                       class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 transition">
                                        予約一覧
                                    </a>
                                    <a href="{{ route('host.campsites.edit', $site) }}"
                                       class="text-xs px-3 py-1.5 rounded-lg bg-[#2d5a1b] text-white hover:bg-[#142e09] transition">
                                        編集
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 直近の予約 --}}
            @if ($recentReservations->isNotEmpty())
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-900">直近の予約</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                                    <th class="px-6 py-3 text-left font-semibold">予約者</th>
                                    <th class="px-6 py-3 text-left font-semibold">サイト</th>
                                    <th class="px-6 py-3 text-left font-semibold">日程</th>
                                    <th class="px-6 py-3 text-right font-semibold">金額</th>
                                    <th class="px-6 py-3 text-center font-semibold">ステータス</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($recentReservations as $res)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3">
                                            <div class="font-medium text-gray-900">{{ $res->user->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $res->created_at->isoFormat('M月D日') }}</div>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700 text-xs">{{ $res->campsite->name }}</td>
                                        <td class="px-6 py-3 text-gray-600 text-xs">
                                            {{ $res->check_in_date->isoFormat('M/D') }} 〜 {{ $res->check_out_date->isoFormat('M/D') }}
                                        </td>
                                        <td class="px-6 py-3 text-right font-semibold text-gray-900">
                                            ¥{{ number_format($res->total_price) }}
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                                {{ match($res->status) {
                                                    'confirmed' => 'bg-green-100 text-green-700',
                                                    'pending'   => 'bg-yellow-100 text-yellow-700',
                                                    'cancelled' => 'bg-red-100 text-red-700',
                                                    default     => 'bg-gray-100 text-gray-600',
                                                } }}">
                                                {{ match($res->status) { 'confirmed'=>'確定', 'pending'=>'保留', 'cancelled'=>'キャンセル', default=>$res->status } }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const monthly = @json($monthlyData);
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: monthly.map(d => { const [y,m] = d.month.split('-'); return m+'月'; }),
                datasets: [
                    {
                        label: '売上',
                        data: monthly.map(d => d.revenue),
                        backgroundColor: '#e07b39',
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: '予約数',
                        type: 'line',
                        data: monthly.map(d => d.count),
                        borderColor: '#2d5a1b',
                        pointBackgroundColor: '#2d5a1b',
                        borderWidth: 2,
                        pointRadius: 4,
                        yAxisID: 'y2',
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: {
                    y:  { beginAtZero: true, ticks: { callback: v => '¥'+v.toLocaleString() }, grid: { color: '#f3f4f6' } },
                    y2: { beginAtZero: true, position: 'right', ticks: { stepSize: 1 }, grid: { display: false } },
                    x:  { grid: { display: false } }
                }
            }
        });
    </script>
</x-app-layout>
