<?php

use App\Models\Comic;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.frontend')] class extends Component {
    public string $activeTab = 'TRENDING';

    public function with(): array
    {
        // Peringkat dibatasi hanya 30 seri teratas
        $comics = Comic::with('author', 'genres')
            ->where('status', 'approved')
            ->inRandomOrder(seed: $this->activeTab) // Simulasi urutan berdasarkan Tab
            ->take(30)
            ->get();

        return [
            'comics' => $comics,
        ];
    }
    
    public function setTab($tab) { $this->activeTab = $tab; }
}; ?>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex space-x-8 md:space-x-12 border-b border-gray-200 mb-10 overflow-x-auto custom-scrollbar">
        @foreach(['TRENDING', 'POPULAR', 'ORIGINALS', 'CANVAS'] as $tab)
            <button wire:click="setTab('{{ $tab }}')" 
                class="pb-3 text-[15px] font-bold uppercase tracking-wide transition-colors whitespace-nowrap relative {{ $activeTab === $tab ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                {{ $tab }}
                @if($activeTab === $tab)
                    <span class="absolute bottom-0 left-0 w-full h-[3px] bg-black"></span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-x-2 gap-y-12">
        @forelse($comics as $index => $comic)
            <a href="#" class="group block relative">
                
                <div class="relative w-full aspect-[3/4] rounded-lg overflow-visible mb-3 bg-gray-100 border border-gray-100">
                    <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" alt="{{ $comic->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 rounded-lg">
                    
                    @if($index == 4 || $index == 18 || $index == 24) <div class="absolute top-1.5 left-1.5 bg-[#00dc64] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-[3px] uppercase tracking-wide">New Series</div>
                    @endif

                    <div class="absolute -bottom-7 -left-1 text-[85px] font-black italic tracking-tighter text-white z-10 flex items-end" 
                         style="-webkit-text-stroke: 3px black; text-shadow: 0px 4px 4px rgba(0,0,0,0.25); line-height: 0.8;">
                        {{ $index + 1 }}
                        
                        <div class="flex items-center text-[#00dc64] font-sans italic-none text-[11px] font-bold mb-3 ml-1" style="-webkit-text-stroke: 0px;">
                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                            {{ rand(1, 20) }}
                        </div>
                    </div>
                </div>

                <div class="space-y-0.5 pl-6 mt-6">
                    <h3 class="text-[14px] font-bold text-black group-hover:text-[#00dc64] transition-colors line-clamp-1">{{ $comic->title }}</h3>
                    <div class="flex items-center text-[11px]">
                        <span class="text-gray-500 font-medium">{{ $comic->genres->first()->name ?? 'Fantasy' }}</span>
                        <span class="text-[#00dc64] font-bold ml-2 flex items-center">
                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                            {{ number_format(rand(10, 999) / 10, 1) }}M
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-24 text-gray-500 font-medium">Belum ada data ranking.</div>
        @endforelse
    </div>
</div>