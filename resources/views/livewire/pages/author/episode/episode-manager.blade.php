<?php

use App\Models\Comic;
use App\Models\Chapter;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public Comic $comic;

    public function mount(Comic $comic)
    {
        if ($comic->author_id !== Auth::id()) {
            abort(403);
        }
        $this->comic = $comic;
    }

    #[On('trigger-delete-episode')]
    public function deleteEpisode($id)
    {
        $chapter = Chapter::where('comic_id', $this->comic->id)->findOrFail($id);

        if (in_array($chapter->status, ['approved', 'rejected'])) {
            session()->flash('error', 'Episode dengan status ini tidak dapat dihapus.');
            return;
        }

        // Hapus fisik gambar panel & thumbnail
        Storage::disk('public')->delete($chapter->thumbnail);
        foreach ($chapter->panels as $panel) {
            Storage::disk('public')->delete($panel->image_path);
        }

        $chapter->delete();
        session()->flash('success', 'Episode berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'chapters' => Chapter::where('comic_id', $this->comic->id)->orderBy('chapter_number', 'desc')->get(),
        ];
    }
}; ?>

<div>
    <x-common.page-breadcrumb pageTitle="Daftar Episode: {{ $comic->title }}" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        <div class="flex justify-end items-center mb-6">
            <a href="{{ route('author.episodes.create', $comic->id) }}" wire:navigate
                class="bg-brand-500 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-brand-600">
                + Tambah Episode Baru
            </a>
        </div>

        @if (session()->has('success') || session()->has('error'))
            <div
                class="mb-4 p-4 text-sm rounded-lg {{ session()->has('success') ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-6 py-4 font-medium">Episode</th>
                        <th class="px-6 py-4 font-medium">Judul</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($chapters as $chapter)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Ep.
                                {{ $chapter->chapter_number }}</td>
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <img src="{{ asset('storage/' . $chapter->thumbnail) }}"
                                    class="h-10 w-14 object-cover rounded shadow-sm">
                                <span>{{ $chapter->title }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($chapter->status === 'approved')
                                    <span class="text-green-500 font-medium">Approved</span>
                                @elseif($chapter->status === 'pending_review')
                                    <span class="text-yellow-500 font-medium">Menunggu Review</span>
                                @else
                                    <span class="text-gray-500 font-medium">Draft / Ditolak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if (!in_array($chapter->status, ['approved', 'rejected']))
                                    <a href="{{ route('author.episodes.edit', $chapter->chapter_number) }}" wire:navigate
                                        class="text-blue-600 hover:text-blue-700 font-medium bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 rounded inline-block">
                                        Revisi
                                    </a>
                                    <button
                                        @click="$dispatch('open-delete-modal', {
                                            id: {{ $chapter->chapter_number }},
                                            eventName: 'trigger-delete-episode',
                                            title: 'Hapus Episode {{ $chapter->chapter_number }}?',
                                            message: 'Hapus permanen episode ini beserta semua panel gambarnya?'
                                        })"
                                        class="text-red-500 hover:text-red-700 font-medium bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded">
                                        Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada episode di serial
                                ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
