<?php

use App\Models\Comic;
use App\Models\Genre;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.frontend')] class extends Component {
    public string $activeCategory = 'DRAMA';
    public string $sortBy = 'popularity';

    public function with(): array
    {
        $query = Comic::with('author', 'genres')->where('status', 'approved');

        // Simulasi pengurutan
        if ($this->sortBy === 'date') {
            $query->latest();
        } else {
            $query->inRandomOrder(seed: $this->activeCategory . $this->sortBy);
        }

        $comics = $query->get();

        return [
            // FAKTA: Mengambil genre dari database untuk menu tabs
            'genres' => Genre::orderBy('name')->pluck('name')->toArray(),
            'comics' => $comics,
            'totalSeries' => $comics->count(),
        ];
    }

    public function setCategory($cat) { $this->activeCategory = strtoupper($cat); }
    public function setSort($sort) { $this->sortBy = $sort; }
}; ?>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex space-x-8 border-b border-gray-200 mb-8 overflow-x-auto custom-scrollbar">
        @php
            $displayGenres = count($genres) > 0 ? $genres : ['DRAMA', 'FANTASY', 'COMEDY', 'ACTION', 'SLICE OF LIFE', 'ROMANCE', 'SUPERHERO', 'SCI-FI', 'THRILLER', 'SUPERNATURAL', 'MYSTERY'];
        @endphp

        @foreach($displayGenres as $genre)
            @php $genreUpper = strtoupper($genre); @endphp
            <button wire:click="setCategory('{{ $genreUpper }}')"
                class="pb-3 text-[15px] font-bold uppercase tracking-wide transition-colors whitespace-nowrap relative {{ $activeCategory === $genreUpper ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                {{ $genreUpper }}
                @if($activeCategory === $genreUpper)
                    <span class="absolute bottom-0 left-0 w-full h-[3px] bg-black"></span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="flex justify-between items-end mb-6">
        <h2 class="text-[13px] font-normal text-gray-500">
            <span class="text-black text-[16px] font-bold">{{ $totalSeries }}</span> series
        </h2>

        <div class="flex space-x-3 text-[12px] font-medium text-gray-400">
            <button wire:click="setSort('popularity')" class="transition-colors {{ $sortBy === 'popularity' ? 'text-black font-bold' : 'hover:text-black' }}">by Popularity</button>
            <span>|</span>
            <button wire:click="setSort('likes')" class="transition-colors {{ $sortBy === 'likes' ? 'text-black font-bold' : 'hover:text-black' }}">by Likes</button>
            <span>|</span>
            <button wire:click="setSort('date')" class="transition-colors {{ $sortBy === 'date' ? 'text-black font-bold' : 'hover:text-black' }}">by Date</button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-x-2 gap-y-10">
        @forelse($comics as $comic)
            <a href="{{ route('comics.show', $comic->id) }}" class="group block">
                <div class="relative w-full aspect-[3/4] rounded-lg overflow-hidden mb-3 bg-gray-100 border border-gray-100">
                    <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" alt="{{ $comic->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                    @if($loop->index % 5 === 0)
                        <div class="absolute top-1.5 left-1.5 bg-[#00dc64] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-[3px] uppercase tracking-wide">New Series</div>
                    @elseif($loop->index % 3 === 0)
                        <div class="absolute top-1.5 left-1.5 bg-black text-white text-[9px] font-bold px-1.5 py-0.5 rounded-[3px] uppercase tracking-wide">New Episode</div>
                    @endif
                </div>

                <div class="space-y-0.5">
                    <h3 class="text-[14px] font-bold text-black group-hover:text-[#00dc64] transition-colors line-clamp-1">{{ $comic->title }}</h3>
                    <p class="text-[11px] text-gray-500 font-medium line-clamp-1">{{ strtoupper($comic->author->name) }}</p>
                    <div class="flex items-center text-[11px] font-bold text-[#00dc64] pt-0.5">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                        {{ number_format(rand(10, 999) / 10, 1) }}M
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-24 text-gray-500 font-medium">Belum ada komik di kategori ini.</div>
        @endforelse
    </div>
</div>
