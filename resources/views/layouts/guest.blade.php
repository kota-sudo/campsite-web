<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
             style="background: linear-gradient(160deg, #0d2208 0%, #1c3a0e 40%, #2d5a1b 100%);">

            {{-- 星 --}}
            <div class="fixed inset-0 pointer-events-none overflow-hidden">
                @for ($i = 0; $i < 50; $i++)
                    @php $x = rand(0,100); $y = rand(0,50); $s = rand(1,2); @endphp
                    <div class="absolute rounded-full bg-white opacity-40"
                         style="left:{{$x}}%;top:{{$y}}%;width:{{$s}}px;height:{{$s}}px;"></div>
                @endfor
            </div>

            <div class="relative z-10 mb-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <span class="text-3xl">🏕️</span>
                    <span class="text-2xl font-black text-white tracking-wide group-hover:text-[#a8d878] transition-colors">
                        {{ config('app.name') }}
                    </span>
                </a>
            </div>

            <div class="relative z-10 w-full sm:max-w-md px-6 py-7 bg-white/95 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-2xl border border-white/30">
                {{ $slot }}
            </div>

            <p class="relative z-10 mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-green-300/70">
                <a href="{{ route('contact.create') }}" class="font-semibold text-white underline decoration-green-400/50 underline-offset-2 hover:text-[#a8d878]">お問い合わせ</a>
                <span class="text-green-400/40" aria-hidden="true">|</span>
                <span>© {{ date('Y') }} {{ config('app.name') }}</span>
            </p>
        </div>
    </body>
</html>
