<x-app-layout>
    @section('title', '送信完了')
    @section('description', 'お問い合わせを受け付けました。')

    <div class="mx-auto max-w-lg px-4 py-16 sm:py-20">
        <div class="rounded-3xl border border-parchment-300/90 bg-white px-6 py-10 text-center shadow-[0_8px_40px_rgba(45,90,27,0.08)] sm:px-10 sm:py-12">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-forest-100 ring-4 ring-forest-600/10">
                <svg class="h-8 w-8 text-forest-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
            <h1 class="text-xl font-black text-forest-800 sm:text-2xl">お問い合わせを受け付けました</h1>
            <p class="mx-auto mt-4 max-w-sm text-sm leading-relaxed text-gray-600">
                ご連絡ありがとうございます。<br>
                通常 <span class="font-semibold text-forest-700">2営業日以内</span> に、ご入力のメールアドレスへ返信します。
            </p>
            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('campsites.index') }}"
                   class="btn-ripple inline-flex items-center justify-center rounded-2xl bg-gradient-to-b from-campfire-500 to-campfire-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-campfire-600/30 transition hover:from-campfire-400 hover:to-campfire-500">
                    サイトを探す
                </a>
                @auth
                    <a href="{{ route('reservations.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border-2 border-parchment-300 bg-parchment-50/50 px-6 py-3.5 text-sm font-bold text-gray-800 transition hover:border-forest-300 hover:bg-white">
                        マイ予約
                    </a>
                @endauth
            </div>
            <a href="{{ route('home') }}" class="mt-8 inline-block text-sm font-semibold text-forest-700 underline decoration-forest-300 underline-offset-2 hover:text-forest-900">
                トップへ戻る
            </a>
        </div>
    </div>
</x-app-layout>
