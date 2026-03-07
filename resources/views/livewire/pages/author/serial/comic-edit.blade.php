<?php

use App\Models\Comic;
use App\Models\Genre;
use App\Models\User;
use App\Notifications\SubmissionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public Comic $comic;

    public string $title = '';
    public string $synopsis = '';
    public array $selected_genres = [];
    public ?int $prequel_id = null;
    public bool $is_adult = false;

    // Menampung gambar baru jika user melakukan upload ulang
    public $new_square_thumbnail;
    public $new_vertical_thumbnail;
    public $new_banner_image;

    public function mount(Comic $comic)
    {
        // GATE KEAMANAN: Pastikan ini milik author yang login dan belum di-approve
        if ($comic->author_id !== Auth::id()) {
            abort(403, 'Akses Ditolak. Ini bukan karya Anda.');
        }
        if ($comic->status === 'approved') {
            session()->flash('error', 'Serial yang sudah diterbitkan tidak dapat direvisi secara langsung.');
            return redirect()->route('comics.index');
        }

        // Hydration: Mengisi form dengan data dari database
        $this->comic = $comic;
        $this->title = $comic->title;
        $this->synopsis = $comic->synopsis;
        $this->is_adult = $comic->is_adult;
        $this->prequel_id = $comic->prequel_id;
        $this->selected_genres = $comic->genres->pluck('id')->toArray();
    }

    public function with(): array
    {
        return [
            'genres' => Genre::orderBy('name')->get(),
            // Pengecualian: Tidak boleh memilih dirinya sendiri sebagai prekuel
            'myComics' => Comic::where('author_id', Auth::id())->where('id', '!=', $this->comic->id)->where('status', 'approved')->get(),
        ];
    }

    public function updateSerial()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'synopsis' => 'required|string',
            'selected_genres' => 'required|array|min:1',
            'new_square_thumbnail' => 'nullable|image|dimensions:ratio=1/1|max:2048',
            'new_vertical_thumbnail' => 'nullable|image|max:2048',
            'new_banner_image' => 'nullable|image|max:4096',
        ], [
            'new_square_thumbnail.dimensions' => 'Thumbnail persegi wajib memiliki rasio 1:1 (contoh: 500x500 px).'
        ]);

        // FAKTA: Logika Penggantian Gambar & Penghapusan File Lama (Mencegah Storage Penuh)
        if ($this->new_square_thumbnail) {
            Storage::disk('public')->delete($this->comic->square_thumbnail);
            $this->comic->square_thumbnail = $this->new_square_thumbnail->store('comics/square', 'public');
        }
        if ($this->new_vertical_thumbnail) {
            Storage::disk('public')->delete($this->comic->vertical_thumbnail);
            $this->comic->vertical_thumbnail = $this->new_vertical_thumbnail->store('comics/vertical', 'public');
        }
        if ($this->new_banner_image) {
            if ($this->comic->banner_image) Storage::disk('public')->delete($this->comic->banner_image);
            $this->comic->banner_image = $this->new_banner_image->store('comics/banners', 'public');
        }

        // Simpan pembaruan teks & status
        $this->comic->title = $this->title;
        $this->comic->synopsis = $this->synopsis;
        $this->comic->is_adult = $this->is_adult;
        $this->comic->prequel_id = $this->prequel_id ?: null;
        $this->comic->status = 'pending_review'; // Reset status untuk ditinjau ulang admin
        $this->comic->save();

        // Sinkronisasi ulang kategori genre
        $this->comic->genres()->sync($this->selected_genres);

        $admins = User::role('admin')->get();
        Notification::send($admins, new SubmissionNotification('Serial', $this->comic->title));
        session()->flash('success', 'Perubahan Serial berhasil disimpan dan sedang ditinjau ulang!');
        $this->redirectRoute('comics.index', navigate: true);
    }
}; ?>

