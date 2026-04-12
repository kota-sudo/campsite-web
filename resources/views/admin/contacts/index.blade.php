<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">管理画面 — お問い合わせ</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            {{-- 管理ナビ --}}
            <div class="flex gap-4 mb-6 text-sm">
                <a href="{{ route('admin.campsites.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">キャンプサイト</a>
                <a href="{{ route('admin.reservations.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">予約管理</a>
                <span class="font-semibold text-[#2d5a1b] border-b-2 border-[#2d5a1b] pb-1">
                    お問い合わせ
                    @if ($newCount > 0)
                        <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $newCount }}</span>
                    @endif
                </span>
            </div>

            {{-- フィルター --}}
            <form method="GET" class="flex gap-3 mb-6 flex-wrap items-center">
                <select name="status"
                        class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">すべてのステータス</option>
                    <option value="new"         {{ request('status') === 'new'         ? 'selected' : '' }}>新規</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>対応中</option>
                    <option value="resolved"    {{ request('status') === 'resolved'    ? 'selected' : '' }}>解決済み</option>
                </select>
                <x-primary-button type="submit">絞り込み</x-primary-button>
                <a href="{{ route('admin.contacts.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">
                    クリア
                </a>
            </form>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日時</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名前</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">件名</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e0d8cc]">
                        @forelse ($contacts as $contact)
                            <tr x-data="{ open: false }" class="hover:bg-gray-50">
                                {{-- ID --}}
                                <td class="px-4 py-3 text-gray-400 text-xs">#{{ $contact->id }}</td>

                                {{-- 日時 --}}
                                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $contact->created_at->format('m/d H:i') }}
                                </td>

                                {{-- 名前・メール --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $contact->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $contact->email }}</div>
                                </td>

                                {{-- 件名（クリックで本文展開） --}}
                                <td class="px-4 py-3 max-w-xs">
                                    <button type="button" @click="open = !open"
                                            class="text-left text-[#2d5a1b] hover:underline font-medium truncate block max-w-xs">
                                        {{ $contact->subject }}
                                    </button>
                                    <div x-show="open" x-cloak class="mt-2 p-3 bg-[#f9f5ef] rounded text-xs text-gray-600 whitespace-pre-wrap border border-gray-200">{{ $contact->body }}</div>
                                </td>

                                {{-- ステータスバッジ --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $contact->statusColor() }}">
                                        {{ $contact->statusLabel() }}
                                    </span>
                                </td>

                                {{-- インライン更新フォーム --}}
                                <td class="px-4 py-3">
                                    <button type="button" @click="open = !open"
                                            class="text-xs text-blue-600 hover:underline mr-2">
                                        <span x-text="open ? '閉じる' : '詳細'"></span>
                                    </button>
                                    <button type="button"
                                            x-data
                                            @click="$dispatch('open-contact-modal-{{ $contact->id }}')"
                                            class="text-xs text-gray-500 hover:text-gray-800 border border-gray-300 rounded px-2 py-0.5 hover:bg-gray-100 transition">
                                        編集
                                    </button>
                                </td>
                            </tr>

                            {{-- 編集モーダル --}}
                            <tr>
                                <td colspan="6" class="p-0">
                                    <div x-data="{ show: false }"
                                         x-on:open-contact-modal-{{ $contact->id }}.window="show = true"
                                         x-show="show" x-cloak
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                                         @click.self="show = false">
                                        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                                            <h3 class="font-semibold text-[#2d5a1b] mb-4">お問い合わせ #{{ $contact->id }} — 編集</h3>

                                            <div class="mb-4 p-3 bg-[#f9f5ef] rounded text-sm text-gray-700 whitespace-pre-wrap border border-gray-100 max-h-40 overflow-y-auto">{{ $contact->body }}</div>

                                            <form method="POST" action="{{ route('admin.contacts.update', $contact) }}" class="space-y-4">
                                                @csrf
                                                @method('PATCH')

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">ステータス</label>
                                                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b]">
                                                        <option value="new"         {{ $contact->status === 'new'         ? 'selected' : '' }}>新規</option>
                                                        <option value="in_progress" {{ $contact->status === 'in_progress' ? 'selected' : '' }}>対応中</option>
                                                        <option value="resolved"    {{ $contact->status === 'resolved'    ? 'selected' : '' }}>解決済み</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">管理者メモ（任意）</label>
                                                    <textarea name="admin_note" rows="3"
                                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] resize-none"
                                                              placeholder="対応内容、メモなど">{{ $contact->admin_note }}</textarea>
                                                </div>

                                                <div class="flex gap-3 pt-1">
                                                    <button type="submit"
                                                            class="flex-1 bg-[#2d5a1b] hover:bg-[#142e09] text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                                                        更新する
                                                    </button>
                                                    <button type="button" @click="show = false"
                                                            class="flex-1 border border-gray-300 text-gray-600 text-sm py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                                        キャンセル
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                                    お問い合わせはありません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーション --}}
            @if ($contacts->hasPages())
                <div class="mt-6">
                    {{ $contacts->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
