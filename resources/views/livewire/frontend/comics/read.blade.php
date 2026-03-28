<?php

use App\Models\Comic;
use App\Models\Chapter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.reader')] class extends Component {
    public Comic $comic;
    public Chapter $chapter;

    public function with(): array
    {
        $panels = $this->chapter->panels()->orderBy('order_index')->get();

        $prevChapter = $this->comic->chapters()->where('status', 'approved')->where('chapter_number', '<', $this->chapter->chapter_number)->orderBy('chapter_number', 'desc')->first();

        $nextChapter = $this->comic->chapters()->where('status', 'approved')->where('chapter_number', '>', $this->chapter->chapter_number)->orderBy('chapter_number', 'asc')->first();

        $adjacentChapters = $this->comic
            ->chapters()
            ->where('status', 'approved')
            ->whereBetween('chapter_number', [$this->chapter->chapter_number - 4, $this->chapter->chapter_number + 4])
            ->orderBy('chapter_number', 'asc')
            ->get();

        $trendingComics = Comic::with('author', 'genres')
            ->where('status', 'approved')
            ->inRandomOrder()
            ->take(5)
            ->get();

        return compact('panels', 'prevChapter', 'nextChapter', 'adjacentChapters', 'trendingComics');
    }
}; ?>

<div x-data="{
    showHeader: true,
    lastScrollY: 0,
    init() {
        window.addEventListener('scroll', () => {
            let currentScrollY = window.scrollY;
            if (currentScrollY <= 50) {
                this.showHeader = true; // Muncul saat di paling atas
            } else if (currentScrollY > this.lastScrollY) {
                this.showHeader = false; // Sembunyi saat layar digulir ke bawah
            } else {
                this.showHeader = true; // Opsional: Muncul saat gulir ke atas
            }
            this.lastScrollY = currentScrollY;
        });
    }
}" class="bg-[#ffffff] min-h-screen">

    <div x-show="!showHeader" class="fixed top-0 left-0 w-full h-12 z-[60]" @mouseenter="showHeader = true"></div>

    <div :class="showHeader ? 'translate-y-0' : '-translate-y-full'"
        class="fixed top-0 left-0 w-full bg-[#1b1b1b] text-white z-50 shadow-md transition-transform duration-300 ease-in-out"
        @mouseleave="if(window.scrollY > 50) showHeader = false">

        <div class="max-w-[1200px] mx-auto px-3 sm:px-4 h-14 flex justify-between items-center gap-2">

            <div class="flex items-center space-x-2 sm:space-x-3 text-[13px] sm:text-[15px] font-bold flex-1 min-w-0">

                <a href="{{ route('home') }}" wire:navigate class="flex-shrink-0 flex items-center group">
                    <span
                        class="text-lg sm:text-xl font-black text-brand-500 tracking-tighter uppercase font-sans pr-1 sm:pr-2">WEBKOMIK</span>
                </a>

                <div class="hidden sm:block w-[1px] h-4 bg-gray-600"></div>

                <a href="{{ route('comics.show', $comic->slug) }}" wire:navigate
                    class="hover:text-gray-300 flex-shrink-0 flex items-center group">
                    <svg class="w-5 h-5 sm:hidden mr-1 text-gray-400 group-hover:text-white transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>

                    <span class="hidden sm:inline truncate max-w-[100px] md:max-w-[200px]">{{ $comic->title }}</span>
                </a>
                <span class="text-gray-500 hidden sm:inline">&gt;</span>
                <span class="truncate text-gray-200 sm:text-white">{{ $chapter->title }}</span>
            </div>

            <div
                class="flex items-center space-x-2 sm:space-x-4 bg-[#2b2b2b] rounded-full px-1.5 sm:px-2 py-1 flex-shrink-0">
                @if ($prevChapter)
                    <a href="{{ route('comics.read', ['comic' => $comic->slug, 'chapter' => $prevChapter->chapter_number]) }}"
                        wire:navigate class="p-1 hover:bg-gray-600 rounded-full transition"><svg
                            class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></a>
                @else
                    <span class="p-1 text-gray-600 cursor-not-allowed"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></span>
                @endif

                <span class="text-[12px] sm:text-[13px] font-bold px-1">#{{ $chapter->chapter_number }}</span>

                @if ($nextChapter)
                    <a href="{{ route('comics.read', ['comic' => $comic->slug, 'chapter' => $nextChapter->chapter_number]) }}"
                        wire:navigate class="p-1 hover:bg-gray-600 rounded-full transition"><svg
                            class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></a>
                @else
                    <span class="p-1 text-gray-600 cursor-not-allowed"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></span>
                @endif
            </div>

            <div class="flex items-center justify-end space-x-3 sm:space-x-4 flex-1 flex-shrink-0">
                <button
                    class="text-white hover:text-[#00dc64] font-bold text-xl sm:text-2xl leading-none pb-0.5">+</button>
                <button x-data @click="navigator.clipboard.writeText(window.location.href); alert('Tautan tersalin!');"
                    class="text-white hover:text-gray-300"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z">
                        </path>
                    </svg></button>
            </div>
        </div>
    </div>

    <div class="w-full mx-auto bg-[#1b1b1b] sm:bg-[#f2f2f2] min-h-screen flex flex-col items-center pt-14">

        <div class="w-full max-w-[100%] sm:max-w-[800px] flex flex-col bg-white">
            @forelse($panels as $panel)
                <img src="{{ asset('storage/' . $panel->image_path) }}" alt="Panel {{ $panel->order_index }}"
                    class="w-full h-auto block m-0 p-0 align-bottom leading-none" loading="lazy">
            @empty
                <div class="py-32 text-center text-gray-500 font-bold bg-white">
                    Gambar episode sedang diproses.
                </div>
            @endforelse
        </div>

    </div>

    <div class="bg-[#f2f2f2] pt-12 pb-8 border-t border-gray-300">
        <div class="max-w-[1000px] mx-auto px-4 flex items-center justify-center space-x-4">

            @if ($prevChapter)
                <a href="{{ route('comics.read', ['comic' => $comic->slug, 'chapter' => $prevChapter->chapter_number]) }}"
                    wire:navigate class="text-gray-400 hover:text-gray-600 transition"><svg class="w-8 h-8"
                        fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg></a>
            @else
                <span class="text-gray-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg></span>
            @endif

            <div class="flex overflow-x-auto custom-scrollbar space-x-3 px-2 pb-2">
                @foreach ($adjacentChapters as $adj)
                    <a href="{{ route('comics.read', ['comic' => $comic->slug, 'chapter' => $adj->chapter_number]) }}" wire:navigate
                        class="flex-shrink-0 w-[85px] group">
                        <div
                            class="w-full aspect-[4/3] overflow-hidden border-2 transition-all {{ $adj->chapter_number === $chapter->chapter_number ? 'border-[#00dc64]' : 'border-transparent group-hover:border-gray-300' }}">
                            <img src="{{ asset('storage/' . $adj->thumbnail) }}"
                                class="w-full h-full object-cover opacity-90 group-hover:opacity-100">
                        </div>
                        <p
                            class="text-[12px] text-center mt-1 truncate transition-colors {{ $adj->chapter_number === $chapter->chapter_number ? 'text-[#00dc64] font-bold' : 'text-gray-500 group-hover:text-black font-medium' }}">
                            Ep. {{ $adj->chapter_number }}
                        </p>
                    </a>
                @endforeach
            </div>

            @if ($nextChapter)
                <a href="{{ route('comics.read', ['comic' => $comic->slug, 'chapter' => $nextChapter->chapter_number]) }}"
                    wire:navigate class="text-gray-400 hover:text-gray-600 transition"><svg class="w-8 h-8"
                        fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg></a>
            @else
                <span class="text-gray-300"><svg class="w-8 h-8" fill="none" stroke="currentColor"
                        stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg></span>
            @endif

        </div>
    </div>

    <div class="bg-white border-t border-gray-200 mt-10">
        <div class="max-w-[1000px] mx-auto px-4 py-12 flex flex-col md:flex-row gap-12">

            <div class="w-full md:w-[70%]">
                <div class="flex items-center justify-between pb-8 border-b border-gray-200 mb-8">
                    <div>
                        <p class="text-[12px] text-gray-500 font-bold mb-1">Creator</p>
                        <h3 class="text-[18px] font-bold text-black">{{ strtoupper($comic->author->name) }}</h3>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-bold px-4 py-2 rounded-full transition flex items-center">
                            ♡ 7,903
                        </button>
                        <button
                            class="bg-black hover:bg-gray-800 text-white text-[13px] font-bold px-5 py-2 rounded-full transition flex items-center">
                            + Subscribe
                        </button>
                    </div>
                </div>

                <livewire:frontend.comics.comment-section :chapter="$chapter" />
            </div>

            <div class="w-full md:w-[30%]">

                <div class="flex justify-between items-center mb-2 pb-3 border-b border-gray-200 cursor-pointer group">
                    <h3 class="text-[18px] font-bold text-black group-hover:text-gray-700 transition-colors flex items-center gap-1">
                        Trending & Popular
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                    </h3>
                </div>

                <div class="flex flex-col">
                    @foreach($trendingComics as $index => $trendComic)
                        <a href="{{ route('comics.show', $trendComic->slug) }}" wire:navigate class="flex items-center gap-3.5 py-3 border-b border-gray-100 last:border-0 group">

                            <div class="w-[74px] h-[74px] flex-shrink-0 overflow-hidden rounded-[4px] border border-gray-200">
                                <img src="{{ asset('storage/' . $trendComic->square_thumbnail) }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>

                            <div class="flex flex-col justify-center flex-1 min-w-0">
                                <p class="text-[12px] text-gray-400 font-medium mb-1 truncate">{{ $trendComic->genres->first()->name ?? 'NA' }}</p>

                                <div class="flex items-center gap-2.5 mb-1">
                                    <span class="text-[22px] font-black text-black leading-none">{{ $index + 1 }}</span>
                                    <h4 class="text-[15px] font-bold text-black group-hover:text-[#00dc64] truncate leading-tight">{{ $trendComic->title }}</h4>
                                </div>

                                <p class="text-[13px] text-gray-600 truncate">{{ $trendComic->author->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
