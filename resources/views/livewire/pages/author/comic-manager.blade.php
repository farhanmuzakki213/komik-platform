<div>
    <x-common.page-breadcrumb pageTitle="Manajemen Karya Saya" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Pilih Serial</h3>
            <a href="{{ route('author.comics.create') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition shadow-theme-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Serial Baru
            </a>
        </div>


        @if (session()->has('success'))
            <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($comics as $comic)
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden shadow-sm flex flex-col">
                    <div class="relative h-48 w-full bg-gray-100 dark:bg-gray-800">
                        <img src="{{ asset('storage/' . $comic->square_thumbnail) }}" alt="{{ $comic->title }}"
                            class="w-full h-full object-cover">

                        <div class="absolute top-3 left-3">
                            @if ($comic->status === 'approved')
                                <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Aktif
                                    Diterbitkan</span>
                            @elseif($comic->status === 'pending_review')
                                <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">Menunggu
                                    Review
                                    Admin</span>
                            @else
                                <span class="bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded">Draft /
                                    Ditolak</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-1">{{ $comic->title }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">{{ $comic->synopsis }}</p>

                        <div
                            class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap justify-between items-center gap-2">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{ $comic->chapters_count }} Episode
                            </span>

                            <div class="flex items-center space-x-2">
                                <a href="{{ route('author.episodes.index', $comic->id) }}" wire:navigate
                                    class="text-gray-600 hover:text-brand-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400 px-3 py-1.5 rounded-md text-sm font-medium transition">
                                    Daftar Episode
                                </a>

                                @if ($comic->status !== 'approved')
                                    <a href="{{ route('author.comics.edit', $comic->id) }}" wire:navigate
                                        class="text-blue-600 hover:text-blue-700 bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 rounded-md text-sm font-medium transition">
                                        Revisi
                                    </a>
                                @endif

                                @if (!in_array($comic->status, ['approved', 'rejected']))
                                    <button
                                        @click="$dispatch('open-delete-modal', {
                                            id: {{ $comic->id }},
                                            eventName: 'trigger-delete-comic',
                                            title: 'Hapus Serial: {{ addslashes($comic->title) }}?',
                                            message: 'Menghapus serial ini akan menghapus SELURUH EPISODE di dalamnya secara permanen. Anda yakin?'
                                        })"
                                        class="text-red-600 hover:text-red-700 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-md text-sm font-medium transition">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
