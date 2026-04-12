<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-10">

        {{-- ヘッダー --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#2d5a1b]">お問い合わせ</h1>
            <p class="mt-1 text-sm text-gray-500">ご不明な点・ご要望などお気軽にご連絡ください。通常2営業日以内にご返答いたします。</p>
        </div>

        @error('submit')
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        {{-- フォーム --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                @csrf

                {{-- 名前 --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        お名前 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', auth()->user()?->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] focus:border-transparent @error('name') border-red-400 @enderror"
                           placeholder="山田 太郎">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- メール --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        メールアドレス <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', auth()->user()?->email) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] focus:border-transparent @error('email') border-red-400 @enderror"
                           placeholder="example@email.com">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 件名 --}}
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                        件名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="subject" name="subject"
                           value="{{ old('subject') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] focus:border-transparent @error('subject') border-red-400 @enderror"
                           placeholder="予約についてのご質問">
                    @error('subject')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 本文 --}}
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                        お問い合わせ内容 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="body" name="body" rows="6"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] focus:border-transparent resize-none @error('body') border-red-400 @enderror"
                              placeholder="ご質問・ご要望の内容をご記入ください（最大2000文字）">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 送信ボタン --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-[#e07b39] hover:bg-[#c4621a] text-white font-semibold py-3 rounded-lg transition-colors text-sm">
                        送信する
                    </button>
                </div>
            </form>
        </div>

        {{-- FAQリンク --}}
        <div class="mt-6 bg-green-50 border border-green-100 rounded-xl p-5">
            <p class="text-sm font-medium text-[#2d5a1b] mb-2">よくあるご質問</p>
            <ul class="space-y-1.5 text-sm text-gray-600">
                <li>・ キャンセルはチェックイン前日まで無料でできます</li>
                <li>・ チェックイン 15:00〜 / チェックアウト 〜11:00</li>
                <li>・ 設備・アメニティは各サイトの詳細ページでご確認いただけます</li>
                <li>・ ペット可否はサイトにより異なります</li>
            </ul>
            <p class="mt-3 text-xs text-gray-400">画面右下のチャットでもすぐにご回答できます。</p>
        </div>

    </div>
</x-app-layout>
