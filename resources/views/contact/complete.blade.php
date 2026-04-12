<x-app-layout>
    <div class="max-w-lg mx-auto px-4 py-16 text-center">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-10">
            {{-- アイコン --}}
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-xl font-bold text-[#2d5a1b] mb-3">お問い合わせを受け付けました</h1>
            <p class="text-sm text-gray-500 leading-relaxed mb-8">
                お問い合わせいただきありがとうございます。<br>
                通常2営業日以内にご入力のメールアドレスへご返答いたします。<br>
                しばらくお待ちください。
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('campsites.index') }}"
                   class="bg-[#e07b39] hover:bg-[#c4621a] text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    サイト一覧へ
                </a>
                @auth
                    <a href="{{ route('reservations.index') }}"
                       class="border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                        マイ予約を見る
                    </a>
                @endauth
            </div>
        </div>

    </div>
</x-app-layout>
