<?php

use App\Models\Comic;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.frontend')] class extends Component {
    public string $activeDay = 'SAT';
    public string $sortBy = 'popularity'; // popularity, likes, date

    public function with(): array
    {
        // FAKTA: Di aplikasi nyata, Anda harus menambahkan kolom 'release_day' di tabel comics.
        // Untuk saat ini, kita filter data 'approved' dan menyimulasikan sorting.
        $query = Comic::with('author', 'genres')->where('status', 'approved');

        // Logika Mesin Sorting (Reaktif)
        if ($this->sortBy === 'date') {
            $query->latest();
        } else {
            // Simulasi urutan berbeda setiap hari / kategori untuk keperluan purwarupa
            $query->inRandomOrder(seed: $this->activeDay . $this->sortBy);
        }

        $comics = $query->get();

        return [
            'comics' => $comics,
            'totalSeries' => $comics->count(),
        ];
    }

    public function setDay($day)
    {
        $this->activeDay = $day;
    }

    public function setSort($sort)
    {
        $this->sortBy = $sort;
    }
}; ?>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex justify-center md:justify-start space-x-8 md:space-x-12 border-b border-gray-200 mb-8 overflow-x-auto custom-scrollbar">
        @foreach(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN', 'COMPLETED'] as $day)
            <button wire:click="setDay('{{ $day }}')"
                class="pb-3 text-[15px] font-bold uppercase tracking-wide transition-colors whitespace-nowrap relative {{ $activeDay === $day ? 'text-black' : 'text-gray-400 hover:text-black' }}">
                {{ $day }}
                @if($activeDay === $day)
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
            <a href="{{ route('comics.show', $comic->slug) }}" class="group block">
                <div class="relative w-full aspect-[3/4] rounded-lg overflow-hidden mb-3 bg-gray-100 shadow-sm border border-gray-100">
                    <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" alt="{{ $comic->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                    <div class="absolute top-1.5 left-1.5 bg-[#00dc64] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-[3px] uppercase tracking-wide shadow-sm border border-[#00c55a]">
                        New Episode
                    </div>
                </div>

                <div class="space-y-0.5">
                    <p class="text-[11px] text-gray-500 font-medium">{{ $comic->genres->first()->name ?? 'Romance' }}</p>
                    <h3 class="text-[14px] font-bold text-black group-hover:text-[#00dc64] transition-colors line-clamp-1">{{ $comic->title }}</h3>
                    <div class="flex items-center text-[11px] font-bold text-[#00dc64]">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                        {{\Illuminate\Support\Number::abbreviate($comic->likes_count) }}
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-24 text-gray-500 font-medium bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-lg text-gray-400 mb-2">Belum ada komik untuk hari ini.</p>
                <p class="text-sm">Silakan pilih hari lain atau tunggu update kreator selanjutnya.</p>
            </div>
        @endforelse
    </div>
</div>
