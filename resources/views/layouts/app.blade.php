<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name')) | {{ config('app.name') }}</title>
        <meta name="description" content="@yield('description', '全国のキャンプサイトを簡単検索・予約。テント・オートキャンプ・バンガロー・グランピングが揃っています。')">

        {{-- OGP --}}
        <meta property="og:type"        content="website">
        <meta property="og:site_name"   content="{{ config('app.name') }}">
        <meta property="og:title"       content="@yield('og_title', config('app.name'))">
        <meta property="og:description" content="@yield('description', '全国のキャンプサイトを簡単検索・予約。テント・オートキャンプ・バンガロー・グランピングが揃っています。')">
        <meta property="og:image"       content="@yield('og_image', asset('images/ogp.png'))">
        <meta property="og:url"         content="{{ url()->current() }}">
        <meta name="twitter:card"       content="summary_large_image">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col bg-[#f0ece3]">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>

        {{-- チャットボットウィジェット --}}
        @include('layouts.chatbot')

    </body>
</html>