<div>
    <x-common.page-breadcrumb pageTitle="Revisi Serial" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-8 relative">
        <h3 class="mb-5 text-xl font-bold text-gray-800 dark:text-white lg:mb-7">Edit Detail Serial</h3>

        <form wire:submit="updateSerial">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Thumbnail Persegi (1:1)</label>
                        <input wire:model="new_square_thumbnail" type="file" accept="image/*" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 dark:border-gray-700 dark:file:bg-gray-800 dark:text-gray-400" />
                        <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah gambar.</p>

                        @if ($new_square_thumbnail)
                            <img src="{{ $new_square_thumbnail->temporaryUrl() }}" class="mt-2 h-32 w-32 object-cover rounded-lg shadow border-2 border-brand-500">
                        @else
                            <img src="{{ asset('storage/' . $comic->square_thumbnail) }}" class="mt-2 h-32 w-32 object-cover rounded-lg shadow opacity-80">
                        @endif
                        <x-input-error :messages="$errors->get('new_square_thumbnail')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Thumbnail Vertikal (9:16)</label>
                        <input wire:model="new_vertical_thumbnail" type="file" accept="image/*" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 dark:border-gray-700 dark:file:bg-gray-800 dark:text-gray-400" />

                        @if ($new_vertical_thumbnail)
                            <img src="{{ $new_vertical_thumbnail->temporaryUrl() }}" class="mt-2 h-48 w-32 object-cover rounded-lg shadow border-2 border-brand-500">
                        @else
                            <img src="{{ asset('storage/' . $comic->vertical_thumbnail) }}" class="mt-2 h-48 w-32 object-cover rounded-lg shadow opacity-80">
                        @endif
                        <x-input-error :messages="$errors->get('new_vertical_thumbnail')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Banner Background</label>
                        <input wire:model="new_banner_image" type="file" accept="image/*" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 dark:border-gray-700 dark:file:bg-gray-800 dark:text-gray-400" />

                        @if ($new_banner_image)
                            <img src="{{ $new_banner_image->temporaryUrl() }}" class="mt-2 h-24 w-full object-cover rounded-lg shadow border-2 border-brand-500">
                        @elseif($comic->banner_image)
                            <img src="{{ asset('storage/' . $comic->banner_image) }}" class="mt-2 h-24 w-full object-cover rounded-lg shadow opacity-80">
                        @endif
                        <x-input-error :messages="$errors->get('new_banner_image')" class="mt-1 text-sm text-red-500" />
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Judul Komik <span class="text-red-500">*</span></label>
                        <input wire:model="title" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Sinopsis Cerita <span class="text-red-500">*</span></label>
                        <textarea wire:model="synopsis" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white" required></textarea>
                        <x-input-error :messages="$errors->get('synopsis')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Kategori (Pilih minimal 1)</label>
                            <div class="h-32 overflow-y-auto p-3 border border-gray-300 rounded-lg dark:border-gray-700 space-y-2 bg-gray-50 dark:bg-gray-800/50">
                                @foreach($genres as $genre)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" wire:model="selected_genres" value="{{ $genre->id }}" class="rounded border-gray-300 text-brand-500">
                                        <span class="text-sm dark:text-gray-300">{{ $genre->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('selected_genres')" class="mt-1 text-sm text-red-500" />
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Lanjutan dari Serial</label>
                                <select wire:model="prequel_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 dark:border-gray-700 dark:text-white">
                                    <option value="">Bukan Lanjutan (Karya Baru)</option>
                                    @foreach($myComics as $myComic)
                                        <option value="{{ $myComic->id }}">{{ $myComic->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="flex items-center space-x-3 p-4 border border-red-200 bg-red-50 rounded-lg dark:border-red-900/50 dark:bg-red-900/10">
                                <input type="checkbox" wire:model="is_adult" class="h-5 w-5 rounded border-red-300 text-red-600 focus:ring-red-500">
                                <span class="text-sm font-medium text-red-800 dark:text-red-400">Karya ini mengandung unsur dewasa (17+).</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end pt-5 border-t border-gray-200 dark:border-gray-800">
                <button type="submit" class="bg-brand-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-brand-600 transition shadow-theme-md flex items-center">
                    <span wire:loading.remove wire:target="updateSerial">Simpan Perubahan &rarr;</span>
                    <span wire:loading wire:target="updateSerial">
                        <svg class="animate-spin h-5 w-5 mr-2 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
