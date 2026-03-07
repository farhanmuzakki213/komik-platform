<?php

namespace App\Livewire;

use App\Models\Chapter;
use App\Notifications\ReviewResultNotification;
use Livewire\Component;

class AdminChapterTable extends Component
{
    public ?Chapter $previewChapter = null;
    public $rejectingId = null;
    public string $adminNotes = '';

    public function loadChapterPreview($id)
    {
        $this->previewChapter = Chapter::with(['comic', 'panels'])->findOrFail($id);
        $this->dispatch('open-modal', 'preview-chapter-modal');
    }

    public function approveChapter($id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->update([
            'status' => 'approved',
            'published_at' => $chapter->published_at ?: now()
        ]);

        $this->dispatch('close-modal', 'preview-chapter-modal');
        $chapter->comic->author->notify(new ReviewResultNotification('Episode', $chapter->title, 'approved'));
        session()->flash('success', 'Episode "' . $chapter->title . '" berhasil disetujui!');
    }

    public function openRejectModal($id)
    {
        $this->rejectingId = $id;
        $this->adminNotes = '';
        $this->dispatch('close-modal', 'preview-chapter-modal');
        $this->dispatch('open-modal', 'reject-chapter-modal');
    }

    public function confirmReject()
    {
        $this->validate([
            'adminNotes' => 'required|string|min:5'
        ], [
            'adminNotes.required' => 'Catatan penolakan wajib diisi agar penulis tahu apa yang harus diperbaiki.'
        ]);

        Chapter::where('id', $this->rejectingId)->update([
            'status' => 'rejected',
            'admin_notes' => $this->adminNotes
        ]);

        $this->dispatch('close-modal', 'reject-chapter-modal');
        $chapter = Chapter::find($this->rejectingId);
        $chapter->comic->author->notify(new ReviewResultNotification('Episode', $chapter->title, 'rejected', $this->adminNotes));
        session()->flash('error', 'Episode telah ditolak dan dikembalikan ke penulis.');
    }

    public function render()
    {
        return view('livewire.pages.admin.admin-chapter-table', [
            'pendingChapters' => Chapter::with(['comic', 'comic.author'])
                ->where('status', 'pending_review')
                ->latest()
                ->get()
        ]);
    }
}
