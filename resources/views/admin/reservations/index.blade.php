<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">管理画面 — 予約管理</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- 管理ナビ --}}
            <div class="flex gap-4 mb-6 text-sm">
                <a href="{{ route('admin.campsites.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">キャンプサイト</a>
                <span class="font-semibold text-[#2d5a1b] border-b-2 border-[#2d5a1b] pb-1">予約管理</span>
            </div>

            {{-- フィルター --}}
            <form method="GET" class="flex gap-3 mb-6 flex-wrap">
                <select name="status"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">
                    <option value="">すべてのステータス</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>確認中</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>確定</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                </select>
                <x-primary-button type="submit">絞り込み</x-primary-button>
                <a href="{{ route('admin.reservations.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-sm hover:bg-gray-300">
                    クリア
                </a>
            </form>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ユーザー</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">サイト</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">日程</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">金額</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($reservations as $reservation)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-gray-400">#{{ $reservation->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $reservation->user->name }}</p>
                                    <p class="text-gray-400 text-xs">{{ $reservation->user->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $reservation->campsite->name }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $reservation->check_in_date->format('Y/m/d') }}
                                    〜 {{ $reservation->check_out_date->format('Y/m/d') }}
                                    ({{ $reservation->nights() }}泊)
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">¥{{ number_format($reservation->total_price) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                        {{ match($reservation->status) {
                                            'pending'   => 'bg-yellow-100 text-yellow-700',
                                            'confirmed' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-gray-100 text-gray-500',
                                        } }}">
                                        {{ match($reservation->status) {
                                            'pending'   => '確認中',
                                            'confirmed' => '確定',
                                            'cancelled' => 'キャンセル',
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}"
                                          class="flex items-center gap-2">
                                        @csrf @method('PATCH')
                                        <select name="status"
                                                class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-xs py-1">
                                            <option value="pending"   {{ $reservation->status === 'pending'   ? 'selected' : '' }}>確認中</option>
                                            <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>確定</option>
                                            <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                                        </select>
                                        <button type="submit" class="text-xs px-2 py-1 bg-[#2d5a1b] text-white rounded hover:bg-[#1c3a0e]">
                                            更新
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $reservations->links() }}</div>
        </div>
    </div>
</x-app-layout>
