<?php

use App\Models\Comic;
use App\Models\Chapter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.frontend')] class extends Component {
    use WithPagination;

    public Comic $comic;

    public function with(): array
    {
        return [
            'chapters' => $this->comic->chapters()
                ->where('status', 'approved')
                ->latest('chapter_number')
                ->paginate(10),
            
            'firstChapter' => $this->comic->chapters()
                ->where('status', 'approved')
                ->orderBy('chapter_number', 'asc')
                ->first()
        ];
    }
    
    public function paginationView()
    {
        return 'vendor.pagination.webkomik'; 
    }
}; ?>

<div class="bg-white min-h-screen pb-20">
    
    <div class="relative w-full h-[300px] overflow-hidden bg-gray-900 flex items-center justify-center">
        <div class="absolute inset-0 opacity-40">
            <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" class="w-full h-full object-cover blur-xl transform scale-110">
        </div>
        
        <div class="relative z-10 text-center text-white space-y-2 max-w-2xl px-4">
            <p class="text-[15px] font-bold text-[#00dc64] tracking-wider">{{ $comic->genres->first()->name ?? 'Umum' }}</p>
            <h1 class="text-5xl font-black tracking-tight drop-shadow-lg">{{ $comic->title }}</h1>
            <p class="text-[15px] font-medium text-gray-200">{{ $comic->author->name }} <span class="text-[#00dc64] ml-1">✓</span></p>
        </div>

        <div class="absolute bottom-6 right-8 flex space-x-3">
            <button x-data @click="navigator.clipboard.writeText(window.location.href); alert('Tautan komik berhasil disalin ke clipboard!');" 
                    class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-bold transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                Share
            </button>
            <button class="bg-black hover:bg-gray-800 text-white px-5 py-2 rounded-full text-sm font-bold transition flex items-center border border-gray-700">
                <span class="text-xl leading-none mr-2">+</span> Favorit
            </button>
        </div>
    </div>

    <div class="max-w-[1000px] mx-auto mt-8 px-4 sm:px-6 flex flex-col md:flex-row gap-10">
        
        <div class="w-full md:w-[65%]">
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex justify-between items-center mb-4">
                <div class="text-sm font-bold text-black text-center w-full">
                    Baca <span class="text-[#00dc64]">10 episode</span> selanjutnya di aplikasi!<br>
                    <span class="text-xs text-gray-500 font-medium">Download app WEBKOMIK di Google Play Store atau App Store.</span>
                </div>
                <div class="hidden sm:block">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=request komik? hub @frhnmzki_ " alt="QR" class="w-14 h-14 rounded">
                </div>
            </div>

            <div class="border-t border-black" x-data="{ readChapters: JSON.parse(localStorage.getItem('readChapters') || '[]') }">
                @forelse($chapters as $chapter)
                    <a href="{{ route('comics.read', ['comic' => $comic->id, 'chapter' => $chapter->id]) }}" wire:navigate
                       @click="if(!readChapters.includes({{ $chapter->id }})) { readChapters.push({{ $chapter->id }}); localStorage.setItem('readChapters', JSON.stringify(readChapters)); }"
                       class="flex items-center justify-between py-3 border-b border-gray-100 transition group"
                       :class="readChapters.includes({{ $chapter->id }}) ? 'bg-white hover:bg-gray-50' : 'hover:bg-gray-50'">
                        
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('storage/' . $chapter->thumbnail) }}" 
                                 class="w-[72px] h-[52px] object-cover rounded border border-gray-200 transition"
                                 :class="readChapters.includes({{ $chapter->id }}) ? 'opacity-60' : 'opacity-100'">
                            
                            <div>
                                <h4 class="text-[13px] font-bold transition line-clamp-1"
                                    :class="readChapters.includes({{ $chapter->id }}) ? 'text-gray-400' : 'text-gray-900 group-hover:text-[#00dc64]'">
                                    {{ $chapter->title }}
                                    @if($loop->first && $chapters->currentPage() == 1)
                                        <span class="text-[#00dc64] text-[10px] ml-1">UP</span>
                                    @endif
                                </h4>
                                <p class="text-[11px] text-gray-400 mt-1">{{ $chapter->published_at ? \Carbon\Carbon::parse($chapter->published_at)->translatedFormat('d M Y') : $chapter->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 text-[12px] font-medium">
                            <span class="flex items-center text-gray-400">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                {{ number_format(rand(1000, 15000), 0, ',', '.') }}
                            </span>
                            <span class="w-8 text-right font-bold transition" 
                                  :class="readChapters.includes({{ $chapter->id }}) ? 'text-gray-300' : 'text-gray-500'">
                                #{{ $chapter->chapter_number }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="py-12 text-center text-gray-500 text-sm font-medium">
                        Kreator belum mengunggah episode untuk komik ini.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $chapters->links() }}
            </div>
        </div>

        <div class="w-full md:w-[35%] pt-2">
            
            <div class="flex items-center space-x-4 mb-6">
                <span class="flex items-center text-[13px] font-bold text-gray-700">
                    <svg class="w-4 h-4 mr-1.5 text-[#00dc64]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                    {{ number_format(rand(1, 50), 1) }}JT
                </span>
                <span class="flex items-center text-[13px] font-bold text-gray-700">
                    <svg class="w-4 h-4 mr-1.5 text-[#00dc64]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path></svg>
                    {{ number_format(rand(10000, 900000), 0, ',', '.') }}
                </span>
            </div>

            <div class="flex items-start mb-6">
                <div class="bg-[#00dc64] text-white text-[10px] font-bold px-1.5 py-0.5 rounded mr-2 mt-0.5">UP</div>
                <p class="text-[14px] font-bold text-black">Update RAB, MIN</p>
            </div>

            <p class="text-[13px] text-gray-600 leading-relaxed mb-6">
                {{ $comic->synopsis }}
            </p>

            @if($comic->is_adult)
                <p class="text-[12px] text-gray-400 mb-8 font-medium">Serial ini memiliki rating Dewasa (17+).</p>
            @else
                <p class="text-[12px] text-gray-400 mb-8 font-medium">Serial ini memiliki rating Semua Umur.</p>
            @endif

            @if($firstChapter)
                <a href="#" class="block w-full text-center bg-[#1b1b1b] hover:bg-black text-white font-bold py-3.5 rounded-full text-[15px] transition shadow-lg">
                    Eps. pertama &gt;
                </a>
            @else
                <button disabled class="w-full text-center bg-gray-200 text-gray-400 font-bold py-3.5 rounded-full text-[15px] cursor-not-allowed">
                    Belum ada episode
                </button>
            @endif
        </div>
    </div>
</div>