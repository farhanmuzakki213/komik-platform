<?php

use App\Models\Comic;
use App\Models\Genre;
use App\Models\User;
use App\Notifications\SubmissionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public string $title = '';
    public string $synopsis = '';
    public $square_thumbnail;
    public $vertical_thumbnail;
    public $banner_image;
    public array $selected_genres = [];
    public ?int $prequel_id = null;
    public bool $is_adult = false;

    public function with(): array
    {
        return [
            'genres' => Genre::orderBy('name')->get(),
            'myComics' => Comic::where('author_id', Auth::id())->where('status', 'approved')->get(),
        ];
    }

    public function submitSerial()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'synopsis' => 'required|string',
            'square_thumbnail' => 'required|image|dimensions:ratio=1/1|max:2048',
            'vertical_thumbnail' => 'required|image|dimensions:ratio=9/16|max:2048',
            'banner_image' => 'nullable|image|max:4096',
            'selected_genres' => 'required|array|min:1',
            'is_adult' => 'boolean'
        ]);

        $comic = Comic::create([
            'author_id' => Auth::id(),
            'title' => $this->title,
            'slug' => Str::slug($this->title . '-' . uniqid()),
            'synopsis' => $this->synopsis,
            'square_thumbnail' => $this->square_thumbnail->store('comics/square', 'public'),
            'vertical_thumbnail' => $this->vertical_thumbnail->store('comics/vertical', 'public'),
            'banner_image' => $this->banner_image ? $this->banner_image->store('comics/banners', 'public') : null,
            'is_adult' => $this->is_adult,
            'prequel_id' => $this->prequel_id ?: null,
            'status' => 'pending_review',
        ]);

        $comic->genres()->sync($this->selected_genres);

        $admins = User::role('admin')->get();
        Notification::send($admins, new SubmissionNotification('Serial', $comic->title));

        session()->flash('success', 'Serial berhasil dibuat! Silakan unggah episode perdana Anda.');
        $this->redirectRoute('author.episodes.create', ['comic' => $comic->id], navigate: true);
    }
}; ?>

<div>
    <x-common.page-breadcrumb pageTitle="Buat Serial Baru" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-8 relative">
        <h3 class="mb-5 text-xl font-bold text-gray-800 dark:text-white lg:mb-7">Detail Serial</h3>

        <form wire:submit="submitSerial">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Thumbnail Persegi (1:1) <span class="text-red-500">*</span></label>
                        <input wire:model="square_thumbnail" type="file" accept="image/*" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 dark:border-gray-700 dark:file:bg-gray-800 dark:text-gray-400" required />
                        @if ($square_thumbnail) <img src="{{ $square_thumbnail->temporaryUrl() }}" class="mt-2 h-32 w-32 object-cover rounded-lg shadow"> @endif
                        <x-input-error :messages="$errors->get('square_thumbnail')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Thumbnail Vertikal (9:16) <span class="text-red-500">*</span></label>
                        <input wire:model="vertical_thumbnail" type="file" accept="image/*" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 dark:border-gray-700 dark:file:bg-gray-800 dark:text-gray-400" required />
                        @if ($vertical_thumbnail) <img src="{{ $vertical_thumbnail->temporaryUrl() }}" class="mt-2 h-48 w-32 object-cover rounded-lg shadow"> @endif
                        <x-input-error :messages="$errors->get('vertical_thumbnail')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Banner Background</label>
                        <input wire:model="banner_image" type="file" accept="image/*" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 dark:border-gray-700 dark:file:bg-gray-800 dark:text-gray-400" />
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
                <button type="submit" class="bg-brand-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-brand-600 transition shadow-theme-md">
                    <span wire:loading.remove wire:target="submitSerial">Lanjut ke Upload Episode &rarr;</span>
                    <span wire:loading wire:target="submitSerial">Menyimpan Serial...</span>
                </button>
            </div>
        </form>
    </div>
</div>
