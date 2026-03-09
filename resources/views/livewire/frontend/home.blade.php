<?php

use App\Models\Comic;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.frontend')] class extends Component {
    public string $activeDay = 'SAT';
    public string $activeCategory = 'Drama';

    public function with(): array
    {
        $baseQuery = Comic::with('author', 'genres')->where('status', 'approved');

        return [
            'trendingComics' => (clone $baseQuery)->take(5)->get(),
            'categoryComics' => (clone $baseQuery)->latest()->take(6)->get(),
            'newReleases'    => (clone $baseQuery)->inRandomOrder()->take(5)->get(),
            'dailyComics'    => (clone $baseQuery)->take(12)->get(),
            'indieComics'    => (clone $baseQuery)->latest()->take(6)->get(),
        ];
    }

    public function setDay($day) { $this->activeDay = $day; }
    public function setCategory($cat) { $this->activeCategory = $cat; }
}; ?>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-16">

    <section>
        <div class="flex justify-between items-end mb-4">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-bold text-black">Trending & Popular Series</h2>
                <div class="flex bg-gray-100 rounded-full p-0.5">
                    <button class="text-[11px] font-bold px-4 py-1.5 bg-black text-white rounded-full">Trending</button>
                    <button class="text-[11px] font-bold px-4 py-1.5 text-gray-500 hover:text-black rounded-full">Popular</button>
                </div>
            </div>
            <a href="{{ route('rankings')}}" class="text-xs text-gray-400 hover:text-black font-semibold flex items-center">View all <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
        </div>

        <div class="grid grid-cols-5 gap-4">
            @foreach($trendingComics as $index => $comic)
                <a href="{{ route('comics.show', $comic->id) }}" class="group relative block">
                    <div class="relative aspect-[3/4] rounded-lg overflow-hidden bg-gray-100">
                        <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute -bottom-6 -left-2 text-[100px] font-black italic tracking-tighter text-white"
                             style="-webkit-text-stroke: 3px black; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); line-height: 1;">
                            {{ $index + 1 }}
                        </div>
                    </div>
                    <div class="mt-2 pl-6">
                        <div class="flex items-center space-x-1 text-[10px] text-[#00dc64] font-bold mb-0.5">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                            <span>{{ rand(1, 20) }}</span>
                        </div>
                        <h3 class="text-[14px] font-bold text-black line-clamp-1 group-hover:text-[#00dc64]">{{ $comic->title }}</h3>
                        <p class="text-[12px] text-gray-500 mt-0.5">{{ $comic->genres->first()->name ?? 'Fantasy' }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-xl font-bold text-black mb-4">Now on WEBKOMIK</h2>
        <div class="w-full h-[100px] bg-[#1a1c3d] rounded-lg flex items-center justify-center cursor-pointer overflow-hidden relative">
            <div class="text-white text-center z-10">
                <h3 class="text-2xl font-bold">Click to read stories on WEBKOMIK!</h3>
                <p class="text-[10px] text-gray-400 mt-1">© Copyright {{ date('Y') }}</p>
            </div>
        </div>
    </section>

    <section>
        <div class="flex justify-between items-end mb-4">
            <h2 class="text-xl font-bold text-black">Popular Series by Category</h2>
            <a href="{{ route('categories')}}" class="text-xs text-gray-400 hover:text-black font-semibold flex items-center">View all <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
        </div>

        <div class="flex space-x-2 mb-4 overflow-x-auto custom-scrollbar pb-2">
            @foreach(['Drama', 'Fantasy', 'Comedy', 'Action', 'Slice of life', 'Romance', 'Superhero', 'Sci-fi'] as $cat)
                <button wire:click="setCategory('{{ $cat }}')"
                    class="px-4 py-1.5 text-[12px] font-bold rounded-full border transition-colors whitespace-nowrap {{ $activeCategory === $cat ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-6 gap-4">
            @foreach($categoryComics as $comic)
                <a href="{{ route('comics.show', $comic->id) }}" class="group block">
                    <div class="relative aspect-[3/4] rounded-lg overflow-hidden bg-gray-100 mb-2">
                        <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <h3 class="text-[13px] font-bold text-black line-clamp-1 group-hover:text-[#00dc64]">{{ $comic->title }}</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ rand(10, 99) }}M Views</p>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-xl font-bold text-black mb-4">Newly Released Originals</h2>
        <div class="grid grid-cols-5 gap-4">
            @foreach($newReleases as $comic)
                <a href="{{ route('originals') }}" class="group block relative aspect-[3/4] rounded-lg overflow-hidden bg-gray-100 shadow-sm border border-gray-200">
                    <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-80"></div>
                    <h3 class="absolute bottom-4 left-4 right-4 text-white font-black text-lg leading-tight text-center drop-shadow-lg">{{ $comic->title }}</h3>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <div class="flex justify-between items-end mb-4">
            <h2 class="text-xl font-bold text-black">Daily</h2>
            <a href="{{ route('comics.show', $comic->id) }}" class="text-xs text-gray-400 hover:text-black font-semibold flex items-center">View all <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
        </div>

        <div class="flex border-b border-gray-200 mb-4 overflow-x-auto custom-scrollbar">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Completed'] as $day)
                <button wire:click="setDay('{{ strtoupper($day) }}')"
                    class="px-5 py-3 text-[14px] font-bold transition-colors whitespace-nowrap {{ $activeDay === strtoupper($day) ? 'text-white bg-black rounded-t-lg' : 'text-gray-500 hover:text-black' }}">
                    {{ $day }}
                </button>
            @endforeach
        </div>

        <div class="flex justify-between items-end mb-4">
            <p class="text-[13px] text-gray-500"><span class="font-bold text-black">{{ count($dailyComics) }}</span> series</p>
            <div class="flex space-x-3 text-[11px] font-medium text-gray-400">
                <button class="text-black font-bold">by Popularity</button>
                <span>|</span><button class="hover:text-black">by Likes</button>
                <span>|</span><button class="hover:text-black">by Date</button>
            </div>
        </div>

        <div class="grid grid-cols-6 gap-x-4 gap-y-8">
            @foreach($dailyComics as $comic)
                <a href="{{ route('comics.show', $comic->id) }}" class="group block">
                    <div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-2 bg-gray-100">
                        <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute top-1.5 left-1.5 bg-[#00dc64] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-sm uppercase tracking-wide">
                            New Episode
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500">{{ $comic->genres->first()->name ?? 'Romance' }}</p>
                    <h3 class="text-[13px] font-bold text-black group-hover:text-[#00dc64] line-clamp-1">{{ $comic->title }}</h3>
                    <p class="text-[11px] text-[#00dc64] font-bold mt-0.5">♥ {{ rand(10, 999) }},000</p>
                </a>
            @endforeach
        </div>
    </section>
</div>
