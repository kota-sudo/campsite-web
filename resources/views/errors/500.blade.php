<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 サーバーエラー | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-[#f2f2f2] min-h-screen flex flex-col">
    <div class="bg-[#2d5a1b] py-3 px-6">
        <a href="{{ url('/') }}" class="text-white font-bold flex items-center gap-2 w-fit">
            <span class="text-xl">⛺</span>
            <span>{{ config('app.name') }}</span>
        </a>
    </div>
    <div class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="text-center max-w-md">
            <p class="text-7xl font-bold text-[#2d5a1b]/20 mb-4 leading-none">500</p>
            <div class="text-5xl mb-6">⚡</div>
            <h1 class="text-2xl font-bold text-[#2d5a1b] mb-3">サーバーエラーが発生しました</h1>
            <p class="text-gray-500 text-sm mb-8 leading-relaxed">
                申し訳ありません。一時的な問題が発生しています。<br>
                しばらく時間をおいてから再度お試しください。
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url('/') }}"
                   class="bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold px-6 py-2.5 rounded-lg text-sm transition-colors">
                    トップへ戻る
                </a>
                <a href="{{ route('contact.create') }}"
                   class="border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
                    お問い合わせ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
