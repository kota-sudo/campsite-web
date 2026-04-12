<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">管理ダッシュボード</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- KPIカード --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ([
                    ['総予約数',     $totalReservations,              '#2d5a1b', '📋'],
                    ['確定済み',     $confirmedCount,                 '#16a34a', '✅'],
                    ['キャンセル',   $cancelledCount,                 '#dc2626', '❌'],
                    ['総売上',       '¥' . number_format($totalRevenue), '#e07b39', '💰'],
                    ['アクティブサイト', $totalCampsites,             '#7c3aed', '⛺'],
                    ['登録ユーザー', $totalUsers,                     '#0891b2', '👤'],
                ] as [$label, $value, $color, $icon])
                    <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xl">{{ $icon }}</span>
                            <span class="w-2 h-2 rounded-full" style="background:{{ $color }}"></span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            {{-- グラフエリア --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- 月別予約数 --}}
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4">月別予約数（過去12ヶ月）</h3>
                    <div style="position:relative; height:220px;">
                        <canvas id="reservationsChart"></canvas>
                    </div>
                </div>

                {{-- 月別売上 --}}
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4">月別売上（過去12ヶ月）</h3>
                    <div style="position:relative; height:220px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ステータス内訳 + サイト稼働率 --}}
            <div class="grid grid-cols-1 lg:grid-cols-[280px_minmax(0,1fr)] gap-6">

                {{-- ドーナツチャート --}}
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4">予約ステータス内訳</h3>
                    <div style="position:relative; height:180px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 space-y-2">
                        @foreach ([
                            ['pending',   '保留中',    '#f59e0b'],
                            ['confirmed', '確定済み',  '#16a34a'],
                            ['cancelled', 'キャンセル','#dc2626'],
                        ] as [$key, $label, $color])
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $color }}"></span>
                                    <span class="text-gray-600">{{ $label }}</span>
                                </span>
                                <span class="font-semibold text-gray-900">{{ $statusCounts[$key] ?? 0 }}件</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- サイト稼働率ランキング --}}
                <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4">サイト別稼働率 Top10</h3>
                    <div class="space-y-3">
                        @forelse ($siteStats as $i => $site)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="flex-shrink-0 w-5 h-5 rounded text-xs font-bold flex items-center justify-center
                                            {{ $i === 0 ? 'bg-yellow-400 text-white' : ($i === 1 ? 'bg-gray-300 text-gray-700' : ($i === 2 ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-500')) }}">
                                            {{ $i + 1 }}
                                        </span>
                                        <span class="text-sm text-gray-800 font-medium truncate">{{ $site->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0 ml-2">
                                        <span class="text-xs text-gray-500">¥{{ number_format($site->total_revenue) }}</span>
                                        <span class="text-xs font-bold text-[#2d5a1b] w-10 text-right">{{ $site->occupancy_rate }}%</span>
                                    </div>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                         style="width:{{ $site->occupancy_rate }}%; background:{{ $site->occupancy_rate >= 70 ? '#16a34a' : ($site->occupancy_rate >= 40 ? '#e07b39' : '#94a3b8') }}">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">データなし</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 直近の予約 --}}
            <div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">直近の予約</h3>
                    <a href="{{ route('admin.reservations.index') }}" class="text-sm text-[#2d5a1b] hover:underline">
                        すべて見る →
                    </a>
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
                                        <div class="text-xs text-gray-400">{{ $res->created_at->isoFormat('M月D日 HH:mm') }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">{{ $res->campsite->name }}</td>
                                    <td class="px-6 py-3 text-gray-600">
                                        {{ $res->check_in_date->isoFormat('M/D') }} 〜 {{ $res->check_out_date->isoFormat('M/D') }}
                                        <span class="text-xs text-gray-400">({{ $res->nights() }}泊)</span>
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
                                            {{ match($res->status) {
                                                'confirmed' => '確定',
                                                'pending'   => '保留',
                                                'cancelled' => 'キャンセル',
                                                default     => $res->status,
                                            } }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const monthlyData = @json($monthlyData);
        const labels = monthlyData.map(d => {
            const [y, m] = d.month.split('-');
            return m + '月';
        });

        // 月別予約数
        new Chart(document.getElementById('reservationsChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: '予約数',
                    data: monthlyData.map(d => d.count),
                    backgroundColor: '#2d5a1b',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 月別売上
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: '売上',
                    data: monthlyData.map(d => d.revenue),
                    borderColor: '#e07b39',
                    backgroundColor: 'rgba(224,94,40,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#e07b39',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: { callback: v => '¥' + v.toLocaleString() }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // ステータスドーナツ
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['保留中', '確定済み', 'キャンセル'],
                datasets: [{
                    data: [
                        {{ $statusCounts['pending']   ?? 0 }},
                        {{ $statusCounts['confirmed'] ?? 0 }},
                        {{ $statusCounts['cancelled'] ?? 0 }},
                    ],
                    backgroundColor: ['#f59e0b', '#16a34a', '#dc2626'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw + '件' } }
                },
                cutout: '65%',
            }
        });
    </script>
</x-app-layout>
