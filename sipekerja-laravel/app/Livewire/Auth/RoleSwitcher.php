<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RoleSwitcher extends Component
{
    public $activeRole;

    public function mount()
    {
        $this->activeRole = session('active_role');
    }

    public function switchRole($role)
    {
        if (Auth::user()->hasRole($role)) {
            session(['active_role' => $role]);
            $this->activeRole = $role;
            
            return redirect()->route('dashboard');
        }
    }

    public function render()
    {
        return view('livewire.auth.role-switcher', [
            'roles' => Auth::user()->getRoleNames()
        ]);
    }
}
