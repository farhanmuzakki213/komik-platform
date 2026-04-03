<?php

use App\Models\Comic;
use App\Models\Chapter;
use App\Models\Panel;
use App\Models\User;
use App\Notifications\SubmissionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public Comic $comic;

    public string $ep_title = '';
    public $ep_thumbnail;
    public string $creator_note = '';
    public bool $allow_comments = true;
    public string $published_at = '';

    public $temporary_uploads = [];
    public $upload_pool = [];
    public array $panels = [];

    public function updatedTemporaryUploads()
    {
        $this->validate([
            'temporary_uploads.*' => 'image|max:5120',
        ]);

        foreach ($this->temporary_uploads as $file) {
            $ref = uniqid('upload_');
            $this->upload_pool[$ref] = $file;

            $this->panels[] = [
                'uuid' => uniqid('panel_'),
                'type' => 'new',
                'file_ref' => $ref,
                'url' => $file->temporaryUrl(),
                'name' => $file->getClientOriginalName(),
            ];
        }
        $this->temporary_uploads = [];
    }

    public function removePanel($uuid)
    {
        foreach ($this->panels as $key => $panel) {
            if ($panel['uuid'] === $uuid) {
                if ($panel['type'] === 'new') {
                    unset($this->upload_pool[$panel['file_ref']]);
                }
                unset($this->panels[$key]);
                break;
            }
        }
        $this->panels = array_values($this->panels);
    }

    public function reorderPanels($orderedUuids)
    {
        $newPanels = [];
        $panelsByUuid = collect($this->panels)->keyBy('uuid')->toArray();
        foreach ($orderedUuids as $uuid) {
            if (isset($panelsByUuid[$uuid])) {
                $newPanels[] = $panelsByUuid[$uuid];
            }
        }
        $this->panels = $newPanels;
    }

    public function submitEpisode()
    {
        $this->validate([
            'ep_title' => 'required|string|max:255',
            'ep_thumbnail' => 'required|image|max:1024',
            'panels' => 'required|array|min:1',
            'published_at' => 'nullable|date',
        ], [
            'panels.required' => 'Episode harus memiliki setidaknya 1 gambar panel.'
        ]);

        $nextChapterNum = Chapter::where('comic_id', $this->comic->id)->max('chapter_number') + 1;

        \DB::transaction(function () use ($nextChapterNum) {
            $chapter = Chapter::create([
                'comic_id' => $this->comic->id,
                'chapter_number' => $nextChapterNum,
                'title' => $this->ep_title,
                'thumbnail' => $this->ep_thumbnail->store('chapters/thumbnails', 'public'),
                'creator_note' => $this->creator_note,
                'allow_comments' => $this->allow_comments,
                'status' => 'pending_review',
                'published_at' => $this->published_at ?: now(),
            ]);

            $manager = new ImageManager(new Driver());
            $globalOrderIndex = 0;

            foreach ($this->panels as $panel) {
                $file = $this->upload_pool[$panel['file_ref']];
                $image = $manager->read($file->getRealPath());
                $width = $image->width();
                $height = $image->height();

                if ($width > 800) {
                    $image->scale(width: 800);
                    $height = $image->height();
                }

                if ($height > 1280) {
                    $slices = ceil($height / 1280);
                    for ($i = 0; $i < $slices; $i++) {
                        $sliceHeight = ($i == $slices - 1) ? ($height - ($i * 1280)) : 1280;
                        $slicey = $i * 1280;
                        $slice = $manager->read($file->getRealPath())->scale(width: 800)->crop(800, $sliceHeight, 0, $slicey);

                        $path = 'panels/' . uniqid() . '.jpg';
                        Storage::disk('public')->put($path, (string) $slice->toJpeg(85));

                        Panel::create(['chapter_id' => $chapter->id, 'image_path' => $path, 'order_index' => $globalOrderIndex++]);
                    }
                } else {
                    $path = $file->store('panels', 'public');
                    Panel::create(['chapter_id' => $chapter->id, 'image_path' => $path, 'order_index' => $globalOrderIndex++]);
                }
            }
        });

        $admins = User::role('admin')->get();
        Notification::send($admins, new SubmissionNotification('Episode', $this->ep_title));

        session()->flash('success', 'Episode berhasil ditambahkan dan menunggu peninjauan!');
        $this->redirectRoute('comics.index', navigate: true);
    }
}; ?>

