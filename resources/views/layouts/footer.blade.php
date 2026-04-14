<footer class="mt-auto border-t border-white/10 bg-[#1c3a0e] text-sm text-green-100/85">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 py-8 sm:flex-row sm:px-6 lg:px-8">
        <p class="order-2 text-center text-xs text-green-200/60 sm:order-1 sm:text-left">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
        <nav class="order-1 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 sm:order-2" aria-label="フッター">
            <a href="{{ route('home') }}" class="font-semibold text-white transition hover:text-[#a8d878]">トップ</a>
            <a href="{{ route('campsites.index') }}" class="font-semibold text-white transition hover:text-[#a8d878]">サイトを探す</a>
            <a href="{{ route('contact.create') }}" class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-4 py-2 font-bold text-white transition hover:border-[#a8d878]/50 hover:bg-white/15">
                お問い合わせ
            </a>
        </nav>
    </div>
</footer>
