<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CreatorDashboard extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.author.creator-dashboard');
    }
}
