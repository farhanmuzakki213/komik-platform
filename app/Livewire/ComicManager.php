<?php

namespace App\Livewire;

use App\Models\Comic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class ComicManager extends Component
{
    public function mount()
    {
        if (Comic::where('author_id', Auth::id())->count() === 0) {
            return redirect()->route('author.comics.create');
        }
    }

    #[On('trigger-delete-comic')]
    public function deleteComic($id)
    {
        $comic = Comic::where('author_id', Auth::id())->findOrFail($id);

        if (in_array($comic->status, ['approved', 'rejected'])) {
            session()->flash('error', 'Serial dengan status ini tidak dapat dihapus.');
            return;
        }

        Storage::disk('public')->delete([
            $comic->square_thumbnail,
            $comic->vertical_thumbnail,
            $comic->banner_image
        ]);

        foreach ($comic->chapters as $chapter) {
            Storage::disk('public')->delete($chapter->thumbnail);
            foreach ($chapter->panels as $panel) {
                Storage::disk('public')->delete($panel->image_path);
            }
        }

        $comic->delete();
        session()->flash('success', 'Serial dan seluruh episodenya berhasil dihapus.');
    }

    public function render()
    {
        $myComics = Comic::where('author_id', Auth::id())
                        ->withCount('chapters')
                        ->orderBy('updated_at', 'desc')
                        ->get();

        return view('livewire.pages.author.comic-manager', [
            'comics' => $myComics
        ]);
    }
}
