<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public $username = '';
    public $password = '';

    protected $rules = [
        'username' => 'required',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        // Try login with username or NIP
        $credentials = filter_var($this->username, FILTER_VALIDATE_EMAIL) 
            ? ['email' => $this->username, 'password' => $this->password]
            : (is_numeric($this->username) 
                ? ['nip' => $this->username, 'password' => $this->password]
                : ['username' => $this->username, 'password' => $this->password]);

        if (Auth::attempt($credentials)) {
            session()->regenerate();

            // Set initial active role — load roles eager to avoid extra query
            $user = Auth::user();
            $user->load('roles');
            session(['active_role' => $user->roles->first()?->name]);

            return redirect()->intended('/dashboard');
        }

        $this->addError('username', 'Email/NIP atau Password salah.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.app')->title('Login SIPEKERJA');
    }
}
