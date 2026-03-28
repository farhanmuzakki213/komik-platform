<div x-data="{ activeTab: 'comics' }">
    <x-common.page-breadcrumb pageTitle="Review Serial Baru" />

    @if (session()->has('success'))
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden shadow-sm">
        <div class="flex border-b border-gray-200 dark:border-gray-800">
            <button @click="activeTab = 'comics'"
                    :class="activeTab === 'comics' ? 'border-brand-500 text-brand-600 dark:text-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5'"
                    class="flex-1 py-4 px-6 text-center font-semibold text-sm border-b-2 transition-colors">
                Antrean Serial
            </button>
            <button @click="activeTab = 'chapters'"
                    :class="activeTab === 'chapters' ? 'border-brand-500 text-brand-600 dark:text-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5'"
                    class="flex-1 py-4 px-6 text-center font-semibold text-sm border-b-2 transition-colors">
                Antrean Episode
            </button>
        </div>

        <div x-show="activeTab === 'comics'" class="p-0">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-6 py-4 font-medium">Serial</th>
                        <th class="px-6 py-4 font-medium">Penulis</th>
                        <th class="px-6 py-4 font-medium">Rating 17+</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($pendingComics as $comic)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 flex items-center space-x-4">
                                <img src="{{ asset('storage/' . $comic->square_thumbnail) }}" class="h-12 w-12 object-cover rounded shadow-sm">
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-white">{{ $comic->title }}</p>
                                    <p class="text-xs text-gray-500 truncate w-48">{{ $comic->synopsis }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $comic->author->name }}</td>
                            <td class="px-6 py-4">
                                @if($comic->is_adult) <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Ya (17+)</span>
                                @else <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Aman</span> @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="loadComicPreview({{ $comic->slug }})" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 px-4 py-2 rounded-lg font-medium transition shadow-sm">Review</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Tidak ada antrean serial baru saat ini. ✨</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div x-show="activeTab === 'chapters'" style="display: none;" class="p-0">
            <livewire:admin-chapter-table />
        </div>
    </div>

    <x-ui.modal x-data="{ open: false }" @open-modal.window="if($event.detail[0] === 'preview-comic-modal') open = true" @close-modal.window="if($event.detail[0] === 'preview-comic-modal') open = false" :isOpen="false" class="max-w-[800px]">
        @if($previewComic)
        <div class="relative w-full max-w-[800px] overflow-hidden rounded-3xl bg-white dark:bg-gray-900 shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Review Pengajuan Serial</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div class="flex flex-col md:flex-row gap-6">
                    <img src="{{ asset('storage/' . $previewComic->vertical_thumbnail) }}" class="w-48 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 object-cover">
                    <div class="space-y-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $previewComic->title }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($previewComic->genres as $genre)
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full text-xs font-medium">{{ $genre->name }}</span>
                            @endforeach
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-1">Sinopsis:</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $previewComic->synopsis }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 flex justify-end space-x-3 dark:bg-gray-950">
                <button wire:click="openRejectModal({{ $previewComic->id }})" class="px-6 py-2.5 bg-white border border-red-200 text-red-600 rounded-lg font-medium transition dark:bg-gray-800 dark:border-red-900/50">Tolak Pengajuan</button>
                <button wire:click="approveComic({{ $previewComic->id }})" class="px-6 py-2.5 bg-brand-500 text-white rounded-lg font-medium transition">Setujui & Terbitkan</button>
            </div>
        </div>
        @endif
    </x-ui.modal>

    <x-ui.modal x-data="{ open: false }" @open-modal.window="if($event.detail[0] === 'reject-modal') open = true" @close-modal.window="if($event.detail[0] === 'reject-modal') open = false" :isOpen="false" class="max-w-[400px]">
        <div class="relative w-full max-w-[400px] overflow-hidden rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tolak Serial</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Serial akan dikembalikan ke penulis dengan status Ditolak.</p>
            <div class="flex justify-end space-x-3">
                <button @click="open = false" class="px-5 py-2 text-sm font-medium bg-gray-100 rounded-lg dark:bg-gray-800 dark:text-white">Batal</button>
                <button wire:click="confirmReject" class="px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Ya, Tolak</button>
            </div>
        </div>
    </x-ui.modal>
</div>
