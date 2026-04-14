<x-app-layout>
    @section('title', 'お問い合わせ')
    @section('description', 'ご質問・ご要望はお問い合わせフォームから。通常2営業日以内にメールでご返答します。')

    <div class="relative overflow-hidden border-b border-parchment-300/80 bg-gradient-to-b from-forest-900 via-[#1c3a0e] to-forest-900">
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
             style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.35) 0, transparent 45%), radial-gradient(circle at 80% 70%, rgba(168,216,120,0.25) 0, transparent 40%);"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-12 text-center sm:py-14">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-green-300/90">CONTACT</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-white sm:text-4xl">お問い合わせ</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-green-100/85">
                ご予約・サイト情報・不具合など、お気軽にご連絡ください。<br class="hidden sm:inline">
                通常 <span class="font-semibold text-[#f5e6a3]">2営業日以内</span> にメールでお返事します。
            </p>
            <a href="{{ route('home') }}"
               class="mt-6 inline-flex items-center gap-1.5 text-xs font-semibold text-white/80 transition hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                トップへ戻る
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-xl px-4 py-10 sm:py-12">
        @error('submit')
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                {{ $message }}
            </div>
        @enderror

        <div class="rounded-3xl border border-parchment-300/90 bg-white p-6 shadow-[0_8px_40px_rgba(45,90,27,0.08)] sm:p-8">
            <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-forest-50 text-forest-700" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </span>
                        お名前
                        <span class="text-xs font-normal text-campfire-600">必須</span>
                    </label>
                    <input type="text" id="name" name="name" autocomplete="name" required maxlength="100"
                           value="{{ old('name', auth()->user()?->name) }}"
                           class="h-12 w-full rounded-xl border-2 border-parchment-300 bg-parchment-50/40 px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/15 @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 @enderror"
                           placeholder="例：山田 太郎">
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-forest-50 text-forest-700" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </span>
                        メールアドレス
                        <span class="text-xs font-normal text-campfire-600">必須</span>
                    </label>
                    <input type="email" id="email" name="email" autocomplete="email" required maxlength="200"
                           value="{{ old('email', auth()->user()?->email) }}"
                           class="h-12 w-full rounded-xl border-2 border-parchment-300 bg-parchment-50/40 px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/15 @error('email') border-red-400 focus:border-red-500 focus:ring-red-200 @enderror"
                           placeholder="例：you@example.com">
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-forest-50 text-forest-700" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 18H15a2.25 2.25 0 002.25-2.25V15a2.25 2.25 0 00-2.25-2.25H8.25A2.25 2.25 0 006 15v3.75A2.25 2.25 0 008.25 21z"/></svg>
                        </span>
                        件名
                        <span class="text-xs font-normal text-campfire-600">必須</span>
                    </label>
                    <input type="text" id="subject" name="subject" list="subject-suggestions" required maxlength="200"
                           value="{{ old('subject') }}"
                           class="h-12 w-full rounded-xl border-2 border-parchment-300 bg-parchment-50/40 px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/15 @error('subject') border-red-400 focus:border-red-500 focus:ring-red-200 @enderror"
                           placeholder="例：予約の変更について">
                    <datalist id="subject-suggestions">
                        <option value="予約・キャンセルについて">
                        <option value="サイトの設備・アメニティについて">
                        <option value="決済・領収書について">
                        <option value="アカウント・ログインについて">
                        <option value="サイト掲載・提携について">
                        <option value="不具合・エラーの報告">
                    </datalist>
                    @error('subject')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-forest-50 text-forest-700" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        </span>
                        お問い合わせ内容
                        <span class="text-xs font-normal text-campfire-600">必須</span>
                    </label>
                    <textarea id="body" name="body" rows="7" required maxlength="2000"
                              class="w-full resize-y rounded-xl border-2 border-parchment-300 bg-parchment-50/40 px-4 py-3 text-sm leading-relaxed text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/15 @error('body') border-red-400 focus:border-red-500 focus:ring-red-200 @enderror"
                              placeholder="できるだけ具体的にご記入ください（サイト名・予約番号があるとスムーズです）">{{ old('body') }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">最大 2,000 文字</p>
                    @error('body')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-parchment-200 bg-parchment-50/50 px-4 py-3 text-xs leading-relaxed text-gray-600">
                    送信内容は当サービスのサポート担当が確認します。お急ぎの場合は画面右下のチャットもご利用ください。
                </div>

                <button type="submit"
                        class="btn-ripple flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-b from-campfire-500 to-campfire-600 py-4 text-base font-black text-white shadow-[0_8px_28px_rgba(224,123,57,0.35)] transition hover:from-campfire-400 hover:to-campfire-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    送信する
                </button>
            </form>
        </div>

        <div class="mt-8 rounded-3xl border border-forest-200/60 bg-forest-50/40 px-5 py-5 sm:px-6">
            <p class="text-sm font-bold text-forest-800">よくあるご質問</p>
            <ul class="mt-3 space-y-2 text-sm text-gray-600">
                <li class="flex gap-2"><span class="text-forest-500" aria-hidden="true">・</span>キャンセルはチェックイン前日まで無料の場合がほとんどです（サイトにより異なります）</li>
                <li class="flex gap-2"><span class="text-forest-500" aria-hidden="true">・</span>設備・アメニティは各サイトの詳細ページでご確認ください</li>
                <li class="flex gap-2"><span class="text-forest-500" aria-hidden="true">・</span>ペット可否はサイトにより異なります</li>
            </ul>
        </div>
    </div>
</x-app-layout>
