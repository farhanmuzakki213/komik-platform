<div>
    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-6 py-4 font-medium">Episode</th>
                <th class="px-6 py-4 font-medium">Serial Induk</th>
                <th class="px-6 py-4 font-medium">Waktu Pengajuan</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse($pendingChapters as $chapter)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                    <td class="px-6 py-4 flex items-center space-x-4">
                        <img src="{{ asset('storage/' . $chapter->thumbnail) }}" class="h-10 w-16 object-cover rounded shadow-sm border border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">Ep. {{ $chapter->chapter_number }} - {{ $chapter->title }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium">{{ $chapter->comic->title }}</span><br>
                        <span class="text-xs text-gray-500">Oleh: {{ $chapter->comic->author->name }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $chapter->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4 text-right">
                        <button wire:click="loadChapterPreview({{ $chapter->id }})" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 px-4 py-2 rounded-lg font-medium transition shadow-sm">
                            Review Konten
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Tidak ada antrean episode baru saat ini. ✨</td></tr>
            @endforelse
        </tbody>
    </table>

    <x-ui.modal x-data="{ open: false }" @open-modal.window="if($event.detail[0] === 'preview-chapter-modal') open = true" @close-modal.window="if($event.detail[0] === 'preview-chapter-modal') open = false" :isOpen="false" class="max-w-[500px]">
        @if($previewChapter)
        <div class="relative w-full max-w-[500px] overflow-hidden rounded-3xl bg-black shadow-2xl flex flex-col h-[90vh]">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950">
                <div>
                    <h3 class="text-white font-bold truncate w-64">{{ $previewChapter->comic->title }}</h3>
                    <p class="text-gray-400 text-xs">Ep. {{ $previewChapter->chapter_number }} - {{ $previewChapter->title }}</p>
                </div>
                <button @click="open = false" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            <div class="flex-1 overflow-y-auto bg-white flex flex-col custom-scrollbar">
                @foreach($previewChapter->panels as $panel)
                    <img src="{{ asset('storage/' . $panel->image_path) }}" class="w-full h-auto block m-0 p-0 leading-none">
                @endforeach
            </div>

            <div class="p-4 border-t border-gray-800 bg-gray-950 flex justify-between">
                <button wire:click="openRejectModal({{ $previewChapter->id }})" class="px-4 py-2 border border-red-500 text-red-500 hover:bg-red-500/10 rounded-lg text-sm font-medium transition">Tolak</button>
                <button wire:click="approveChapter({{ $previewChapter->id }})" class="px-4 py-2 bg-brand-500 text-white hover:bg-brand-600 rounded-lg text-sm font-medium transition">Setujui Episode</button>
            </div>
        </div>
        @endif
    </x-ui.modal>

    <x-ui.modal x-data="{ open: false }" @open-modal.window="if($event.detail[0] === 'reject-chapter-modal') open = true" @close-modal.window="if($event.detail[0] === 'reject-chapter-modal') open = false" :isOpen="false" class="max-w-[500px]">
        <div class="relative w-full max-w-[500px] overflow-hidden rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tolak Episode</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Berikan catatan mengapa episode ini ditolak.</p>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan untuk Penulis <span class="text-red-500">*</span></label>
                <textarea wire:model="adminNotes" rows="3" class="w-full rounded-lg border border-gray-300 p-3 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-brand-500"></textarea>
                <x-input-error :messages="$errors->get('adminNotes')" class="mt-1 text-sm text-red-500" />
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="open = false" class="px-5 py-2 text-sm font-medium bg-gray-100 rounded-lg dark:bg-gray-800 dark:text-white">Batal</button>
                <button wire:click="confirmReject" class="px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 flex items-center shadow-sm">
                    <span wire:loading.remove wire:target="confirmReject">Konfirmasi Penolakan</span>
                    <span wire:loading wire:target="confirmReject">Memproses...</span>
                </button>
            </div>
        </div>
    </x-ui.modal>
</div>
