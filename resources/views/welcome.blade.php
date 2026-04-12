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
        <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 pt-16 pb-72">

            <div class="hero-badges inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-green-200 text-xs font-semibold px-4 py-1.5 rounded-full border border-white/20 mb-6">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                全国のキャンプサイトを今すぐ予約
            </div>

            <h1 class="hero-title text-5xl lg:text-7xl font-black text-white leading-none tracking-tight mb-4"
                style="text-shadow: 0 4px 24px rgba(0,0,0,.6);">
                自然の中へ<span class="text-[#a8d878]">。</span>
            </h1>
            <p class="hero-sub text-lg lg:text-xl text-green-100/80 max-w-xl leading-relaxed mb-10"
               style="text-shadow: 0 2px 12px rgba(0,0,0,.5);">
                テント泊からグランピングまで。<br>
                星空の下で過ごす、最高の夜を見つけよう。
            </p>

            {{-- 検索カード --}}
            <div class="hero-search w-full max-w-3xl bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-5 border border-white/40">
                <form method="GET" action="{{ route('campsites.index') }}">
                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_0.6fr_0.7fr_auto] gap-3 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#2d5a1b]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                出発日
                            </label>
                            <input type="date" name="check_in" min="{{ date('Y-m-d') }}"
                                   class="w-full h-12 rounded-xl border border-gray-200 bg-white text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-2 focus:ring-[#2d5a1b]/20 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#2d5a1b]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                帰着日
                            </label>
                            <input type="date" name="check_out" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full h-12 rounded-xl border border-gray-200 bg-white text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-2 focus:ring-[#2d5a1b]/20 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#2d5a1b]" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                人数
                            </label>
                            <input type="number" name="guests" min="1" max="20" placeholder="1名"
                                   class="w-full h-12 rounded-xl border border-gray-200 bg-white text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-2 focus:ring-[#2d5a1b]/20 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">スタイル</label>
                            <select name="type"
                                    class="w-full h-12 rounded-xl border border-gray-200 bg-white text-gray-800 px-3 text-sm focus:border-[#2d5a1b] focus:ring-2 focus:ring-[#2d5a1b]/20 outline-none transition">
                                <option value="">すべて</option>
                                <option value="tent">⛺ テント泊</option>
                                <option value="auto">🚗 オートキャンプ</option>
                                <option value="bungalow">🏠 バンガロー</option>
                                <option value="glamping">✨ グランピング</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="btn-ripple h-12 px-6 bg-[#2d5a1b] hover:bg-[#1c3a0e] text-white font-bold rounded-xl text-sm transition-all hover:scale-[1.03] whitespace-nowrap shadow-lg shadow-[#2d5a1b]/30">
                            探す
                        </button>
                    </div>
                </form>
            </div>

            {{-- クイックリンク --}}
            <div class="hero-links mt-5 flex flex-wrap justify-center gap-2">
                @foreach ([
                    ['tent', '⛺', 'テント泊'],
                    ['auto', '🚗', 'オートキャンプ'],
                    ['bungalow', '🏠', 'バンガロー'],
                    ['glamping', '✨', 'グランピング'],
                ] as [$type, $icon, $label])
                    <a href="{{ route('campsites.index', ['type' => $type]) }}"
                       class="flex items-center gap-1.5 px-4 py-2 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white text-sm rounded-full border border-white/20 transition-all hover:scale-105">
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
                <a href="{{ route('campsites.index') }}" class="hidden sm:flex items-center gap-1 text-sm text-[#2d5a1b] font-semibold hover:underline">
                    すべて見る <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['tent',     '⛺', 'テント泊',       '自然と一体になる<br>原点のキャンプ体験',        'from-[#1a3d1a] to-[#2d6b1b]', '#a8d878', 1],
                    ['auto',     '🚗', 'オートキャンプ', '車で乗り込む<br>ファミリーの定番スタイル',     'from-[#1a2d3d] to-[#1b4a6b]', '#7ab8d8', 2],
                    ['bungalow', '🏠', 'バンガロー',     'テント不要<br>手ぶらでアウトドア入門',          'from-[#3d2d1a] to-[#6b4a1b]', '#d8b87a', 3],
                    ['glamping', '✨', 'グランピング',   'おしゃれな空間で<br>上質な野外体験を',           'from-[#2d1a3d] to-[#4a1b6b]', '#c87ad8', 4],
                ] as [$type, $icon, $name, $desc, $grad, $accent, $delay])
                    <a href="{{ route('campsites.index', ['type' => $type]) }}"
                       class="reveal reveal-delay-{{ $delay }} group relative rounded-2xl overflow-hidden aspect-[3/4] sm:aspect-[4/5] transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                        <div class="absolute inset-0 bg-gradient-to-br {{ $grad }}"></div>
                        <div class="absolute inset-0 opacity-10"
                             style="background-image: repeating-linear-gradient(45deg, rgba(255,255,255,0.1) 0, rgba(255,255,255,0.1) 1px, transparent 0, transparent 50%); background-size: 10px 10px;"></div>
                        <div class="absolute inset-0 flex flex-col justify-between p-5">
                            <div class="text-5xl animate-float-slow">{{ $icon }}</div>
                            <div>
                                <h3 class="text-xl font-black text-white leading-tight mb-1 group-hover:translate-x-1 transition-transform">{{ $name }}</h3>
                                <p class="text-xs text-white/70 leading-relaxed">{!! $desc !!}</p>
                                <div class="mt-3 flex items-center gap-1 text-xs font-bold transition-all group-hover:gap-2"
                                     style="color: {{ $accent }};">
                                    詳しく見る
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- キャンプの魅力 --}}
        <section class="reveal relative rounded-3xl overflow-hidden"
                 style="background: linear-gradient(135deg, #0d2208 0%, #1c3a0e 50%, #0d1f0a 100%);">
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                @for ($i = 0; $i < 40; $i++)
                    @php $x = rand(0, 100); $y = rand(0, 60); $s = rand(1, 2); @endphp
                    <div class="star absolute rounded-full bg-white"
                         style="left:{{ $x }}%;top:{{ $y }}%;width:{{ $s }}px;height:{{ $s }}px;
                                --star-op:0.45;--star-dur:{{ rand(25,55)/10 }}s;--star-delay:{{ rand(0,40)/10 }}s;"></div>
                @endfor
            </div>
            <div class="relative z-10 p-10 lg:p-16">
                <div class="text-center mb-12">
                    <p class="text-xs font-bold text-green-400 uppercase tracking-widest mb-2">WHY CAMPING</p>
                    <h2 class="text-3xl lg:text-4xl font-black text-white mb-3">なぜ、キャンプなのか。</h2>
                    <p class="text-green-200/70 text-sm max-w-lg mx-auto">スマホを置いて、自然の中へ。焚き火を囲み、星を眺める。</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ([
                        ['🔥', '焚き火の夜', '炎を囲んで語らう時間。スマホも仕事も、全部忘れられる。'],
                        ['🌟', '満天の星空', '都市では見えない星が、無数に輝く。子どもも大人も感動する空。'],
                        ['🌲', '森林浴の朝', '鳥のさえずりで目覚める朝。体も心もリセットされる感覚。'],
                        ['🍳', '外ご飯の旨さ', '炭火で焼いた肉は、なぜあんなに美味しいのか。外飯の魔法。'],
                    ] as $idx => [$icon, $title, $desc])
                        <div class="reveal reveal-delay-{{ $idx + 1 }} text-center">
                            <div class="text-4xl mb-3 animate-float" style="animation-delay: {{ $idx * 0.4 }}s;">{{ $icon }}</div>
                            <h3 class="text-sm font-bold text-white mb-2">{{ $title }}</h3>
                            <p class="text-xs text-green-200/60 leading-relaxed">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 使い方 --}}
        <section>
            <div class="reveal text-center mb-10">
                <p class="text-xs font-bold text-[#2d5a1b] uppercase tracking-widest mb-1">HOW IT WORKS</p>
                <h2 class="text-3xl font-black text-gray-900">かんたん3ステップ</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ([
                    ['01', '🔍', 'サイトを探す', '日程・人数・スタイルを入力して、全国のキャンプサイトを検索。地図や写真で詳細をチェック。', '#2d5a1b', 1],
                    ['02', '📅', '空きを確認して予約', 'カレンダーで空き状況を確認。そのまますぐに予約が完了。面倒なやり取りは不要。', '#c4621a', 2],
                    ['03', '⛺', '自然を満喫！', '確認メールを受け取ったら、あとは荷造りするだけ。最高の野外体験があなたを待っています。', '#1a3a6b', 3],
                ] as [$num, $icon, $title, $desc, $color, $delay])
                    <div class="reveal reveal-delay-{{ $delay }} group relative bg-white rounded-2xl p-7 shadow-sm border border-[#e0d8cc] hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="text-7xl font-black opacity-[0.04] absolute top-3 right-4 select-none leading-none"
                             style="color: {{ $color }};">{{ $num }}</div>
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform inline-block">{{ $icon }}</div>
                        <h3 class="font-black text-gray-900 text-lg mb-2">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- CTA --}}
        <section class="reveal relative rounded-3xl overflow-hidden text-center py-16 px-6"
                 style="background: linear-gradient(135deg, #2d5a1b 0%, #1c3a0e 100%);">
            <div class="absolute inset-0 opacity-10"
                 style="background-image: repeating-linear-gradient(-45deg, rgba(255,255,255,0.15) 0, rgba(255,255,255,0.15) 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 animate-float">🏕️</div>
                <h2 class="text-3xl lg:text-4xl font-black text-white mb-3">
                    今週末、どこで焚き火する？
                </h2>
                <p class="text-green-200/80 mb-8 text-base max-w-md mx-auto">
                    全国のキャンプサイトを一括検索。<br>空きがあれば今すぐ予約できます。
                </p>
                <a href="{{ route('campsites.index') }}"
                   class="btn-ripple inline-flex items-center gap-2 bg-[#e07b39] hover:bg-[#c4621a] text-white font-black px-10 py-4 rounded-2xl text-base transition-all hover:scale-105 shadow-xl shadow-black/20">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                    キャンプサイトを探す
                </a>
                @guest
                    <p class="mt-4 text-green-300/60 text-xs">
                        <a href="{{ route('register') }}" class="underline hover:text-green-200">無料で登録</a>すると予約・お気に入り管理ができます
                    </p>
                @endguest
            </div>
        </section>
    </div>
</x-app-layout>
