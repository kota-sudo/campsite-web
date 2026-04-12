<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.campsites.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← サイト一覧</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">新規キャンプサイト追加</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('admin.campsites._form', ['campsite' => null, 'amenities' => $amenities, 'action' => route('admin.campsites.store'), 'method' => 'POST'])
        </div>
    </div>
</x-app-layout>
