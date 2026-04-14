<x-app-layout>
    @section('title', '自然の中へ — 全国キャンプサイト予約')
    @section('og_title', config('app.name') . ' — 自然の中で、非日常を。')

    {{-- ヒーロー: 森と星空の全画面 --}}
    <div id="hero-section" class="relative overflow-hidden" style="min-height: 92vh;">

        {{-- 空のグラデーション --}}
        <div class="absolute inset-0"
             style="background: linear-gradient(180deg, #060e14 0%, #0d1f2d 20%, #1a3a2a 50%, #2d5a1b 75%, #3a7020 100%);">
        </div>

        {{-- 星 (twinkle アニメーション付き) --}}
        <div id="stars-container" class="absolute inset-0 pointer-events-none parallax-slow">
            @for ($i = 0; $i < 100; $i++)
                @php
                    $x     = rand(0, 100);
                    $y     = rand(0, 50);
                    $size  = rand(1, 3);
                    $op    = rand(35, 85) / 100;
                    $dur   = rand(20, 60) / 10;
                    $delay = rand(0, 50) / 10;
                @endphp
                <div class="star absolute rounded-full bg-white"
                     style="left:{{ $x }}%;top:{{ $y }}%;
                            width:{{ $size }}px;height:{{ $size }}px;
                            --star-op:{{ $op }};--star-dur:{{ $dur }}s;--star-delay:{{ $delay }}s;">
                </div>
            @endfor
        </div>

        {{-- 月 --}}
        <div class="moon-glow absolute top-10 right-20 w-16 h-16 rounded-full parallax-mid"
             style="background: radial-gradient(circle at 35% 35%, #fffde0, #f5d78a);"></div>

        {{-- 山のシルエット (パララックス別レイヤー) --}}
        <svg class="absolute bottom-0 left-0 w-full parallax-slow" viewBox="0 0 1440 320" preserveAspectRatio="none" style="height:320px;">
            <path d="M0 320 L0 200 L120 110 L240 170 L360 80 L480 150 L600 60 L720 130 L840 50 L960 120 L1080 40 L1200 110 L1320 70 L1440 130 L1440 320 Z"
                  fill="#1a3a2a" opacity="0.65"/>
        </svg>
        <svg class="absolute bottom-0 left-0 w-full parallax-mid" viewBox="0 0 1440 320" preserveAspectRatio="none" style="height:280px;">
            <path d="M0 320 L0 260 L180 160 L320 220 L500 130 L680 200 L820 110 L1000 190 L1160 120 L1300 180 L1440 140 L1440 320 Z"
                  fill="#1c3a0e" opacity="0.9"/>
        </svg>

        {{-- 森（揺れる木々） --}}
        <div class="absolute bottom-0 left-0 w-full parallax-fast" style="height: 130px;">
            <svg viewBox="0 0 1440 130" preserveAspectRatio="none" style="width:100%;height:100%;">
                {{-- 木のグループを複数配置 --}}
                @php
                    $trees = [];
                    for ($t = 0; $t < 60; $t++) {
                        $tx = $t * 24 + rand(-8, 8);
                        $th = rand(55, 100);
                        $tw = rand(18, 32);
                        $trees[] = [$tx, $th, $tw];
                    }
                @endphp
                @foreach ($trees as [$tx, $th, $tw])
                    <polygon
                        points="{{ $tx }},130 {{ $tx - $tw/2 }},{{ 130 - $th }} {{ $tx + $tw/2 }},{{ 130 - $th }}"
                        fill="#0d2208"/>
                @endforeach
            </svg>
        </div>

        {{-- テント --}}
        <div class="absolute bottom-28 left-[28%] pointer-events-none hidden lg:block tree-sway" style="transform-origin: bottom center;">
            <svg viewBox="0 0 80 50" class="w-20 h-12 opacity-90">
                <path d="M40 5 L5 45 L40 38 L75 45 Z" fill="#c4621a" opacity="0.9"/>
                <path d="M40 5 L5 45 L40 38 Z" fill="#a34f14" opacity="0.7"/>
                <path d="M32 38 L48 38 L48 45 L32 45 Z" fill="#0d1a0a"/>
            </svg>
        </div>
        <div class="absolute bottom-28 right-[26%] pointer-events-none hidden lg:block tree-sway-2" style="transform-origin: bottom center;">
            <svg viewBox="0 0 60 40" class="w-16 h-10 opacity-75">
                <path d="M30 4 L4 36 L30 30 L56 36 Z" fill="#3d6b1a" opacity="0.9"/>
                <path d="M30 4 L4 36 L30 30 Z" fill="#2d5014" opacity="0.7"/>
                <path d="M24 30 L36 30 L36 36 L24 36 Z" fill="#0d1a0a"/>
            </svg>
        </div>

        {{-- 焚き火 --}}
        <div class="absolute bottom-28 left-1/2 -translate-x-1/2 pointer-events-none select-none" style="width:90px;">

            {{-- 地面の光反射 --}}
            <div class="ground-light absolute"
                 style="bottom:-6px;left:50%;transform:translateX(-50%);width:140px;height:18px;
                        background:radial-gradient(ellipse, rgba(245,166,35,.55), transparent 70%);
                        border-radius:50%;"></div>

            {{-- 大グロー --}}
            <div class="fire-glow absolute"
                 style="bottom:4px;left:50%;transform:translateX(-50%);width:180px;height:60px;border-radius:50%;
                        background:radial-gradient(ellipse, rgba(245,120,20,.55), rgba(245,166,35,.25) 50%, transparent 75%);"></div>

            {{-- 火の粉 (8粒) --}}
            <div class="ember" style="bottom:48px;left:45%;--em-dur:2.1s;--em-delay:0s;   --ex:-6px; --ex2:-14px;--ex3:-4px;"></div>
            <div class="ember" style="bottom:44px;left:55%;--em-dur:1.7s;--em-delay:.4s;  --ex: 8px; --ex2: 18px;--ex3: 6px;"></div>
            <div class="ember" style="bottom:50px;left:40%;--em-dur:2.4s;--em-delay:.9s;  --ex:-10px;--ex2:-8px; --ex3:-12px;background:#ff8833;"></div>
            <div class="ember" style="bottom:42px;left:60%;--em-dur:1.9s;--em-delay:1.3s; --ex: 12px;--ex2: 6px; --ex3: 14px;background:#ff8833;"></div>
            <div class="ember" style="bottom:52px;left:50%;--em-dur:2.6s;--em-delay:.2s;  --ex: 4px; --ex2: 16px;--ex3: 2px; width:2px;height:2px;"></div>
            <div class="ember" style="bottom:46px;left:48%;--em-dur:1.5s;--em-delay:1.7s; --ex:-4px; --ex2:-10px;--ex3:-2px;width:2px;height:2px;"></div>
            <div class="ember" style="bottom:40px;left:53%;--em-dur:2.2s;--em-delay:.6s;  --ex: 6px; --ex2: 4px; --ex3: 8px; width:2px;height:2px;background:#ffee88;"></div>
            <div class="ember" style="bottom:54px;left:44%;--em-dur:1.8s;--em-delay:2.1s; --ex:-8px; --ex2:-12px;--ex3:-6px;width:2px;height:2px;background:#ffee88;"></div>

            {{-- 煙 --}}
            <div style="position:absolute;bottom:80px;left:50%;transform:translateX(-50%);">
                <div class="smoke-particle" style="left:-4px;"></div>
                <div class="smoke-particle" style="left: 4px;"></div>
                <div class="smoke-particle" style="left: 0;"></div>
                <div class="smoke-particle" style="left:-8px;animation-delay:2.4s;"></div>
            </div>

            {{-- 炎: 外 --}}
            <div class="flame-outer absolute left-1/2 -translate-x-1/2"
                 style="bottom:20px;width:36px;height:70px;
                        background:radial-gradient(ellipse at 50% 100%, #c4621a 0%, #e07b39 30%, #f5a623 60%, rgba(255,220,80,.35) 82%, transparent 100%);
                        border-radius:60% 60% 30% 30% / 80% 80% 20% 20%;">
            </div>
            {{-- 炎: 中 --}}
            <div class="flame-inner absolute left-1/2 -translate-x-1/2"
                 style="bottom:20px;width:22px;height:56px;
                        background:radial-gradient(ellipse at 50% 100%, #f5c94a 0%, #f5a623 45%, rgba(245,166,35,.3) 80%, transparent 100%);
                        border-radius:60% 60% 30% 30% / 80% 80% 20% 20%;">
            </div>
            {{-- 炎: 芯 --}}
            <div class="flame-core absolute left-1/2 -translate-x-1/2"
                 style="bottom:20px;width:10px;height:34px;
                        background:radial-gradient(ellipse at 50% 100%, #ffffff, #fffde0 40%, #fff5aa 70%, transparent);
                        border-radius:60% 60% 30% 30% / 80% 80% 20% 20%;
                        filter:blur(.5px);">
            </div>

            {{-- 石のリング --}}
            <div class="absolute" style="bottom:14px;left:50%;transform:translateX(-50%);width:72px;height:12px;">
                <div style="position:absolute;width:13px;height:10px;background:#5a4a3a;border-radius:50% 50% 40% 40%;bottom:0;left:0;"></div>
                <div style="position:absolute;width:11px;height:8px; background:#4a3a2a;border-radius:50% 50% 40% 40%;bottom:0;left:14px;"></div>
                <div style="position:absolute;width:12px;height:10px;background:#5e4e3c;border-radius:50% 50% 40% 40%;bottom:0;left:26px;"></div>
                <div style="position:absolute;width:10px;height:9px; background:#4a3a2a;border-radius:50% 50% 40% 40%;bottom:0;left:39px;"></div>
                <div style="position:absolute;width:13px;height:11px;background:#5a4a3a;border-radius:50% 50% 40% 40%;bottom:0;left:50px;"></div>
            </div>

            {{-- 丸太 --}}
            <div class="absolute" style="bottom:18px;left:50%;width:60px;height:10px;background:linear-gradient(180deg,#6b3d14,#3d2408);border-radius:5px;transform:translateX(-50%) rotate(24deg);transform-origin:center;box-shadow:0 2px 4px rgba(0,0,0,.4);"></div>
            <div class="absolute" style="bottom:18px;left:50%;width:60px;height:10px;background:linear-gradient(180deg,#7a4518,#4a2e0a);border-radius:5px;transform:translateX(-50%) rotate(-24deg);transform-origin:center;box-shadow:0 2px 4px rgba(0,0,0,.4);"></div>
            {{-- 燃えている端 --}}
            <div class="flame-core absolute" style="bottom:18px;left:calc(50% + 22px);width:5px;height:8px;background:radial-gradient(#f5a623,transparent);border-radius:50%;opacity:.7;"></div>
            <div class="flame-core absolute" style="bottom:18px;left:calc(50% - 27px);width:5px;height:8px;background:radial-gradient(#f5a623,transparent);border-radius:50%;opacity:.7;animation-delay:.3s;"></div>
        </div>

        {{-- メインコンテンツ --}}
        <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 pt-14 pb-72 sm:pt-16">

            <div class="hero-badges inline-flex items-center gap-2 bg-white/15 backdrop-blur-md text-green-50 text-xs font-bold px-4 py-2 rounded-full border border-white/30 shadow-lg shadow-black/10 mb-5">
                <span class="h-2 w-2 shrink-0 rounded-full bg-campfire-400 shadow-[0_0_10px_rgba(245,166,35,0.9)] animate-pulse"></span>
                日程を入れて、空きのあるサイトを今すぐチェック
            </div>

            <h1 class="hero-title text-5xl lg:text-7xl font-black text-white leading-none tracking-tight mb-4"
                style="text-shadow: 0 4px 24px rgba(0,0,0,.6);">
                自然の中へ<span class="text-[#a8d878]">。</span>
            </h1>
            <p class="hero-sub mb-8 text-lg lg:text-xl text-green-100/85 max-w-xl leading-relaxed"
               style="text-shadow: 0 2px 12px rgba(0,0,0,.5);">
                テント泊からグランピングまで。<br>
                星空の下で過ごす、最高の夜を見つけよう。
            </p>

            {{-- 検索カード --}}
            <div id="hero-search" class="hero-search scroll-mt-28 w-full max-w-4xl">
                <div class="rounded-3xl border-2 border-white/70 bg-white p-5 shadow-[0_16px_56px_rgba(0,0,0,0.45),0_0_0_1px_rgba(45,90,27,0.06)] ring-4 ring-campfire-400/25 sm:p-6">
                    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between sm:text-left">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-forest-600">空き検索</p>
                            <p class="text-base font-black text-gray-900 sm:text-lg">日程と人数を入れて、サイトを探す</p>
                        </div>
                        <a href="{{ route('campsites.index') }}"
                           class="hidden text-sm font-semibold text-forest-700 underline decoration-forest-300 decoration-2 underline-offset-2 hover:text-forest-900 sm:inline">
                            条件を決めずに一覧へ →
                        </a>
                    </div>
                    <form method="GET" action="{{ route('campsites.index') }}">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_1fr_0.62fr_0.78fr_auto] sm:items-end">
                            <div>
                                <label class="mb-1.5 flex items-center gap-1 text-xs font-bold text-gray-600">
                                    <svg class="h-3.5 w-3.5 text-forest-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                    出発日
                                </label>
                                <input type="date" name="check_in" min="{{ date('Y-m-d') }}"
                                       class="h-12 w-full rounded-xl border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none transition focus:border-forest-600 focus:ring-4 focus:ring-forest-600/15">
                            </div>
                            <div>
                                <label class="mb-1.5 flex items-center gap-1 text-xs font-bold text-gray-600">
                                    <svg class="h-3.5 w-3.5 text-forest-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                    帰着日
                                </label>
                                <input type="date" name="check_out" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       class="h-12 w-full rounded-xl border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none transition focus:border-forest-600 focus:ring-4 focus:ring-forest-600/15">
                            </div>
                            <div>
                                <label class="mb-1.5 flex items-center gap-1 text-xs font-bold text-gray-600">
                                    <svg class="h-3.5 w-3.5 text-forest-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                    人数
                                </label>
                                <input type="number" name="guests" min="1" max="20" placeholder="例: 2"
                                       class="h-12 w-full rounded-xl border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none transition focus:border-forest-600 focus:ring-4 focus:ring-forest-600/15">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-600">スタイル</label>
                                <select name="type"
                                        class="h-12 w-full cursor-pointer rounded-xl border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none transition focus:border-forest-600 focus:ring-4 focus:ring-forest-600/15">
                                    <option value="">すべて</option>
                                    <option value="tent">⛺ テント泊</option>
                                    <option value="auto">🚗 オートキャンプ</option>
                                    <option value="bungalow">🏠 バンガロー</option>
                                    <option value="glamping">✨ グランピング</option>
                                </select>
                            </div>
                            <button type="submit"
                                    class="btn-ripple flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-campfire-500 to-campfire-600 px-6 text-base font-black text-white shadow-[0_6px_24px_rgba(224,123,57,0.45)] transition hover:from-campfire-400 hover:to-campfire-500 hover:shadow-[0_8px_28px_rgba(224,123,57,0.55)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:h-[3.25rem] sm:w-auto sm:min-w-[10.5rem] sm:self-end sm:text-base">
                                <svg class="h-5 w-5 shrink-0 opacity-95" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                                空きを検索
                            </button>
                        </div>
                    </form>
                    <p class="mt-3 text-center text-xs text-gray-500 sm:text-left">
                        未入力の項目は指定なしで検索できます。
                    </p>
                    <a href="{{ route('campsites.index') }}"
                       class="mt-2 block text-center text-sm font-semibold text-forest-700 underline decoration-forest-300 decoration-2 underline-offset-2 hover:text-forest-900 sm:hidden">
                        条件を決めずに一覧を見る →
                    </a>
                </div>
            </div>

            {{-- クイックリンク --}}
            <div class="hero-links mt-6 flex flex-wrap justify-center gap-2 sm:mt-7">
                @foreach ([
                    ['tent', '⛺', 'テント泊'],
                    ['auto', '🚗', 'オートキャンプ'],
                    ['bungalow', '🏠', 'バンガロー'],
                    ['glamping', '✨', 'グランピング'],
                ] as [$type, $icon, $label])
                    <a href="{{ route('campsites.index', ['type' => $type]) }}"
                       class="flex items-center gap-1.5 rounded-full border border-white/35 bg-white/15 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-black/10 backdrop-blur-sm transition-all hover:scale-[1.04] hover:border-white/50 hover:bg-white/25">
                        {{ $icon }} {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- コンテンツエリア --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 space-y-20">

        {{-- キャンプスタイルから探す --}}
        <section>
            <div class="reveal flex items-end justify-between mb-8">
                <div>
                    <p class="text-xs font-bold text-[#2d5a1b] uppercase tracking-widest mb-1">FIND YOUR STYLE</p>
                    <h2 class="text-3xl font-black text-gray-900">どんな夜を過ごしたい？</h2>
                </div>
                <a href="{{ route('campsites.index') }}"
                   class="hidden items-center gap-2 rounded-full border-2 border-forest-600 bg-forest-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-forest-900/15 transition hover:bg-forest-700 hover:border-forest-700 sm:inline-flex">
                    すべて見る
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ([
                    ['tent',     'テント泊',       '自然と一体になる<br>原点のキャンプ体験',        'tent.jpg',     1, true],
                    ['auto',     'オートキャンプ', '車で乗り込む<br>ファミリーの定番スタイル',     'auto.jpg',     2, false],
                    ['bungalow', 'バンガロー',     'テント不要<br>手ぶらでアウトドア入門',          'bungalow.jpg', 3, false],
                    ['glamping', 'グランピング',   'おしゃれな空間で<br>上質な野外体験を',           'glamping.jpg', 4, false],
                ] as [$type, $name, $desc, $image, $delay, $zoomCover])
                    <a href="{{ route('campsites.index', ['type' => $type]) }}"
                       class="reveal reveal-delay-{{ $delay }} group relative isolate rounded-2xl sm:rounded-3xl overflow-hidden aspect-[3/4] sm:aspect-[4/5] text-white transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl ring-1 ring-black/10 shadow-lg">
                        <img src="{{ asset('images/find-your-style/'.$image) }}"
                             alt="{{ $name }}の雰囲気"
                             class="absolute inset-0 z-0 h-full w-full object-cover transition-transform duration-700 ease-out will-change-transform {{ $zoomCover ? 'scale-[1.22] sm:scale-110 group-hover:scale-[1.28] sm:group-hover:scale-[1.14]' : 'scale-105 group-hover:scale-110' }}"
                             loading="lazy"
                             decoding="async"
                             width="800"
                             height="1000">
                        <div class="absolute inset-0 z-[1] pointer-events-none"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.94) 0%, rgba(0,0,0,0.82) 22%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.22) 58%, transparent 76%);"></div>
                        <div class="absolute inset-0 z-[2] flex flex-col justify-end p-4 sm:p-5">
                            <h3 class="text-lg font-black leading-tight text-white sm:text-xl [text-shadow:0_1px_2px_rgba(0,0,0,0.95),0_2px_16px_rgba(0,0,0,0.85)] group-hover:translate-x-0.5 transition-transform">
                                {{ $name }}
                            </h3>
                            <p class="mt-1.5 text-[11px] leading-relaxed !text-white sm:text-xs [text-shadow:0_1px_2px_rgba(0,0,0,0.95),0_1px_12px_rgba(0,0,0,0.8)]">{!! $desc !!}</p>
                            <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-3.5 py-1.5 text-[11px] font-bold text-white shadow-md shadow-black/30 transition-all group-hover:bg-blue-500 group-hover:gap-2">
                                <span>詳しく見る</span>
                                <svg class="h-3.5 w-3.5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- キャンプの魅力（写真ベース） --}}
        <section class="reveal overflow-hidden rounded-3xl ring-1 ring-black/10 shadow-xl">
            <div class="relative isolate aspect-[4/3] min-h-[280px] sm:aspect-[21/9] sm:min-h-[300px] lg:min-h-[340px]">
                <img src="{{ asset('images/why-camping/stars.jpg') }}"
                     alt="満天の星空と天の川"
                     class="absolute inset-0 h-full w-full object-cover object-center"
                     loading="lazy"
                     decoding="async"
                     width="1200"
                     height="630">
                <div class="absolute inset-0 pointer-events-none"
                     style="background: linear-gradient(to bottom, rgba(6,12,18,0.35) 0%, transparent 38%, rgba(5,10,8,0.25) 55%, rgba(5,10,8,0.88) 100%);"></div>
                <div class="relative z-[1] flex h-full flex-col items-center justify-end px-6 pb-10 pt-16 text-center sm:justify-center sm:pb-12 sm:pt-12 lg:px-12">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-300/90 [text-shadow:0_1px_3px_rgba(0,0,0,0.9)]">WHY CAMPING</p>
                    <h2 class="mt-3 max-w-3xl text-3xl font-black leading-tight text-white sm:text-4xl lg:text-[2.65rem] [text-shadow:0_2px_4px_rgba(0,0,0,0.85),0_4px_24px_rgba(0,0,0,0.75)]">
                        なぜ、キャンプなのか。
                    </h2>
                    <p class="mt-4 max-w-xl text-sm leading-relaxed !text-stone-100 sm:text-base [text-shadow:0_1px_3px_rgba(0,0,0,0.95),0_2px_14px_rgba(0,0,0,0.85)]">
                        スマホを置いて、自然の中へ。焚き火を囲み、星を眺める。
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 bg-[#0d160a] sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['fire.jpg',   '焚き火の夜',   '炎を囲んで語らう時間。スマホも仕事も、全部忘れられる。',   1],
                    ['stars.jpg',  '満天の星空',   '都市では見えない星が、無数に輝く。子どもも大人も感動する空。', 2],
                    ['forest.jpg', '森林浴の朝',   '鳥のさえずりで目覚める朝。体も心もリセットされる感覚。', 3],
                    ['food.jpg',   '外ご飯の旨さ', '炭火で焼いた肉は、なぜあんなに美味しいのか。外飯の魔法。', 4],
                ] as [$img, $title, $desc, $delay])
                    <div class="reveal reveal-delay-{{ $delay }} group relative aspect-[5/4] min-h-[200px] overflow-hidden sm:aspect-[4/3] sm:min-h-0 lg:aspect-square">
                        <img src="{{ asset('images/why-camping/'.$img) }}"
                             alt="{{ $title }}のイメージ"
                             class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                             loading="lazy"
                             decoding="async"
                             width="800"
                             height="640">
                        <div class="absolute inset-0 pointer-events-none"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 42%, transparent 72%);"></div>
                        <div class="absolute inset-0 flex flex-col justify-end p-4 sm:p-5">
                            <h3 class="text-base font-bold leading-snug text-white [text-shadow:0_1px_3px_rgba(0,0,0,0.9),0_2px_14px_rgba(0,0,0,0.75)]">
                                {{ $title }}
                            </h3>
                            <p class="mt-1.5 text-xs leading-relaxed !text-stone-100 [text-shadow:0_1px_2px_rgba(0,0,0,0.95),0_1px_10px_rgba(0,0,0,0.8)]">
                                {{ $desc }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 使い方 --}}
        <section class="reveal">
            <div class="mx-auto mb-12 max-w-2xl text-center md:mb-14">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-forest-600">HOW IT WORKS</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-gray-900">かんたん3ステップ</h2>
            </div>
            <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl border border-parchment-300/80 bg-white shadow-[0_2px_24px_rgba(45,90,27,0.06)]">
                <ol class="divide-y divide-parchment-300/70 md:grid md:grid-cols-3 md:divide-x md:divide-y-0">
                    @foreach ([
                        ['01', 'サイトを探す', '日程・人数・スタイルから全国のサイトを検索。地図や写真で詳細をチェック。', 1, 'search'],
                        ['02', '空きを確認して予約', 'カレンダーで空きを確認し、その場で予約完了。面倒なやり取りは不要。', 2, 'calendar'],
                        ['03', '自然を満喫！', '確認メールを受け取ったら荷造りするだけ。非日常の時間が待っています。', 3, 'sun'],
                    ] as [$num, $title, $desc, $delay, $icon])
                        <li class="reveal reveal-delay-{{ $delay }} group relative px-8 py-10 transition-colors md:px-10 md:py-12 md:text-center hover:bg-parchment-50/60">
                            <div class="mb-6 flex items-center gap-4 md:mx-auto md:mb-8 md:flex-col md:gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-forest-600 text-[11px] font-black tracking-wide text-white ring-4 ring-forest-600/10">
                                    {{ $num }}
                                </span>
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-parchment-300/90 bg-parchment-50/80 text-forest-700 transition group-hover:border-forest-200 group-hover:bg-white group-hover:text-forest-600">
                                    @if ($icon === 'search')
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                                    @elseif ($icon === 'calendar')
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5"/></svg>
                                    @else
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                                    @endif
                                </span>
                            </div>
                            <h3 class="text-lg font-bold tracking-tight text-gray-900">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ $desc }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- CTA --}}
        <section class="reveal relative min-h-[300px] overflow-hidden rounded-3xl px-5 py-16 text-center sm:min-h-[340px] sm:px-8 sm:py-20 lg:min-h-[380px] lg:py-24">
            <img src="{{ asset('images/cta-campfire.jpg') }}"
                 alt=""
                 class="absolute inset-0 h-full w-full object-cover object-center"
                 width="1200"
                 height="675"
                 loading="lazy"
                 decoding="async">
            <div class="pointer-events-none absolute inset-0"
                 style="background: linear-gradient(to top, rgba(0,0,0,0.93) 0%, rgba(0,0,0,0.65) 32%, rgba(0,0,0,0.38) 55%, rgba(0,0,0,0.2) 100%);"></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#1c3a0e]/50 via-transparent to-black/40 mix-blend-multiply"></div>
            <div class="relative z-10 mx-auto max-w-2xl">
                <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-[#c8e6a8] [text-shadow:0_1px_4px_rgba(0,0,0,0.85)]">さあ、次の一歩</p>
                <h2 class="mb-4 text-3xl font-black leading-tight text-white sm:text-4xl lg:text-[2.5rem] [text-shadow:0_2px_4px_rgba(0,0,0,0.9),0_4px_28px_rgba(0,0,0,0.65)]">
                    今週末、どこで焚き火する？
                </h2>
                <p class="mx-auto mb-10 max-w-md text-base leading-relaxed !text-stone-100 sm:text-lg [text-shadow:0_1px_3px_rgba(0,0,0,0.9),0_2px_16px_rgba(0,0,0,0.75)]">
                    全国のキャンプサイトを一括検索。<br class="hidden sm:inline">空きがあれば、その場で予約まで完了できます。
                </p>
                <div class="flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <a href="{{ route('campsites.index') }}"
                       class="btn-ripple inline-flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-b from-campfire-500 to-campfire-600 px-8 py-4 text-lg font-black text-white shadow-[0_12px_40px_rgba(0,0,0,0.38)] ring-2 ring-white/25 transition hover:from-campfire-400 hover:to-campfire-500 hover:ring-white/40 sm:min-w-[280px] sm:px-10 sm:py-[1.15rem]">
                        <svg class="h-6 w-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                        キャンプサイトを探す
                    </a>
                    <a href="{{ route('home') }}#hero-search"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-white/55 bg-white/10 px-6 py-3.5 text-sm font-bold text-white backdrop-blur-sm transition hover:border-white/80 hover:bg-white/20 sm:shrink-0 sm:py-4">
                        <svg class="h-5 w-5 shrink-0 opacity-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/></svg>
                        日程から探す
                    </a>
                </div>
                @guest
                    <p class="mt-6 text-xs !text-stone-200/90 [text-shadow:0_1px_3px_rgba(0,0,0,0.85)]">
                        <a href="{{ route('register') }}" class="font-semibold text-white underline decoration-white/50 underline-offset-2 hover:text-[#e8ffc8]">無料登録</a>
                        <span class="text-stone-200/80">で予約・お気に入りをまとめて管理</span>
                    </p>
                @endguest
            </div>
        </section>
    </div>
</x-app-layout>
