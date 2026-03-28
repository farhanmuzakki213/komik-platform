<header class="bg-white border-b border-gray-200 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <div class="flex items-center space-x-8">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-black focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <a href="{{ route('home') }}" wire:navigate class="flex-shrink-0 flex items-center">
                    <span
                        class="text-3xl font-black text-brand-500 tracking-tighter uppercase font-sans">Webkomik</span>
                </a>

                <nav class="hidden md:flex space-x-6">
                    <a href="{{ route('originals') }}" wire:navigate
                        class="text-[13px] font-bold uppercase tracking-wide transition-colors {{ request()->routeIs('originals') ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                        Originals
                    </a>
                    <a href="{{ route('categories') }}" wire:navigate
                        class="text-[13px] font-bold uppercase tracking-wide transition-colors {{ request()->routeIs('categories') ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                        Categories
                    </a>
                    <a href="{{ route('rankings') }}" wire:navigate
                        class="text-[13px] font-bold uppercase tracking-wide transition-colors {{ request()->routeIs('rankings') ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                        Rankings
                    </a>
                </nav>
            </div>

            <div class="flex items-center space-x-5 relative">

                <div x-data="{ searchOpen: false }" @click.outside="searchOpen = false" class="relative">
                    <button @click="searchOpen = !searchOpen" class="text-black mt-1">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>

                    <div x-show="searchOpen" style="display: none;" x-transition.opacity.duration.200ms
                        class="absolute right-0 top-12 w-[350px] bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">

                        <div class="p-3 border-b border-gray-100 bg-gray-50 flex items-center">
                            <svg class="w-5 h-5 text-gray-400 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                placeholder="Cari judul atau kreator..."
                                class="w-full bg-transparent border-none focus:ring-0 text-sm px-3 text-gray-800 placeholder-gray-400">
                            <button @click="searchOpen = false; $wire.closeSearch()"
                                class="text-gray-400 hover:text-gray-600 mr-1"><svg class="w-5 h-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg></button>
                        </div>

                        @if ($showDropdown && (count($seriesResults) > 0 || count($creatorResults) > 0))
                            <div class="max-h-[400px] overflow-y-auto">
                                @if (count($seriesResults) > 0)
                                    <div class="p-4 border-b border-gray-100">
                                        <h4 class="text-[11px] font-bold text-black mb-3 uppercase tracking-wider">
                                            Series</h4>
                                        <ul class="space-y-3">
                                            @foreach ($seriesResults as $comic)
                                                <li>
                                                    <a href="{{ route('comics.show', $comic->slug) }}" class="flex items-center space-x-3 group">
                                                        <img src="{{ asset('storage/' . $comic->square_thumbnail) }}"
                                                            class="w-12 h-12 rounded border border-gray-200 object-cover">
                                                        <div>
                                                            <p
                                                                class="text-[13px] font-bold text-gray-900 group-hover:text-[#00dc64] transition-colors">
                                                                {{ $comic->title }}</p>
                                                            <p class="text-[11px] text-gray-500 mt-0.5">
                                                                {{ strtoupper($comic->author->name) }} <span
                                                                    class="text-gray-300 mx-1">|</span>
                                                                {{ $comic->genres->first()->name ?? 'Action' }}</p>
                                                        </div>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (count($creatorResults) > 0)
                                    <div class="p-4 border-b border-gray-100">
                                        <h4 class="text-[11px] font-bold text-black mb-3 uppercase tracking-wider">
                                            Creators</h4>
                                        <ul class="space-y-3">
                                            @foreach ($creatorResults as $creator)
                                                <li>
                                                    <a href="#" class="flex items-center space-x-2 group">
                                                        <p
                                                            class="text-[13px] font-bold text-gray-500 group-hover:text-black transition-colors">
                                                            {{ $creator->name }}</p>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3 bg-gray-50 flex justify-between items-center text-[11px] font-bold">
                                <a href="#" class="text-black hover:text-[#00dc64]">View All &gt;</a>
                                <button @click="searchOpen = false; $wire.closeSearch()"
                                    class="text-gray-500 hover:text-black">Close</button>
                            </div>
                        @elseif($showDropdown && strlen($search) >= 2)
                            <div class="p-6 text-center text-sm text-gray-500">Tidak ada hasil ditemukan.</div>
                        @endif
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-5">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="bg-black text-white text-[12px] font-bold px-4 py-1.5 rounded-full hover:bg-gray-800 transition-colors">Publish</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-[13px] font-bold text-black hover:text-brand-500 transition-colors">Log In</a>
                        <a href="{{ route('register') }}"
                            class="bg-black text-white text-[12px] font-bold px-4 py-1.5 rounded-full hover:bg-gray-800 transition-colors">Publish</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    <div x-show="mobileMenuOpen" style="display: none;" x-transition
        class="md:hidden bg-white border-b border-gray-200 shadow-sm absolute w-full">
        <div class="px-4 pt-2 pb-6 space-y-3">
            <a href="{{ route('originals') }}" wire:navigate
                class="block text-[14px] font-bold text-black py-2 border-b border-gray-50">Originals</a>
            <a href="{{ route('categories') }}" wire:navigate
                class="block text-[14px] font-bold text-black py-2 border-b border-gray-50">Categories</a>
            <a href="{{ route('rankings') }}" wire:navigate
                class="block text-[14px] font-bold text-black py-2 border-b border-gray-50">Rankings</a>

            <div class="pt-4 flex flex-col space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="text-center bg-black text-white text-[13px] font-bold py-2.5 rounded-full w-full">Dashboard
                        Kreator</a>
                @else
                    <a href="{{ route('login') }}"
                        class="text-center text-[14px] font-bold text-black py-2 border border-gray-300 rounded-full w-full">Log
                        In</a>
                    <a href="{{ route('register') }}"
                        class="text-center bg-black text-white text-[13px] font-bold py-2.5 rounded-full w-full">Daftar /
                        Publish</a>
                @endauth
            </div>
        </div>
    </div>
</header>
