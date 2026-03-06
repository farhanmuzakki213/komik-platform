<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminComicManager extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.admin-comic-manager');
    }
}
