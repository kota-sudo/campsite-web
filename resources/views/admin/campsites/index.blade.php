<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">管理画面 — キャンプサイト一覧</h2>
            <a href="{{ route('admin.campsites.create') }}"
               class="inline-flex items-center px-4 py-2 bg-[#2d5a1b] text-white text-sm rounded-md hover:bg-[#1c3a0e]">
                + 新規追加
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- 管理ナビ --}}
            <div class="flex gap-4 mb-6 text-sm">
                <span class="font-semibold text-[#2d5a1b] border-b-2 border-[#2d5a1b] pb-1">キャンプサイト</span>
                <a href="{{ route('admin.reservations.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">予約管理</a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">名前</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイプ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">定員</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">料金/泊</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">公開</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($campsites as $campsite)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-gray-400">#{{ $campsite->id }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $campsite->name }}</td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ match($campsite->type) {
                                        'tent'     => 'テント',
                                        'auto'     => 'オート',
                                        'bungalow' => 'バンガロー',
                                        'glamping' => 'グランピング',
                                    } }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $campsite->capacity }}名</td>
                                <td class="px-4 py-3 text-gray-500">¥{{ number_format($campsite->price_per_night) }}</td>
                                <td class="px-4 py-3">
                                    @if ($campsite->is_active)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">公開</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500">非公開</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.campsites.edit', $campsite) }}"
                                       class="text-[#2d5a1b] hover:text-[#1c3a0e] text-xs">編集</a>
                                    @if (! $campsite->is_active)
                                        <form method="POST" action="{{ route('admin.campsites.approve', $campsite) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-0.5 rounded transition-colors">
                                                承認・公開
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.campsites.destroy', $campsite) }}" class="inline"
                                              onsubmit="return confirm('非公開にしますか？')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">非公開</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $campsites->links() }}</div>
        </div>
    </div>
</x-app-layout>
