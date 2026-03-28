<?php

use App\Models\Comic;
use App\Models\ComicRank;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.frontend')] class extends Component {
    public string $activeTab = 'TRENDING';

    public function with(): array
    {
        $baseQuery = Comic::with('author', 'genres')->where('status', 'approved');

        // FAKTA: Tab yang berbeda memiliki metrik pengurutan yang berbeda
        if ($this->activeTab === 'POPULAR') {
            $comics = (clone $baseQuery)->orderByDesc('likes_count')->take(30)->get();
        } else {
            $comics = (clone $baseQuery)->orderByDesc('views_count')->take(30)->get();
        }

        // FAKTA: Mengambil data peringkat kemarin tanpa N+1 Query
        $comicIds = $comics->pluck('id');
        $latestRecordDate = ComicRank::max('recorded_at');

        $yesterdayRanks = ComicRank::whereIn('comic_id', $comicIds)
            ->where('recorded_at', $latestRecordDate)
            ->pluck('rank', 'comic_id');

        // Kalkulasi Pergerakan
        $comics->map(function ($comic, $index) use ($yesterdayRanks) {
            $currentRank = $index + 1;

            if ($yesterdayRanks->has($comic->id)) {
                $yesterdayRank = $yesterdayRanks[$comic->id];
                $comic->rank_change = $yesterdayRank - $currentRank;
            } else {
                $comic->rank_change = 'NEW';
            }
            return $comic;
        });

        return [
            'comics' => $comics,
        ];
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }
}; ?>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex space-x-8 md:space-x-12 border-b border-gray-200 mb-10 overflow-x-auto custom-scrollbar">
        @foreach (['TRENDING', 'POPULAR', 'ORIGINALS'] as $tab)
            <button wire:click="setTab('{{ $tab }}')"
                class="pb-3 text-[15px] font-bold uppercase tracking-wide transition-colors whitespace-nowrap relative {{ $activeTab === $tab ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                {{ $tab }}
                @if ($activeTab === $tab)
                    <span class="absolute bottom-0 left-0 w-full h-[3px] bg-black"></span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-x-2 sm:gap-x-4 gap-y-6 sm:gap-y-8">
        @forelse($comics as $index => $comic)
            <a href="{{ route('comics.show', $comic->slug) }}" class="group block relative">

                <div class="relative w-full aspect-[10/16] rounded-lg overflow-hidden mb-2 bg-gray-100 border border-gray-100">
                    <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" alt="{{ $comic->title }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                    @if ($index == 4 || $index == 18 || $index == 24)
                        <div class="absolute top-1.5 left-1.5 bg-[#00dc64] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-[3px] uppercase tracking-wide">
                            New Series
                        </div>
                    @endif

                    <div class="absolute -bottom-6 -left-2 text-[100px] font-black italic tracking-tighter text-white z-10 leading-none"
                        style="-webkit-text-stroke: 3px black; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                        {{ $index + 1 }}
                    </div>
                </div>

                <div class="mt-2 pl-6">

                    <div class="h-[15px] mb-0.5 flex items-center">
                        @if($comic->rank_change === 'NEW')
                            <span class="text-[10px] text-orange-500 font-bold bg-orange-100 px-1 rounded-sm leading-none pt-0.5 pb-0.5">NEW</span>

                        @elseif($comic->rank_change > 0)
                            <div class="flex items-center space-x-1 text-[10px] text-[#00dc64] font-bold">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                <span>{{ $comic->rank_change }}</span>
                            </div>

                        @elseif($comic->rank_change < 0)
                            <div class="flex items-center space-x-1 text-[10px] text-red-500 font-bold">
                                <svg class="w-2.5 h-2.5 transform rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                <span>{{ abs($comic->rank_change) }}</span>
                            </div>

                        @else
                            <span class="text-gray-300 font-bold text-[10px] leading-none">-</span>
                        @endif
                    </div>

                    <h3 class="text-[14px] font-bold text-black group-hover:text-[#00dc64] transition-colors line-clamp-1">
                        {{ $comic->title }}
                    </h3>

                    <div class="flex items-center text-[11px] mt-0.5">
                        <span class="text-gray-500 font-medium">{{ $comic->genres->first()->name ?? 'Fantasy' }}</span>
                        <span class="text-[#00dc64] font-bold ml-2 flex items-center">
                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                            {{ \Illuminate\Support\Number::abbreviate($comic->likes_count ?? 0, maxPrecision: 1) }}
                        </span>
                    </div>

                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-24 text-gray-500 font-medium">Belum ada data ranking.</div>
        @endforelse
    </div>
</div>
