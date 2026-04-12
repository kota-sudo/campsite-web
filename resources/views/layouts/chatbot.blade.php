{{-- チャットボットウィジェット (右下固定) --}}
<div
    x-data="chatbot()"
    x-init="init()"
    class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3"
>
    {{-- チャットウィンドウ --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden"
        style="max-height: 520px;"
    >
        {{-- ヘッダー --}}
        <div class="bg-[#2d5a1b] px-4 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-[#e07b39] rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">⛺</div>
                <div>
                    <p class="text-white text-sm font-semibold leading-tight">キャンプくん</p>
                    <p class="text-green-300 text-xs leading-tight">AIサポート</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="resetChat()" title="会話をリセット"
                        class="text-green-300 hover:text-white transition-colors text-xs px-2 py-1 rounded hover:bg-white/10">
                    リセット
                </button>
                <button @click="open = false"
                        class="text-green-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- メッセージ一覧 --}}
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50" x-ref="messageArea">
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="msg.role === 'user'
                            ? 'bg-[#2d5a1b] text-white rounded-2xl rounded-br-sm max-w-[80%] px-4 py-2.5 text-sm'
                            : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm max-w-[80%] px-4 py-2.5 text-sm shadow-sm border border-gray-100'"
                        x-html="formatMessage(msg.content)"
                    ></div>
                </div>
            </template>

            {{-- ローディング --}}
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm border border-gray-100">
                    <div class="flex gap-1 items-center">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 入力エリア --}}
        <div class="px-3 py-3 bg-white border-t border-gray-100 flex-shrink-0">
            <form @submit.prevent="sendMessage()" class="flex gap-2">
                <input
                    type="text"
                    x-model="input"
                    :disabled="loading"
                    class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] focus:border-transparent disabled:bg-gray-50"
                    placeholder="メッセージを入力…"
                    maxlength="500"
                    autocomplete="off"
                >
                <button
                    type="submit"
                    :disabled="loading || input.trim() === ''"
                    class="w-9 h-9 bg-[#e07b39] hover:bg-[#c4621a] disabled:bg-gray-300 text-white rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- トグルボタン --}}
    <button
        @click="toggleChat()"
        class="w-14 h-14 bg-[#2d5a1b] hover:bg-[#142e09] text-white rounded-full shadow-lg flex items-center justify-center transition-all hover:scale-105 active:scale-95 relative"
    >
        <span x-show="!open" class="text-2xl">⛺</span>
        <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{-- 未読バッジ --}}
        <span x-show="unread > 0 && !open"
              x-cloak
              class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
              x-text="unread"></span>
    </button>
</div>

<script>
function chatbot() {
    return {
        open: false,
        loading: false,
        input: '',
        unread: 0,
        messages: [
            {
                role: 'assistant',
                content: 'こんにちは！CampsiteWebサポートの「キャンプくん」です ⛺\n\n予約・キャンセル・設備など、なんでもお気軽にご質問ください！'
            }
        ],

        init() {
            // セッションに履歴がある場合はウェルカムメッセージのみ
        },

        toggleChat() {
            this.open = !this.open;
            if (this.open) {
                this.unread = 0;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async sendMessage() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch('{{ route('chat.message') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: text }),
                });

                const data = await res.json();
                if (res.status === 429) {
                    this.messages.push({ role: 'assistant', content: data.reply || 'メッセージの送信が多すぎます。少し待ってから再度お試しください。' });
                } else {
                    this.messages.push({ role: 'assistant', content: data.reply });
                }

                if (!this.open) this.unread++;
            } catch {
                this.messages.push({ role: 'assistant', content: 'エラーが発生しました。しばらくしてから再度お試しください。' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async resetChat() {
            await fetch('{{ route('chat.reset') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            this.messages = [
                { role: 'assistant', content: 'こんにちは！CampsiteWebサポートの「キャンプくん」です ⛺\n\n予約・キャンセル・設備など、なんでもお気軽にご質問ください！' }
            ];
        },

        scrollToBottom() {
            const el = this.$refs.messageArea;
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatMessage(text) {
            // 改行→<br>、**太字**→<strong>
            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },
    }
}
</script>
