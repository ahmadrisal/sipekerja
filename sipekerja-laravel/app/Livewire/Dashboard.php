<?php

namespace App\Livewire;

use App\Models\Rating;
use App\Models\Team;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $role = session('active_role');

        return view('livewire.dashboard', [
            'role' => $role,
        ])->layout('layouts.app')->title('Dashboard Overview');
    }
}
