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

        $prevChapter = $this->comic->chapters()
            ->where('status', 'approved')
            ->where('chapter_number', '<', $this->chapter->chapter_number)
            ->orderBy('chapter_number', 'desc')
            ->first();

        $nextChapter = $this->comic->chapters()
            ->where('status', 'approved')
            ->where('chapter_number', '>', $this->chapter->chapter_number)
            ->orderBy('chapter_number', 'asc')
            ->first();

        // FAKTA: Mengambil 4 episode sebelum dan 4 sesudah untuk Carousel Navigasi
        $adjacentChapters = $this->comic->chapters()
            ->where('status', 'approved')
            ->whereBetween('chapter_number', [
                $this->chapter->chapter_number - 4,
                $this->chapter->chapter_number + 4
            ])
            ->orderBy('chapter_number', 'asc')
            ->get();

        return compact('panels', 'prevChapter', 'nextChapter', 'adjacentChapters');
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
                    <span class="text-lg sm:text-xl font-black text-brand-500 tracking-tighter uppercase font-sans pr-1 sm:pr-2">WEBKOMIK</span>
                </a>

                <div class="hidden sm:block w-[1px] h-4 bg-gray-600"></div>

                <a href="{{ route('comics.show', $comic->id) }}" wire:navigate class="hover:text-gray-300 flex-shrink-0 flex items-center group">
                    <svg class="w-5 h-5 sm:hidden mr-1 text-gray-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    
                    <span class="hidden sm:inline truncate max-w-[100px] md:max-w-[200px]">{{ $comic->title }}</span>
                </a>
                <span class="text-gray-500 hidden sm:inline">&gt;</span>
                <span class="truncate text-gray-200 sm:text-white">{{ $chapter->title }}</span>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-4 bg-[#2b2b2b] rounded-full px-1.5 sm:px-2 py-1 flex-shrink-0">
                @if($prevChapter)
                    <a href="{{ route('comics.read', ['comic' => $comic->id, 'chapter' => $prevChapter->id]) }}" wire:navigate class="p-1 hover:bg-gray-600 rounded-full transition"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></a>
                @else
                    <span class="p-1 text-gray-600 cursor-not-allowed"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></span>
                @endif
                
                <span class="text-[12px] sm:text-[13px] font-bold px-1">#{{ $chapter->chapter_number }}</span>

                @if($nextChapter)
                    <a href="{{ route('comics.read', ['comic' => $comic->id, 'chapter' => $nextChapter->id]) }}" wire:navigate class="p-1 hover:bg-gray-600 rounded-full transition"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="p-1 text-gray-600 cursor-not-allowed"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>

            <div class="flex items-center justify-end space-x-3 sm:space-x-4 flex-1 flex-shrink-0">
                <button class="text-white hover:text-[#00dc64] font-bold text-xl sm:text-2xl leading-none pb-0.5">+</button>
                <button x-data @click="navigator.clipboard.writeText(window.location.href); alert('Tautan tersalin!');" class="text-white hover:text-gray-300"><svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg></button>
            </div>
        </div>
    </div>

    <div class="w-full mx-auto bg-[#1b1b1b] sm:bg-[#f2f2f2] min-h-screen flex flex-col items-center pt-14">
        
        <div class="w-full max-w-[100%] sm:max-w-[800px] flex flex-col bg-white">
            @forelse($panels as $panel)
                <img src="{{ asset('storage/' . $panel->image_path) }}" 
                     alt="Panel {{ $panel->order_index }}" 
                     class="w-full h-auto block m-0 p-0 align-bottom leading-none"
                     loading="lazy">
            @empty
                <div class="py-32 text-center text-gray-500 font-bold bg-white">
                    Gambar episode sedang diproses.
                </div>
            @endforelse
        </div>
        
    </div>

    <div class="bg-[#f2f2f2] pt-12 pb-8 border-t border-gray-300">
        <div class="max-w-[1000px] mx-auto px-4 flex items-center justify-center space-x-4">
            
            @if($prevChapter)
                <a href="{{ route('comics.read', ['comic' => $comic->id, 'chapter' => $prevChapter->id]) }}" wire:navigate class="text-gray-400 hover:text-gray-600 transition"><svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg></a>
            @else
                <span class="text-gray-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg></span>
            @endif

            <div class="flex overflow-x-auto custom-scrollbar space-x-3 px-2 pb-2">
                @foreach($adjacentChapters as $adj)
                    <a href="{{ route('comics.read', ['comic' => $comic->id, 'chapter' => $adj->id]) }}" wire:navigate class="flex-shrink-0 w-[85px] group">
                        <div class="w-full aspect-[4/3] overflow-hidden border-2 transition-all {{ $adj->id === $chapter->id ? 'border-[#00dc64]' : 'border-transparent group-hover:border-gray-300' }}">
                            <img src="{{ asset('storage/' . $adj->thumbnail) }}" class="w-full h-full object-cover opacity-90 group-hover:opacity-100">
                        </div>
                        <p class="text-[12px] text-center mt-1 truncate transition-colors {{ $adj->id === $chapter->id ? 'text-[#00dc64] font-bold' : 'text-gray-500 group-hover:text-black font-medium' }}">
                            Ep. {{ $adj->chapter_number }}
                        </p>
                    </a>
                @endforeach
            </div>

            @if($nextChapter)
                <a href="{{ route('comics.read', ['comic' => $comic->id, 'chapter' => $nextChapter->id]) }}" wire:navigate class="text-gray-400 hover:text-gray-600 transition"><svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg></a>
            @else
                <span class="text-gray-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg></span>
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
                        <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-bold px-4 py-2 rounded-full transition flex items-center">
                            ♡ 7,903
                        </button>
                        <button class="bg-black hover:bg-gray-800 text-white text-[13px] font-bold px-5 py-2 rounded-full transition flex items-center">
                            + Subscribe
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-[16px] font-bold text-black mb-4">COMMENTS <span class="text-gray-400 font-normal">405</span></h3>
                    
                    <div class="border border-gray-200 rounded-lg p-3 mb-6 bg-white">
                        <textarea rows="2" placeholder="Leave a comment" class="w-full border-none focus:ring-0 text-sm resize-none"></textarea>
                        <div class="flex justify-between items-center mt-2 border-t border-gray-100 pt-2">
                            <label class="flex items-center text-xs text-gray-400 cursor-pointer">
                                <input type="checkbox" class="mr-2 rounded text-black focus:ring-black"> Spoiler
                            </label>
                            <button class="text-gray-400 hover:text-[#00dc64]"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg></button>
                        </div>
                    </div>

                    <div class="flex space-x-4 border-b border-gray-200 text-[12px] font-bold mb-6">
                        <button class="pb-2 border-b-2 border-black text-black">TOP</button>
                        <button class="pb-2 text-gray-400 hover:text-black">NEWEST</button>
                    </div>

                    <div class="space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-[12px] text-gray-600">Elizabeth Corrigan</p>
                                <button class="text-gray-300 hover:text-gray-500">⋮</button>
                            </div>
                            <p class="text-[10px] text-gray-400 mb-2">Jan 18, 2026</p>
                            <span class="inline-block border border-[#00dc64] text-[#00dc64] text-[9px] font-bold px-1 rounded-sm mb-2">TOP</span>
                            <p class="text-[14px] text-gray-900 mb-3">I now ship Crazed Layla and the shovel as endgame</p>
                            
                            <div class="flex justify-between items-center">
                                <button class="text-[12px] font-bold text-gray-500 border border-gray-200 px-3 py-1 rounded hover:bg-gray-50">Replies 9</button>
                                <div class="flex items-center space-x-2">
                                    <button class="text-[12px] text-gray-500 font-bold flex items-center bg-gray-100 px-2 py-1 rounded hover:bg-gray-200">👍 5396</button>
                                    <button class="text-[12px] text-gray-500 font-bold flex items-center bg-gray-100 px-2 py-1 rounded hover:bg-gray-200">👎 12</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-[30%]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[16px] font-bold text-black">Trending & Popular</h3>
                    <span class="text-gray-400 text-lg">&gt;</span>
                </div>
                
                <div class="space-y-4">
                    <a href="#" class="flex items-center space-x-3 group">
                        <img src="{{ asset('storage/' . $comic->square_thumbnail) }}" class="w-12 h-12 rounded object-cover border border-gray-200">
                        <div>
                            <p class="text-[10px] text-gray-400">{{ $comic->genres->first()->name ?? 'Fantasy' }}</p>
                            <p class="text-[13px] font-bold text-black group-hover:text-[#00dc64] line-clamp-1"><span class="mr-1">1</span> {{ $comic->title }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ strtoupper($comic->author->name) }}</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>