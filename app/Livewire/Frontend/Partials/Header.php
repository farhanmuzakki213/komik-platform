<?php

namespace App\Livewire\Frontend\Partials;

use App\Models\Comic;
use App\Models\User;
use Livewire\Component;

class Header extends Component
{
    public $search = '';
    public $seriesResults = [];
    public $creatorResults = [];
    public $showDropdown = false;

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            // Cari Komik (Series) yang sudah di-approve
            $this->seriesResults = Comic::with('author', 'genres')
                ->where('status', 'approved')
                ->where('title', 'like', '%' . $this->search . '%')
                ->take(3)
                ->get();

            // Cari Penulis (Creators)
            $this->creatorResults = User::role('penulis')
                ->where('name', 'like', '%' . $this->search . '%')
                ->take(3)
                ->get();

            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
        }
    }

    public function closeSearch()
    {
        $this->showDropdown = false;
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.frontend.partials.header');
    }
}