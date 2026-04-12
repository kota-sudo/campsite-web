<nav x-data="{ open: false }" class="bg-[#1c3a0e] shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="relative">
                        <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none">
                            <!-- 木 -->
                            <path d="M16 3 L6 17 H12 L8 26 H24 L20 17 H26 Z" fill="#5a9e3a" opacity="0.9"/>
                            <path d="M16 3 L8 15 H13 L10 22 H22 L19 15 H24 Z" fill="#7abe50"/>
                            <rect x="14" y="26" width="4" height="4" rx="1" fill="#5a3e1b"/>
                            <!-- 焚き火 -->
                            <circle cx="26" cy="25" r="3" fill="#e07b39" opacity="0.9"/>
                            <path d="M26 20 C25 22 24 23 26 24 C28 23 27 22 26 20Z" fill="#f5a623" opacity="0.8"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-lg tracking-wide group-hover:text-[#a8d878] transition-colors">
                        {{ config('app.name') }}
                    </span>
                </a>

                <!-- Nav Links -->
                <div class="hidden sm:flex items-center gap-6">
                    <a href="{{ route('campsites.index') }}"
                       class="nav-link-slide text-sm font-medium {{ request()->routeIs('campsites.*') ? 'text-[#a8d878] border-b-2 border-[#a8d878] pb-0.5' : 'text-green-200 hover:text-white' }} transition-colors">
                        サイトを探す
                    </a>
                    <a href="{{ route('contact.create') }}"
                       class="nav-link-slide text-sm font-medium {{ request()->routeIs('contact.*') ? 'text-[#a8d878] border-b-2 border-[#a8d878] pb-0.5' : 'text-green-200 hover:text-white' }} transition-colors">
                        お問い合わせ
                    </a>
                    @auth
                        <a href="{{ route('reservations.index') }}"
                           class="nav-link-slide text-sm font-medium {{ request()->routeIs('reservations.*') ? 'text-[#a8d878] border-b-2 border-[#a8d878] pb-0.5' : 'text-green-200 hover:text-white' }} transition-colors">
                            マイ予約
                        </a>
                        <a href="{{ route('favorites.index') }}"
                           class="nav-link-slide text-sm font-medium {{ request()->routeIs('favorites.*') ? 'text-[#a8d878] border-b-2 border-[#a8d878] pb-0.5' : 'text-green-200 hover:text-white' }} transition-colors">
                            お気に入り
                        </a>
                        @if (Auth::user()->is_host || Auth::user()->is_admin)
                            <a href="{{ route('host.dashboard') }}"
                               class="nav-link-slide text-sm font-medium {{ request()->routeIs('host.*') ? 'text-[#a8d878] border-b-2 border-[#a8d878] pb-0.5' : 'text-green-200 hover:text-white' }} transition-colors">
                                ホスト管理
                            </a>
                        @endif
                        @if (Auth::user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}"
                               class="nav-link-slide text-sm font-medium {{ request()->routeIs('admin.*') ? 'text-[#a8d878] border-b-2 border-[#a8d878] pb-0.5' : 'text-green-200 hover:text-white' }} transition-colors">
                                管理
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 text-sm text-green-100 hover:text-white transition-colors px-3 py-2 rounded-lg hover:bg-white/10">
                                <span class="w-7 h-7 rounded-full bg-[#e07b39] flex items-center justify-center text-white text-xs font-bold">
                                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                                </span>
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">プロフィール</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    ログアウト
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm text-green-200 hover:text-white transition-colors px-3 py-1.5">
                        ログイン
                    </a>
                    <a href="{{ route('register') }}"
                       class="text-sm bg-[#e07b39] hover:bg-[#c4621a] text-white px-4 py-1.5 rounded-lg font-medium transition-colors">
                        無料登録
                    </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open"
                        class="p-2 rounded text-green-200 hover:text-white hover:bg-white/10 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden bg-[#142e09] border-t border-white/10">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('campsites.index') }}" class="block px-3 py-2 text-sm text-green-100 hover:text-white hover:bg-white/10 rounded transition">
                サイトを探す
            </a>
            @auth
                <a href="{{ route('reservations.index') }}" class="block px-3 py-2 text-sm text-green-100 hover:text-white hover:bg-white/10 rounded transition">
                    マイ予約
                </a>
                <a href="{{ route('favorites.index') }}" class="block px-3 py-2 text-sm text-green-100 hover:text-white hover:bg-white/10 rounded transition">
                    お気に入り
                </a>
            @endauth
        </div>
        @auth
            <div class="px-4 py-3 border-t border-white/10">
                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-300">{{ Auth::user()->email }}</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm text-green-100 hover:text-white hover:bg-white/10 rounded transition">
                        プロフィール
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 text-sm text-green-100 hover:text-white hover:bg-white/10 rounded transition">
                            ログアウト
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="px-4 py-3 border-t border-white/10 flex gap-3">
                <a href="{{ route('login') }}" class="flex-1 text-center py-2 text-sm text-green-200 border border-green-400/40 rounded hover:bg-white/10 transition">
                    ログイン
                </a>
                <a href="{{ route('register') }}" class="flex-1 text-center py-2 text-sm bg-[#e07b39] text-white rounded font-medium hover:bg-[#c4621a] transition">
                    無料登録
                </a>
            </div>
        @endauth
    </div>
</nav>
