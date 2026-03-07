<?php

namespace App\Livewire;

use App\Models\Comic;
use App\Notifications\ReviewResultNotification;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AdminComicManager extends Component
{
    public ?Comic $previewComic = null;
    public $rejectingId = null;

    public function loadComicPreview($id)
    {
        $this->previewComic = Comic::with(['genres', 'author'])->findOrFail($id);
        $this->dispatch('open-modal', 'preview-comic-modal');
    }

    public function approveComic($id)
    {
        $comic = Comic::findOrFail($id);
        $comic->update([
            'status' => 'approved',
            'published_at' => $comic->published_at ?: now()
        ]);

        $this->dispatch('close-modal', 'preview-comic-modal');
        $comic->author->notify(new ReviewResultNotification('Serial', $comic->title, 'approved'));
        session()->flash('success', 'Serial "' . $comic->title . '" berhasil disetujui!');
    }

    public function openRejectModal($id)
    {
        $this->rejectingId = $id;
        $this->dispatch('close-modal', 'preview-comic-modal');
        $this->dispatch('open-modal', 'reject-modal');
    }

    public function confirmReject()
    {
        Comic::where('id', $this->rejectingId)->update(['status' => 'rejected']);
        $this->dispatch('close-modal', 'reject-modal');
        $comic = Comic::find($this->rejectingId);
        $comic->author->notify(new ReviewResultNotification('Serial', $comic->title, 'rejected'));
        session()->flash('error', 'Serial telah ditolak.');
    }

    public function render()
    {
        return view('livewire.pages.admin.admin-comic-manager', [
            'pendingComics' => Comic::with('author')->where('status', 'pending_review')->latest()->get()
        ]);
    }
}