<div x-data="{ previewMode: false, device: 'mobile' }">
    <x-common.page-breadcrumb pageTitle="Unggah Episode" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:p-8">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-5 lg:mb-7 border-b border-gray-200 dark:border-gray-800 pb-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Episode Baru: <span class="text-brand-500">{{ $comic->title }}</span></h3>
            <button @click="previewMode = true" type="button" class="mt-3 md:mt-0 text-brand-500 border border-brand-500 px-4 py-2 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-900/20">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> Pratinjau Episode
            </button>
        </div>

        <form wire:submit="submitEpisode">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Judul Episode <span class="text-red-500">*</span></label>
                        <input wire:model="ep_title" type="text" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-800 dark:text-white" required />
                        <x-input-error :messages="$errors->get('ep_title')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Thumbnail (202x142) <span class="text-red-500">*</span></label>
                        <input wire:model="ep_thumbnail" type="file" accept="image/*" class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" required />
                        @if ($ep_thumbnail) <img src="{{ $ep_thumbnail->temporaryUrl() }}" class="mt-3 h-24 object-cover rounded shadow border-2 border-brand-500"> @endif
                        <x-input-error :messages="$errors->get('ep_thumbnail')" class="mt-1 text-sm text-red-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Jadwal Terbit</label>
                        <input wire:model="published_at" type="datetime-local" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan Kreator</label>
                        <textarea wire:model="creator_note" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" wire:model="allow_comments" class="rounded border-gray-300 text-brand-500">
                        <span class="text-sm dark:text-gray-300">Izinkan Komentar Pembaca</span>
                    </label>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Unggah & Susun Potongan Komik</label>
                    <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Anda dapat menambah gambar kapan saja. Seret baris untuk mengubah urutan tayang.</p>

                    <div class="border-2 border-dashed border-brand-300 dark:border-brand-700 bg-brand-50 dark:bg-brand-900/10 rounded-xl p-6 text-center relative mb-4 transition hover:bg-brand-100 dark:hover:bg-brand-900/30">
                        <input wire:model.live="temporary_uploads" type="file" multiple accept="image/jpeg,image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                        <svg class="mx-auto h-10 w-10 text-brand-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <p class="font-medium text-brand-600 dark:text-brand-400">Klik / Drag File Tambahan ke Sini</p>
                    </div>

                    <div wire:loading wire:target="temporary_uploads" class="text-brand-500 text-sm mb-4 font-semibold flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyiapkan gambar...
                    </div>
                    <x-input-error :messages="$errors->get('panels')" class="mb-4 text-sm text-red-500" />

                    @if(count($panels) > 0)
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold mb-3 text-gray-700 dark:text-gray-300">Urutan Panel Saat Ini ({{ count($panels) }} Gambar)</h4>
                            <ul class="space-y-2" x-data="{ draggingUuid: null }">
                                @foreach($panels as $index => $panel)
                                    <li data-uuid="{{ $panel['uuid'] }}" draggable="true"
                                        @dragstart="draggingUuid = '{{ $panel['uuid'] }}'; $el.classList.add('opacity-50')"
                                        @dragend="$el.classList.remove('opacity-50')"
                                        @dragover.prevent
                                        @drop="
                                            let target = $el;
                                            let list = target.parentNode;
                                            let draggables = [...list.querySelectorAll('li')];
                                            let dragIndex = draggables.findIndex(el => el.dataset.uuid == draggingUuid);
                                            let dropIndex = draggables.indexOf(target);
                                            if (dragIndex < dropIndex) { target.after(list.children[dragIndex]); }
                                            else { target.before(list.children[dragIndex]); }
                                            let ordered = [...list.querySelectorAll('li')].map(el => el.dataset.uuid);
                                            $wire.reorderPanels(ordered);
                                        "
                                        class="flex items-center justify-between bg-white dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700 cursor-move shadow-sm hover:border-brand-500 transition-colors">

                                        <div class="flex items-center space-x-4">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                            <span class="font-bold text-gray-500 w-6">{{ $index + 1 }}</span>
                                            <img src="{{ $panel['url'] }}" class="h-12 w-12 object-cover rounded shadow-sm">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate w-32 md:w-56">{{ $panel['name'] }}</span>
                                                <span class="text-xs text-brand-500 font-semibold">Baru Diunggah</span>
                                            </div>
                                        </div>
                                        <button wire:click="removePanel('{{ $panel['uuid'] }}')" type="button" class="text-red-500 hover:text-red-700 p-2 bg-red-50 rounded dark:bg-red-900/20"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">Belum ada gambar yang dimasukkan ke dalam kanvas.</div>
                    @endif
                </div>
            </div>

            <div class="mt-8 flex justify-end pt-5 border-t border-gray-200 dark:border-gray-800">
                <button type="submit" class="bg-brand-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-brand-600 transition flex items-center shadow-theme-md">
                    <span wire:loading.remove wire:target="submitEpisode">Terbitkan Episode</span>
                    <span wire:loading wire:target="submitEpisode">Menyimpan Episode...</span>
                </button>
            </div>
        </form>
    </div>

    <div x-show="previewMode" style="display: none;" class="fixed inset-0 z-99999 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-gray-900 rounded-xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden border border-gray-700 shadow-2xl">
            <div class="flex justify-between items-center p-4 border-b border-gray-800 bg-gray-950">
                <h4 class="text-white font-semibold">Mode Pratinjau Pembaca</h4>
                <div class="flex space-x-2 bg-gray-800 rounded-lg p-1">
                    <button @click="device = 'mobile'" :class="device === 'mobile' ? 'bg-brand-500 text-white' : 'text-gray-400'" class="px-3 py-1.5 rounded-md text-sm font-medium transition">Mobile</button>
                    <button @click="device = 'pc'" :class="device === 'pc' ? 'bg-brand-500 text-white' : 'text-gray-400'" class="px-3 py-1.5 rounded-md text-sm font-medium transition">PC</button>
                </div>
                <button @click="previewMode = false" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 flex justify-center bg-black">
                <div :class="device === 'mobile' ? 'w-[400px]' : 'w-[800px]'" class="bg-white flex flex-col transition-all duration-300 shadow-2xl">
                    <div class="flex flex-col w-full">
                        @forelse($panels as $panel)
                            <img src="{{ $panel['url'] }}" class="w-full h-auto block m-0 p-0 leading-none">
                        @empty
                            <div class="p-10 text-center text-gray-500">Kanvas masih kosong.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
